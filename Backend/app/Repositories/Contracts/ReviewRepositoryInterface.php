<?php

namespace App\Repositories\Contracts;

interface ReviewRepositoryInterface
{
    public function showReviews();
    public function storeReview(array $data);
    public function updateReview(int $id, array $data);
    public function deleteReview(int $id);
    public function findReview(int $id);
    public function showPropertyReviews(int $id);
    public function getAverageRating(int $propertyId);
}
