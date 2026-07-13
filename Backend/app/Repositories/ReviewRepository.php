<?php

namespace App\Repositories;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function showReviews()
    {
        return Review::with(['user', 'property', 'reactions', 'replies'])->get();
    }
    public function storeReview(array $data)
    {
        $userId = Auth::id();
        $propertyId = $data['property_id'];
        $existingReview = Review::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();

        if ($existingReview) {
            return null;
        }

        $data['user_id'] = $userId;
        return Review::create($data);
    }
    public function updateReview(Review $review, array $data)
    {
        $review->update($data);
        return $review;
    }
    public function deleteReview(Review $review)
    {
        $review->delete();
        return true;
    }
    public function findReview(int $id)
    {
        return Review::find($id);
    }
    public function showPropertyReviews(int $id)
    {
        return Review::where('property_id', $id)->with('replies', 'user')->get();
    }
    public function getAverageRating(int $propertyId)
    {
        $averageRating = Review::where('property_id', $propertyId)
            ->avg('rate');
        if (!$averageRating) {
            return null;
        }
        return round($averageRating);
    }
}
