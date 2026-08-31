<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createNotification(User $sender, User $recipient): Notification
    {
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();
        $property = Property::create([
            'title' => 'Test Property',
            'slug' => 'test-property-' . uniqid(),
            'description' => 'Test Description',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 100.5,
            'price' => 1000,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'status' => 'accepted',
            'user_id' => $sender->id,
            'availability' => 'available',
            'listing_type' => 'rent',
        ]);

        return Notification::create([
            'from_user_id' => $sender->id,
            'to_user_id' => $recipient->id,
            'property_id' => $property->id,
            'message' => 'Test Notification',
            'type' => 'confirmation',
            'date' => now(),
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_notification()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $notification = $this->createNotification($sender, $recipient);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_another_users_notification()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $otherUser = User::factory()->create(['role' => 'user']);

        $notification = $this->createNotification($sender, $recipient);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_recipient_can_delete_their_notification()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $notification = $this->createNotification($sender, $recipient);

        Sanctum::actingAs($recipient);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
    }

    public function test_sender_can_delete_their_notification()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $notification = $this->createNotification($sender, $recipient);

        Sanctum::actingAs($sender);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
    }

    public function test_admin_can_delete_any_notification()
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $notification = $this->createNotification($sender, $recipient);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
    }

    public function test_deleting_non_existent_notification_returns_404()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/notifications/99999");

        $response->assertStatus(404);
    }
}
