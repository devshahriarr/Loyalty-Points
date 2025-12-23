<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyCard;
use App\Models\Tenant;
use App\Services\SubscriptionUsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoyaltyCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $loyaltyCards = LoyaltyCard::all();

    //     return response()->json([
    //         'status' => 'success',
    //         'loyaltyCards' => $loyaltyCards,
    //     ]);
    // }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $validated = Validator::make($request->all(), [
    //         "name" => "required",
    //         "design_json" => "required",
    //         "reward_type" => "required",
    //         "reward_threshold" => "required",
    //         "reward_description" => "required",
    //     ]);

    //     if ($validated->fails()) {
    //         return response()->json($validated->errors(), 422);
    //     }

    //     LoyaltyCard::create($validated->validated());

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Loyalty card created successfully',
    //     ]);
    // }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    // {
    //     $loyaltyCard = LoyaltyCard::findOrFail($id);

    //     return response()->json([
    //         'status' => 'success',
    //         'loyaltyCard' => $loyaltyCard,
    //     ]);
    // }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, string $id)
    // {
    //     $validated = Validator::make($request->all(), [
    //         "name" => "required",
    //         "design_json" => "required",
    //         "reward_type" => "required",
    //         "reward_threshold" => "required",
    //         "reward_description" => "required",
    //     ]);

    //     if ($validated->fails()) {
    //         return response()->json($validated->errors(), 422);
    //     }

    //     $loyaltyCard = LoyaltyCard::findOrFail($id);

    //     if (!$loyaltyCard) {

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Loyalty card not found',
    //         ], 404);

    //     }else{

    //         $loyaltyCard->update($validated->validated());

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Loyalty card updated successfully',
    //             'loyaltyCard' => $loyaltyCard,
    //         ]);

    //     }
    // }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id)
    // {
    //     $loyaltyCard = LoyaltyCard::findOrFail($id);

    //     if (!$loyaltyCard) {

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Loyalty card not found',
    //         ], 404);

    //     }else{

    //         $loyaltyCard->delete();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Loyalty card deleted successfully'
    //         ]);

    //     }
    // }

    public function index()
    {
        $cards = LoyaltyCard::with(['rule','design'])->get();

        if ($cards) {
            return response()->json([
                'status' => 'success',
                'cards' => $cards,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Loyalty cards not found',
            ], 404);
        }
    }

    public function availableTypes()
    {
        return [
            ['key'=>'stamp','label'=>'Stamp Card','enabled'=>true],
            ['key'=>'cashback','label'=>'Cashback Card','enabled'=>false],
            ['key'=>'reward','label'=>'Reward Card','enabled'=>true],
            ['key'=>'membership','label'=>'Membership Card','enabled'=>false],
        ];
    }

    public function store(Request $request)
    {
        $card = LoyaltyCard::create($request->only([
            'type','name','company_name','description','barcode_type'
        ]));

        // $card->rule()->create($request->reward_rule);
        // $card->rule()->create([
        //     'loyalty_card_id' => $card->id,
        //     'spend_amount' => $request->spend_amount,
        //     'earn_value' => $request->earn_value,
        //     'earn_type' => $request->earn_type,
        //     'earned_message' => $request->earned_message
        // ]);

        // app(SubscriptionUsageService::class)->increment('cards');

        return response()->json([
            'id'=>$card->id,
            'next_step'=>'design'
        ]);
    }

    public function updateDesign(Request $request, $id)
    {
        $card = LoyaltyCard::findOrFail($id);

        $card->design()->updateOrCreate([], $request->all());

        return response()->json(['message'=>'Design saved']);
    }

    public function activate($id)
    {
        $card = LoyaltyCard::findOrFail($id);

        $card->update([
            'status'=>'active',
            'qr_code'=>Str::uuid()
        ]);

        return response()->json(['message'=>'Card activated']);
    }

    public function destroy($id)
    {
        LoyaltyCard::findOrFail($id)->delete();
        app(SubscriptionUsageService::class)->decrement('cards');

        return response()->json(['message'=>'Deleted']);
    }
}
