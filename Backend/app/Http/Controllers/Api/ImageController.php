<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ImageRepositoryInterface;
use Illuminate\Support\Facades\Auth;

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

        $user = Auth::user();
        if (!$user || ($user->id !== $image->property->user_id && $user->role !== 'admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $deleted = $this->imageRepository->deleteImage($imageId);
        if (!$deleted) {
            return response()->json(['message' => 'Image dosen`t exist'], 404);
        }

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}
