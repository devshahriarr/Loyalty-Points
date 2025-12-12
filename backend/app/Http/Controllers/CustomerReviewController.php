<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerReview;
use App\Models\Tenant;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    protected $host;
    protected $tenant;
    public function __construct(Request $request)
    {
        $this->host = request()->getHost();
        $this->tenant = Tenant::where('domain', $this->host)->first();
    }


    // Get paginated reviews for dashboard
    public function index(Request $request)
    {
        $reviews = CustomerReview::with('customer')
            ->where('tenant_id', $this->tenant->id)
            ->orderByDesc('created_at')
            ->paginate(12);

        // Format response for frontend UI
        $data = $reviews->through(function ($review) {
            return [
                'id'          => $review->id,
                'review_text' => $review->review_text,
                'rating'      => $review->rating,
                'visible'     => $review->visible,
                'visited_at'  => $review->visited_at,
                'customer' => [
                    'id'     => $review->customer?->id,
                    'name'   => $review->customer?->name,
                    'avatar' => $review->customer?->profile_image, // auto-fetched
                ]
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ]);
    }

    // Create new Review
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'  => 'nullable|exists:customers,id',
            'tenant_id'    => 'nullable|exists:tenants,id',
            'review_text'  => 'required|string',
            'rating'       => 'required|integer|min:1|max:5',
            'visited_at'   => 'nullable|date'
        ]);

        $review = CustomerReview::create($validated);
        // $review = CustomerReview::create([
        //     'customer_id' => $validated['customer_id'],
        // ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review created successfully',
            'data' => $review
        ]);
    }

    // Update existing review
    public function update(Request $request, $id)
    {
        $review = CustomerReview::where('tenant_id', $this->tenant->id)
        ->findOrFail($id);

        $review->update($request->validate([
            'review_text' => 'sometimes|string',
            'rating'      => 'sometimes|integer|min:1|max:5',
            'visited_at'  => 'sometimes|date',
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Review updated',
            'data' => $review
        ]);
    }

    // Show/Hide toggle
    public function toggleVisibility($id)
    {
        // $review = CustomerReview::where('tenant_id', $this->tenant->id)
        // ->findOrFail($id);

        $review = CustomerReview::findOrFail($id);

        $review->visible = !$review->visible;
        $review->save();

        return response()->json([
            'status' => 'success',
            'visible' => $review->visible
        ]);
    }

    public function destroy($id)
    {
        CustomerReview::where('tenant_id', $this->tenant->id)
        ->findOrFail($id)
        ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted'
        ]);
    }
}
