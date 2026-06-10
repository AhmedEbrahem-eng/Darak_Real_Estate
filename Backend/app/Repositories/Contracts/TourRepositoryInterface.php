<?php


namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface TourRepositoryInterface
{
    public function createTour(array $data);
    public function findTour(int $id);
    public function approveTour(int $id, int $tourDateId);
    public function declineTour(int $id,string $message);
    public function getUserTours(int $userId);
    public function getToursByStatus(string $status,int $userId);
    public function deleteTour(int $id);
}

