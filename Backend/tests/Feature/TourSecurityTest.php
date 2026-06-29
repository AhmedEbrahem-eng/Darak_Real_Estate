<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tour;
use App\Models\User;
use App\Models\Location;
use App\Models\PropertyType;
use App\Models\TourDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment']);
    }

    public function test_user_cannot_delete_another_users_tour()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user2);
        $tour = Tour::factory()->create([
            'user_id' => $user2->id,
            'property_id' => $property->id,
        ]);

        $user3 = User::factory()->create();
        Sanctum::actingAs($user3);

        $response = $this->deleteJson("/api/tour/{$tour->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tours', ['id' => $tour->id]);
    }

    public function test_user_cannot_approve_tour_for_another_users_property()
    {
        $user1 = User::factory()->create(); // Property owner
        $user2 = User::factory()->create(); // Tour requester
        $user3 = User::factory()->create(); // Attacker

        $property = Property::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user2);
        $tour = Tour::factory()->create([
            'user_id' => $user2->id,
            'property_id' => $property->id,
        ]);

        $tourDate = TourDate::create(['tour_id' => $tour->id, 'date' => now()]);

        Sanctum::actingAs($user3);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_decline_tour_for_another_users_property()
    {
        $user1 = User::factory()->create(); // Property owner
        $user2 = User::factory()->create(); // Tour requester
        $user3 = User::factory()->create(); // Attacker

        $property = Property::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user2);
        $tour = Tour::factory()->create([
            'user_id' => $user2->id,
            'property_id' => $property->id,
        ]);

        Sanctum::actingAs($user3);

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Declined',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_tour_endpoints()
    {
        $response = $this->postJson("/api/tours/1/approve", ['tour_date' => 1]);
        $response->assertStatus(401);

        $response = $this->postJson("/api/tours/1/decline", ['message' => 'Declined']);
        $response->assertStatus(401);

        $response = $this->deleteJson("/api/tour/1");
        $response->assertStatus(401);
    }

    public function test_property_owner_can_approve_tour()
    {
        $user1 = User::factory()->create(); // Property owner
        $user2 = User::factory()->create(); // Tour requester

        $property = Property::factory()->create(['user_id' => $user1->id]);

        Sanctum::actingAs($user2);
        $tour = Tour::factory()->create([
            'user_id' => $user2->id,
            'property_id' => $property->id,
        ]);

        $tourDate = TourDate::create(['tour_id' => $tour->id, 'date' => now()]);

        Sanctum::actingAs($user1);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('approved', $tour->fresh()->status);
    }
}
