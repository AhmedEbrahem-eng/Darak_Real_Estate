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

    public function deleteImage($imageId)
    {
        $image = $this->imageRepository->findImage($imageId);
        if (!$image) {
            return response()->json(['message' => 'Image dosen`t exist'], 404);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Only the admin or the property owner can delete the image
        if ($user->role !== 'admin' && (!$image->property || $image->property->user_id !== $user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $deleted = $this->imageRepository->deleteImage($imageId);
        if (!$deleted) {
            return response()->json(['message' => 'Image dosen`t exist'], 404);
        }

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}
