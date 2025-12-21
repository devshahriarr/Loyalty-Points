<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\Visit;
use App\Models\CustomerAnalytics;
use Illuminate\Support\Carbon;

class CustomerAnalyticsService
{
    /**
     * Recalculate analytics for a single customer
     *
     * @param  int|Customer $customer
     * @return CustomerAnalytics
     */
    public function recalcCustomer($customer)
    {
        if (! $customer instanceof Customer) {
            $customer = Customer::findOrFail($customer);
        }

        // timeframe definitions
        $now = Carbon::now();
        $recencyWindowDays = 90; // example windows
        $monetaryWindowDays = 365;

        // fetch visit stats
        $visits = Visit::where('customer_id', $customer->id);

        $visitsCount = $visits->count();
        $lastVisit = Visit::where('customer_id', $customer->id)
                         ->orderByDesc('visited_at')
                         ->value('visited_at');

        $monetaryTotal = Visit::where('customer_id', $customer->id)
                               ->where('visited_at', '>=', $now->copy()->subDays($monetaryWindowDays))
                               ->sum('amount');

        // compute R, F, M sub-scores from 1..5 (higher = better)
        $rScore = $this->scoreRecency($lastVisit, $now);
        $fScore = $this->scoreFrequency($visitsCount);
        $mScore = $this->scoreMonetary($monetaryTotal);

        // combine into single RFM (simple average scaled 1..5)
        $rfm = (int) round(($rScore + $fScore + $mScore) / 3);

        // determine segment by simple rules (tweak to your needs)
        if ($rfm >= 4 && $visitsCount >= 5) {
            $segment = 'regular';
        } elseif ($rfm == 3 || ($lastVisit && Carbon::parse($lastVisit)->diffInDays($now) > 30 && $visitsCount >= 2)) {
            $segment = 'at-risk';
        } else {
            $segment = 'churning';
        }

        // compute reward points (example: 1 point per $1, bonus for regular)
        $basePoints = intval(floor($monetaryTotal));
        $bonus = $segment === 'regular' ? intval($basePoints * 0.10) : 0;
        $rewardPoints = $basePoints + $bonus;

        // persist
        $analytics = CustomerAnalytics::updateOrCreate(
            ['customer_id' => $customer->id],
            [
                'rfm' => $rfm,
                'segment' => $segment,
                'visits_count' => $visitsCount,
                'monetary_total' => $monetaryTotal,
                'last_visit_at' => $lastVisit ? Carbon::parse($lastVisit) : null,
                'reward_points' => $rewardPoints,
                'extra' => [
                    'r_score' => $rScore,
                    'f_score' => $fScore,
                    'm_score' => $mScore,
                ]
            ]
        );

        return $analytics;
    }

    /* ----- scoring helpers: tune thresholds as you like ----- */

    private function scoreRecency($lastVisit, $now)
    {
        if (! $lastVisit) return 1;
        $days = Carbon::parse($lastVisit)->diffInDays($now);

        if ($days <= 7) return 5;
        if ($days <= 30) return 4;
        if ($days <= 60) return 3;
        if ($days <= 120) return 2;
        return 1;
    }

    private function scoreFrequency($visitsCount)
    {
        if ($visitsCount >= 20) return 5;
        if ($visitsCount >= 10) return 4;
        if ($visitsCount >= 5) return 3;
        if ($visitsCount >= 2) return 2;
        return 1;
    }

    private function scoreMonetary($monetary)
    {
        if ($monetary >= 10000) return 5;
        if ($monetary >= 5000) return 4;
        if ($monetary >= 1000) return 3;
        if ($monetary >= 100) return 2;
        return 1;
    }
}
