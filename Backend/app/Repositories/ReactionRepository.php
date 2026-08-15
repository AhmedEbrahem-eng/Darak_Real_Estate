<?php

namespace App\Repositories;

use App\Models\Reaction;
use App\Repositories\Contracts\ReactionRepositoryInterface;

class ReactionRepository implements ReactionRepositoryInterface
{
    public function addReaction(array $data){
      
        $reaction = Reaction::create($data);
        if($reaction){
            return $reaction;
        }else{
            return false;
        }

    }
    public function deleteReaction(int $id){
        $reaction =Reaction::find($id);
        if(!$reaction){
            return null;
        }else{
            return $reaction->delete();
        }
    }

    public function findReaction(int $id){
        return Reaction::find($id);
    }
}
