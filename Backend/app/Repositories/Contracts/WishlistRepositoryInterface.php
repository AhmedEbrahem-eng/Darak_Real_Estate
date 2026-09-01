<?php
namespace App\Repositories\Contracts;

interface WishlistRepositoryInterface
{
    public function getUserWishlist(int $userId);

    public function addToWishlist(int $userId, int $propertyId);

    public function findWishlist(int $id);

    public function removeFromWishlist(int $id);
}
