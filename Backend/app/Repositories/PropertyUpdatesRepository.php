<?php

namespace App\Repositories;

use App\Mail\PropertyUpdateRejectedMail;
use App\Models\Notification;
use App\Models\PropertyUpdate;
use App\Repositories\Contracts\PropertyUpdatesRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PropertyUpdatesRepository implements PropertyUpdatesRepositoryInterface
{
    public function index()
    {
        $pendingUpdates = PropertyUpdate::where('status', 'pending')->with('property')->get();

        if ($pendingUpdates->isEmpty()) {
            return false;
        }

        return $pendingUpdates;
    }
    public function showNewProperty($id)
    {
        $propertyUpdate = PropertyUpdate::where('status', 'pending')
            ->find($id);

        if (!$propertyUpdate) {
            return null;
        }

        return $propertyUpdate;
    }
   public function showOldProperty($id)
    {
        $propertyUpdate = PropertyUpdate::where('status', 'pending')
            ->with('property')
            ->find($id);

        if (!$propertyUpdate) {
            return null;
        }

        return $propertyUpdate;
    }
    public function approvePropertyUpdate(int $propertyUpdateId)
    {
        $propertyUpdate = PropertyUpdate::find($propertyUpdateId);

        if (!$propertyUpdate) {
            return null;
        }

        $property = $propertyUpdate->property;

        $property->update($propertyUpdate->data);

        $propertyUpdate->status = 'approved';
        $propertyUpdate->save();

        
        return $property;
    }

    public function rejectPropertyUpdate(int $propertyUpdateId)
    {
        $propertyUpdate = PropertyUpdate::find($propertyUpdateId);

        if (!$propertyUpdate) {
            return null;
        }

        $propertyUpdate->status = 'rejected';
        $propertyUpdate->save();

        return $propertyUpdate->property;
    }
}
