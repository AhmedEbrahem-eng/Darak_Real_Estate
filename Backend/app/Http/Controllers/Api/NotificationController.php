<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function showLandlordNotifications()
    {
        $notifications = $this->notificationRepository->getLandlordNotifications(Auth::id());
        if(!$notifications){
            return response()->json(['message' => 'This is not a landlord'], 404);
        }
        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'No notifications found for the landlord'], 404);
        }

        return response()->json(['data' => NotificationResource::collection($notifications)], 200);
    }

    public function showRenterNotifications()
    {
        $notifications = $this->notificationRepository->getRenterNotifications(Auth::id());
        if(!$notifications){
            return response()->json(['message' => 'This is not a renter'], 404);
        }
        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'No notifications found for the renter'], 404);
        }

        return response()->json(['data' => NotificationResource::collection($notifications)], 200);
    }
    public function deleteNotification($id)
    {
        $notification = $this->notificationRepository->findNotification($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $user = Auth::user();

        // Ensure user is recipient, sender, or admin
        if ($user && ($user->id === $notification->to_user_id || $user->id === $notification->from_user_id || $user->role === 'admin')) {
            $this->notificationRepository->deleteNotification($notification);
            return response()->json(['message' => 'Notification deleted successfully'], 200);
        }

        return response()->json(['error' => 'Unauthorized to delete this notification'], 403);
    }
}
