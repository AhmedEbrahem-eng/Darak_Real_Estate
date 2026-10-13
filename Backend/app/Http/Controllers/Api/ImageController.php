<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ImageRepositoryInterface;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    protected $imageRepository;

    public function __construct(ImageRepositoryInterface $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    public function deleteImage(Request $request, $imageId)
    {
        $image = $this->imageRepository->findImage((int) $imageId);

        if (!$image) {
            return response()->json(['message' => 'Image doesn\'t exist'], 404);
        }

        $user = $request->user();
        $isOwner = $image->property && (int) $image->property->user_id === (int) $user->id;
        $isAdmin = $user->role === 'admin';

        if (!$isOwner && !$isAdmin) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $deleted = $this->imageRepository->deleteImage((int) $imageId);

        if (!$deleted) {
            return response()->json(['message' => 'Image doesn\'t exist'], 404);
        }

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}
