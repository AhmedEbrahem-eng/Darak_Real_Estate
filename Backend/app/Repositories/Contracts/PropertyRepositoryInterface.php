<?php

namespace App\Repositories\Contracts;

interface PropertyRepositoryInterface
{
    public function getAllProperties();
    public function getPropertyBySlug(string $slug);
    public function getLatestProperties(string $listing_type);
    public function getAcceptedProperties();
    public function getPendingProperties();
    public function updateStatus(int $id, string $status);
    public function createProperty(array $data);
    public function searchProperties(array $filters);
    public function showUserProperties(int $id);
    public function updateProperty(array $data, string $slug);
    public function findPropertyById(int $id);
    public function delete(int $id);

}
