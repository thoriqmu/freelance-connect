<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function __construct(private ReviewService $reviewService) {}

    /**
     * Get reviews for a user
     */
    public function userReviews(int $userId, Request $request): JsonResponse
    {
        try {
            $reviews = $this->reviewService->getUserReviews(
                $userId,
                $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $reviews, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create review
     */
    public function store(int $projectId, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reviewee_id' => 'required|integer|exists:users,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'sometimes|nullable|string|max:1000',
            ]);

            $review = $this->reviewService->createReview(
                $validated,
                $projectId,
                auth()->id()
            );

            return $this->successResponse('Review created successfully', $review, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Get review detail
     */
    public function show(int $id): JsonResponse
    {
        try {
            $review = $this->reviewService->getReviewDetail($id);

            if (!$review) {
                return $this->notFoundResponse('Review not found');
            }

            return $this->successResponse('Success', $review, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
