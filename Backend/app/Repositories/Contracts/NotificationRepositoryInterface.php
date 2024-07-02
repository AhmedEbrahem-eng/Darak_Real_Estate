<?php

namespace App\Repositories\Contracts;

interface NotificationRepositoryInterface
{
    public function getLandlordNotifications(int $landlordId);
    public function getRenterNotifications(int $userId);
    public function deleteNotification(int $id);

}
