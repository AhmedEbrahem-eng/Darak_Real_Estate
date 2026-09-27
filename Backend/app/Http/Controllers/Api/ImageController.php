<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ImageRepositoryInterface;
use Illuminate\Http\Request;
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
            return response()->json(['message' => 'Image not found'], 404);
        }

        $user = Auth::user();
        if ($image->property && $image->property->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $this->imageRepository->deleteImage($imageId);
        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}
