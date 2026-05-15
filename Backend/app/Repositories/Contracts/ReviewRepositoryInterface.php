<?php

namespace App\Repositories\Contracts;

interface ReviewRepositoryInterface
{
    public function showReviews();
    public function storeReview(array $data);
    public function updateReview(int $id, array $data);
    public function updateReviewObject(\App\Models\Review $review, array $data);
    public function deleteReview(int $id);
    public function deleteReviewObject(\App\Models\Review $review);
    public function showPropertyReviews(int $id);
    public function getAverageRating(int $propertyId);
    public function findReview(int $id);
}
