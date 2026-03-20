<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewService
{
    /**
     * Get reviews for a user
     */
    public function getUserReviews(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('reviewee_id', $userId)
            ->with(['reviewer', 'project'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a review
     */
    public function createReview(array $data, int $projectId, int $reviewerId): Review
    {
        try {
            $project = Project::findOrFail($projectId);

            if ($project->status !== 'completed') {
                throw new \Exception('You can only review completed projects.', 403);
            }

            $alreadyReviewed = Review::where('project_id', $projectId)
                ->where('reviewer_id', $reviewerId)
                ->exists();

            if ($alreadyReviewed) {
                throw new \Exception('You have already reviewed this project.', 422);
            }

            if ($data['rating'] < 1 || $data['rating'] > 5) {
                throw new \Exception('Rating must be between 1 and 5.', 422);
            }

            $review = Review::create([
                'project_id' => $projectId,
                'reviewer_id' => $reviewerId,
                'reviewee_id' => $data['reviewee_id'],
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);

            Log::info('Review created', ['review_id' => $review->id, 'project_id' => $projectId]);

            return $review;
        } catch (\Exception $e) {
            Log::error('Create review failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get review detail
     */
    public function getReviewDetail(int $reviewId): ?Review
    {
        return Review::with(['reviewer', 'reviewee', 'project'])->find($reviewId);
    }
}
