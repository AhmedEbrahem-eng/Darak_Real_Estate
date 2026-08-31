<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function getLandlordNotifications(int $landlordId)
    {
        $landlord = User::find($landlordId);

        if ($landlord && $landlord->role === 'landlord') {
            return Notification::where('to_user_id', $landlordId)
                ->whereIn('type', ['request','deleted-tour','status_change','property_update_approved','property_update_rejected'])
                ->with(['fromUser', 'toUser', 'tour.tourDates','property'])
                ->get();
        }

        return null;
    }

    public function getRenterNotifications(int $userId)
    {
        $user = User::find($userId);

        if ($user && $user->role === 'user') {
            return Notification::where('to_user_id', $userId)
                ->whereIn('type', ['confirmation', 'cancelation','tour_property_update'])
                ->with(['fromUser', 'toUser', 'tour.tourDates','property'])
                ->get();
        }

        return null;
    }
    public function findNotification(int $id)
    {
        return Notification::find($id);
    }

    public function deleteNotification($notification)
    {
        if ($notification instanceof Notification) {
            return $notification->delete();
        }
        $notificationModel = Notification::find($notification);
        if ($notificationModel) {
            return $notificationModel->delete();
        }
        return false;
    }
}
