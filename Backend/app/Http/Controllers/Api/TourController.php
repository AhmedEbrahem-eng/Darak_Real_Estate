<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tours\StoreTourRequest;
use App\Http\Resources\TourResource;
use App\Repositories\Contracts\TourRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    protected $tourRepository;

    public function __construct(TourRepositoryInterface $tourRepository)
    {
        $this->tourRepository = $tourRepository;
    }

    public function send_Request(StoreTourRequest $request)
    {
        $tour = $this->tourRepository->createTour([
            'property_id' => $request->input('property_id'),
            'status' => $request->input('status'),
            'dates' => $request->dates,
        ]);

        if (is_null($tour)) {
            return response()->json(['message' => 'Tour already created'], 409); 
        }

        return response()->json([
            'message' => 'Tour created successfully and Notification sent.',
            'tour' => new TourResource($tour),
        ], 201);
    }

    public function approveTour(Request $request, $id)
    {
        $request->validate([
            'tour_date' => 'required|exists:tour_dates,id,tour_id,' . $id,
        ]);

        $success = $this->tourRepository->approveTour($id, $request->tour_date);
        if (!$success) {
            return response()->json(['error' => 'Tour request already approved'], 404);
        }

        return response()->json(['message' => 'Tour approved successfully'], 200);
    }

    public function declineTour($id)
    {
        $success = $this->tourRepository->declineTour($id);
        if (!$success) {
            return response()->json(['error' => 'Tour request already declined'], 404);
        }

        return response()->json(['message' => 'Tour cancelled successfully'], 200);
    }
    public function getUserTours(){
        $tours = $this->tourRepository->getUserTours(Auth::id());
        return response()->json([
            'tours' => TourResource::collection($tours),
        ], 200);
    }

    public function getToursByStatus($status)
    {
        $tours = $this->tourRepository->getToursByStatus($status,Auth::id());
        return response()->json([
            'tours' => TourResource::collection($tours),
        ], 200);
    }
    public function deleteTour($id){
        $tour= $this->tourRepository->deleteTour($id);
        if($tour){
            return response()->json([
                'message'=>'tour cancelled successfully',
            ], 200);
        }else{
            return response()->json([
                'message'=>'tour not found',
            ], 400);
        }
    }

}