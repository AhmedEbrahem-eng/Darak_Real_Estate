<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reactions\StoreReactionRequest;
use App\Http\Resources\ReactionResource;
use App\Models\Reaction;
use App\Repositories\Contracts\ReactionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    protected $reactionRepository;
    public function __construct(ReactionRepositoryInterface $reactionRepositoryInterface ){
        $this->reactionRepository=$reactionRepositoryInterface;
    }
    public function store(StoreReactionRequest $request){
        $data = $request->all();
        $data['user_id'] = Auth::id();

        $reaction = $this->reactionRepository->addReaction($data);

        if ($reaction) {
            return response()->json(new ReactionResource($reaction), 201);
        } else {
            return response()->json(["message" => "Failed to store reaction"], 400);
        }

    }
    public function delete(int $id)
    {
        $reaction = $this->reactionRepository->findReaction($id);

        if (!$reaction) {
            return response()->json(["message" => "Reaction not found"], 404);
        }

        $user = Auth::user();
        if ($user->id !== $reaction->user_id && $user->role !== 'admin') {
            return response()->json(["message" => "Unauthorized"], 403);
        }

        $deleted = $this->reactionRepository->deleteReaction($id);

        if ($deleted) {
            return response()->json(["message" => "Reaction deleted successfully"], 200);
        } else {
            return response()->json(["message" => "reaction already deleted"], 400);
        }
    }
}
