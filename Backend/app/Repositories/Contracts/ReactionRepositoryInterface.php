<?php

namespace App\Repositories\Contracts;


interface ReactionRepositoryInterface
{
    public function addReaction(array $data);
    public function deleteReaction(int $id);
    public function findReaction(int $id);
}
