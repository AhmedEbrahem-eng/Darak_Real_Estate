<?php

namespace App\Repositories;

interface PropertyRepositoryInterface
{
    public function getAllProperties(int $perPage);
    public function getPropertyBySlug(string $slug);
    public function getLatestProperties(int $property_type_id, string $listing_type);
    public function createProperty(array $data);
    public function searchProperties(array $filters);
}
