<?php

namespace App\Observers;

use App\Mail\TourDeleteMail;
use App\Mail\TourRequestMail;
use App\Models\Notification;
use App\Models\Tour;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TourObserver
{
    /**
     * Handle the Tour "created" event.
     */
    public function created(Tour $tour): void
    {
        $property = $tour->property;
        $landlord = $property->user;

        Notification::create([
            'from_user_id' => Auth::id() ?? $tour->user_id,
            'to_user_id' => $landlord->id,
            'tour_id' => $tour->id,
            'property_id' => $tour->property_id,
            'message' => 'Tour request for property: ' . $property->title,
            'type' => 'request',
            'status' => 'pending',
            'date' => now(),
        ]);

        Mail::to($landlord->email)->send(new TourRequestMail($tour, $property, $landlord));
    }

    /**
     * Handle the Tour "updated" event.
     */
    public function updated(Tour $tour): void
    {
        
    }

    /**
     * Handle the Tour "deleted" event.
     */
    public function deleted(Tour $tour): void
    {
        $property = $tour->property;
        $landlord = $property->user;
        $user = Auth::user();

        Notification::create([
            'from_user_id' => Auth::id() ?? $tour->user_id,
            'to_user_id' => $landlord->id,
            'property_id' => $property->id,
            'tour_id' => $tour->id,
            'message' => 'Tour request for property ' . $property->title . ' has been deleted by the user.',
            'type' => 'deleted-tour',
            'date' => now(),
        ]);

        Mail::to($landlord->email)->send(new TourDeleteMail($tour, $property, $landlord, $user));
    
    }

    /**
     * Handle the Tour "restored" event.
     */
    public function restored(Tour $tour): void
    {
        //
    }

    /**
     * Handle the Tour "force deleted" event.
     */
    public function forceDeleted(Tour $tour): void
    {
        //
    }
}
