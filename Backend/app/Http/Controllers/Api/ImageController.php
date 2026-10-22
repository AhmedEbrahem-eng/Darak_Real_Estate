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
        $image = $this->imageRepository->findImage((int) $imageId);

        if (!$image) {
            return response()->json(['message' => 'Image does not exist'], 404);
        }

        $user = Auth::user();
        if ($user->role !== 'admin' && (!$image->property || $image->property->user_id !== $user->id)) {
            return response()->json(['message' => 'Unauthorized to delete this image'], 403);
        }

        $deleted = $this->imageRepository->deleteImage((int) $imageId);
        if (!$deleted) {
            return response()->json(['message' => 'Image does not exist'], 404);
        }

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}
