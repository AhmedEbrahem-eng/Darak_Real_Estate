<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\Tour;
use App\Models\TourDate;
use App\Repositories\Contracts\TourRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\TourRequestMail;
use App\Mail\TourApprovalMail;
use App\Mail\TourDeclineMail;
use App\Mail\TourDeleteMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TourRepository implements TourRepositoryInterface
{
    public function findTour(int $id)
    {
        return Tour::find($id);
    }

    public function createTour(array $data)
    {
        return DB::transaction(function () use ($data) {
            $existingTour = Tour::where('user_id', Auth::id())
                ->where('property_id', $data['property_id'])
                ->where('status', $data['status'])
                ->exists();

            if ($existingTour) {
                return null;
            }

            $tour = Tour::create([
                'user_id' => Auth::id(),
                'property_id' => $data['property_id'],
                'status' => $data['status'],
            ]);

            foreach ($data['dates'] as $date) {
                TourDate::create([
                    'tour_id' => $tour->id,
                    'date' => $date,
                ]);
            }
            $property = $tour->property;
            if (!$property->id) {
                throw new \Exception('Property ID is missing');
            }
            return $tour->load('tourDates', 'property');
        });
    }


    public function approveTour(int $id, int $tourDateId)
    {
        $tour = Tour::find($id);

        if (!$tour) {
            return false;
        }

        $tourDate = TourDate::where('id', $tourDateId)
            ->where('tour_id', $id)
            ->first();

        if (!$tourDate) {
            return false;
        }

        $tour->status = 'approved';
        $tour->save();

        $tourDate->approved = true;
        $tourDate->save();

        $oldNotification = Notification::where('tour_id', $id)->first();
        $oldNotification->status = 'approved';
        $oldNotification->save();

        $property = $tour->property;
        if (!$property) {
            return null;
        }

        $existingNotification = Notification::where('to_user_id', $tour->user_id)
            ->where('from_user_id', $property->user_id)
            ->where('tour_id', $tour->id)
            ->where('type', 'confirmation')
            ->exists();

        if (!$existingNotification) {
            Notification::create([
                'to_user_id' => $tour->user_id,
                'from_user_id' => $property->user_id,
                'property_id' => $property->id,
                'tour_id' => $tour->id,
                'message' => 'Tour request for property ' . $property->title . ' has been approved at ' . $tourDate['date'],
                'type' => 'confirmation',
                'status' => 'approved',
                'date' => now(),
            ]);

            $user = User::find($tour->user_id);
            Mail::to($user->email)->send(new TourApprovalMail($tour, $property, $user, $tourDate));

            return true;
        } else {
            return null;
        }
    }

    public function declineTour(int $id, string $message)
    {
        $tour = Tour::find($id);

        if (!$tour) {
            return false;
        }
        $oldNotification = Notification::where('tour_id', $id)->first();
        $oldNotification->status = 'declined';
        $oldNotification->save();

        $property = $tour->property;
        if (!$property) {
            return null;
        }

        $tour->status = 'declined';
        $tour->save();

        $existingNotification = Notification::where('to_user_id', $tour->user_id)
            ->where('from_user_id', $property->user_id)
            ->where('tour_id', $tour->id)
            ->where('type', 'cancelation')
            ->exists();

        if (!$existingNotification) {
            Notification::create([
                'to_user_id' => $tour->user_id,
                'from_user_id' => $property->user_id,
                'property_id' => $property->id,
                'tour_id' => $tour->id,
                'message' => $message,
                'type' => 'cancelation',
                'status' => 'declined',
                'date' => now(),
            ]);
            $user = User::find($tour->user_id);
            Mail::to($user->email)->send(new TourDeclineMail($tour, $property, $user, $message));

            return true;
        } else {
            return null;
        }
    }
    public function deleteTour(int $id)
    {
        $tour = Tour::find($id);
        if (!$tour) {
            return null;
        }
        $property = $tour->property;
        if (!$property) {
            return null;
        }
        $tour->delete();
       
        return true;
    }

    public function getUserTours(int $userId)
    {
        return Tour::where('user_id', $userId)
            ->with(['user', 'property', 'tourDates'])
            ->get();
    }
    public function getToursByStatus($status, $userId)
    {
        return Tour::where('status', $status)
            ->where('user_id', $userId)
            ->with(['user', 'property', 'tourDates'])
            ->get();
    }
}
