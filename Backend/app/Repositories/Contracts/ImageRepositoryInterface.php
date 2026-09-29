<?php
namespace App\Repositories\Contracts;

interface ImageRepositoryInterface
{
    public function findImage(int $imageId);
    public function deleteImage(int $imageId);
}
