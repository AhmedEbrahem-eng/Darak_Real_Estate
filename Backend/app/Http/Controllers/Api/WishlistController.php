<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wishlists\StoreWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Repositories\Contracts\WishlistRepositoryInterface;
use Illuminate\Support\Facades\Auth;
class WishlistController extends Controller
{
    protected $wishlistRepository;

    public function __construct(WishlistRepositoryInterface $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    public function show()
    {
        $userId = auth()->id();
        $wishlist = $this->wishlistRepository->getUserWishlist($userId);
        return response()->json(["data"=>WishlistResource::collection($wishlist)]);
    }

    public function store(StoreWishlistRequest $request)
    {

        $userId = auth()->id();
        $propertyId = $request->property_id;

        $wishlistItem = $this->wishlistRepository->addToWishlist($userId, $propertyId);

        if (!$wishlistItem) {
            return response()->json(['message' => 'Property already in wishlist'], 200);
        }

        return response()->json(['message' => 'Property added to wishlist', 'data' => new WishlistResource($wishlistItem)], 200);
    }

    public function delete($id)
    {
        $wishlistItem = $this->wishlistRepository->findWishlist($id);

        if (!$wishlistItem) {
            return response()->json(['message' => 'Wishlist item not found'], 404);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($wishlistItem->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->wishlistRepository->removeFromWishlist($id);
        return response()->json(['message' => 'Item deleted successfully']);
    }
}