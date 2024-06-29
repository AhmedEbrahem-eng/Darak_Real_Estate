<?php

namespace App\Repositories;

use App\Models\Amenity;

class AmenityRepository implements AmenityRepositoryInterface
{
    public function getAllAmenities()
    {
        return Amenity::all();
    }

    public function createAmenity(array $data)
    {
        return Amenity::create($data);
    }

    public function findAmenityBySlug(string $slug)
    {
        return Amenity::where('slug', $slug)->first();
    }

    public function updateAmenity(string $slug, array $data)
    {
        $amenity = $this->findAmenityBySlug($slug);
        if ($amenity) {
            $amenity->update($data);
            return $amenity;
        }

        return null;
    }

    public function deleteAmenity(string $slug)
    {
        $amenity = $this->findAmenityBySlug($slug);
        if ($amenity) {
            $amenity->delete();
            return true;
        }

        return false;
    }
}
