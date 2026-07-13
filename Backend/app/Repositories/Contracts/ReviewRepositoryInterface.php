<?php

namespace App\Repositories\Contracts;

use App\Models\Review;

interface ReviewRepositoryInterface
{
    public function showReviews();
    public function storeReview(array $data);
    public function updateReview(Review $review, array $data);
    public function deleteReview(Review $review);
    public function findReview(int $id);
    public function showPropertyReviews(int $id);
    public function getAverageRating(int $propertyId);
}
