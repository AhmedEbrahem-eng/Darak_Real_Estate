<?php

namespace App\Repositories;

use App\Models\Wishlist;
use App\Repositories\Contracts\WishlistRepositoryInterface;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function getUserWishlist(int $userId)
    {
        return Wishlist::with(['user', 'property.location','property.propertyType'])->where('user_id', $userId)->get();
    }

    public function addToWishlist(int $userId, int $propertyId)
    {
        $existingWishlistItem = Wishlist::where('user_id', $userId)->where('property_id', $propertyId)->first();

        if ($existingWishlistItem) {
            return null;
        }

        $wishlist = Wishlist::create([
            'user_id' => $userId,
            'property_id' => $propertyId,
        ]);

        return $wishlist;
    }

    public function findWishlist(int $id)
    {
        return Wishlist::find($id);
    }

    public function removeFromWishlist(int $id)
    {
        $wishlistItem = Wishlist::find($id);

        if ($wishlistItem) {
            $wishlistItem->delete();
        }
    }
}
