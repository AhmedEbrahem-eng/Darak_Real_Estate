<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\StoreReviewRequest;
use App\Http\Requests\Reviews\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    protected $reviewRepository;
    public function __construct(ReviewRepositoryInterface $reviewRepositoryInterface){
        $this->reviewRepository=$reviewRepositoryInterface;
    }
    public function show()
    {
        $reviews = $this->reviewRepository->showReviews();
        if(!$reviews->isEmpty()){
            return response()->json(['data'=>ReviewResource::collection($reviews)]);
        }else{
            return response()->json(['message'=>'Reviews is empty']);
        }
    }
    public function showPropertyReviews($property_id)
    {
        $reviews = $this->reviewRepository->showPropertyReviews($property_id);
        if (!$reviews->isEmpty()) {
            return response()->json(['data' => ReviewResource::collection($reviews)]);
        } else {
            return response()->json(['message' => 'No reviews found for this property']);
        }
    }
    public function store(StoreReviewRequest $request)
    {
        
        $review = $this->reviewRepository->storeReview($request->validated());
        if ($review) {
            return response()->json(['data' => new ReviewResource($review)], 201);
        } else {
            return response()->json(['message' => 'The user has already added a comment on this property'], 400);
        }
    }
    public function update(UpdateReviewRequest $request, $id)
    {
        $review = $this->reviewRepository->findReview($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        if (Auth::id() !== $review->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $updatedReview = $this->reviewRepository->updateReview($id, $request->validated());
        if ($updatedReview) {
            return response()->json(['data' => new ReviewResource($updatedReview)], 200);
        } else {
            return response()->json(['message' => 'Failed to update review'], 400);
        }
    }
    public function delete($id){
        $review = $this->reviewRepository->findReview($id);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        if (Auth::id() !== $review->user_id && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $deleted = $this->reviewRepository->deleteReview($id);
        if ($deleted) {
            return response()->json(['message' => 'review deleted successfuly'], 200);
        } else {
            return response()->json(['message' => 'Failed to delete review'], 400);
        }
    }
    public function calcAverage($id)
    {
        $averageRating = $this->reviewRepository->getAverageRating($id);
        if(!$averageRating){
            return response()->json(['message'=>'There is no reviews']);
        }
        return response()->json(['average'=>$averageRating]);

    }
}
