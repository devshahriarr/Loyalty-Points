<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerReview;
use App\Models\Tenant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        try {
            $reviews = CustomerReview::with('customer')
                // ->where('tenant_id', $this->tenant->id)
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

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message'=> 'Something went error. Please contact with support.',
                // 'error' => $e->getMessage()
            ], 500);
        }

    }

    // Create new Review
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                // 'customer_id'  => 'nullable|exists:customers,id',
                // 'tenant_id'    => 'nullable|exists:tenants,id',
                'review_text'  => 'required|string',
                'rating'       => 'required|integer|min:1|max:5',
                'visited_at'   => 'nullable|date'
            ]);

            $review = CustomerReview::create($validated->validated());
            // $review = CustomerReview::create([
            //     'customer_id' => $validated['customer_id'],
            // ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Review created successfully',
                'data' => $review
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message'=> 'Something went error. Please contact with support.',
                // 'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update existing review
    public function update(Request $request, $id)
    {
        try {
            $validated = Validator::make($request->all(), [
                'review_text' => 'sometimes|string',
                'rating'      => 'sometimes|integer|min:1|max:5',
                'visited_at'  => 'sometimes|date',
            ]);

            if ($validated->fails()) {
                return response()->json($validated->errors(), 422);
            }

            $review = CustomerReview::findOrFail($id);
            // $review = CustomerReview::where('tenant_id', $this->tenant->id)
            // ->findOrFail($id);

            $review->update($validated->validated());

            return response()->json([
                'status'=> 'success',
                'message' => 'Review updated',
                'data' => $review
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message'=> 'Something went error. Please contact with support.',
                // 'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show/Hide toggle
    public function toggleVisibility($id)
    {
        try {
            // $review = CustomerReview::where('tenant_id', $this->tenant->id)
            // ->findOrFail($id);

            $review = CustomerReview::findOrFail($id);

            $review->visible = !$review->visible;
            $review->save();

            return response()->json([
                'status' => 'success',
                'visible' => $review->visible
            ]);

        } catch (Exception $e){

            return response()->json([
                'status' => 'error',
                'message'=> 'Something went error. Please contact with support.',
                'error' => $e->getMessage()
            ], 500);

        }
    }

    public function destroy($id)
    {
        try {
            // $review = CustomerReview::where('tenant_id', $this->tenant->id)
            // ->findOrFail($id);

            $review = CustomerReview::findOrFail($id);

            $review->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Review deleted'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message'=> 'Something went error. Please contact with support.',
                // 'error' => $e->getMessage()
            ], 500);
        }
    }
}
