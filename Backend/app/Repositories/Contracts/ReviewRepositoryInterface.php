<?php

namespace App\Repositories\Contracts;

interface ReviewRepositoryInterface
{
    public function showReviews();
    public function findReview(int $id);
    public function storeReview(array $data);
    public function updateReview(\App\Models\Review $review, array $data);
    public function deleteReview(\App\Models\Review $review);
    public function showPropertyReviews(int $id);
    public function getAverageRating(int $propertyId);
}
