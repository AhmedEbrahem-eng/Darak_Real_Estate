<?php
namespace App\Repositories;

use App\Models\PropertyImage;
use App\Repositories\Contracts\ImageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImageRepository implements ImageRepositoryInterface
{
    public function deleteImage(int $imageId)
{
    DB::beginTransaction();

    try {
        $image = PropertyImage::find($imageId);
        if (!$image) {
            DB::rollback();
            return false;
        }

        $filePath = public_path($image->image);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $image->delete();

        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollback();
        throw new \Exception('Failed to delete image: ' . $e->getMessage());
    }
}

    public function findImage(int $imageId)
    {
        return PropertyImage::with('property')->find($imageId);
    }
}
