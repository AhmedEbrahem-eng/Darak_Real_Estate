<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Location::create([
            'city' => 'Test City',
            'state' => 'Test State',
            'street' => 'Test Street'
        ]);
        PropertyType::create(['name' => 'Test Type']);
    }

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $response = $this->postJson('/api/tour/1/approve', ['tour_date' => 1]);
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $response = $this->postJson('/api/tour/1/decline', ['message' => 'Sorry']);
        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $renter = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => '2025-10-10'
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'You are not authorized to approve this tour']);
    }

    public function test_unauthorized_user_cannot_decline_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $renter = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tour/{$tour->id}/decline", [
            'message' => 'Decline message'
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'You are not authorized to decline this tour']);
    }

    public function test_landlord_can_approve_their_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => '2025-10-10'
        ]);

        Sanctum::actingAs($landlord);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Tour approved successfully']);
    }

    public function test_admin_can_approve_any_tour()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => '2025-10-10'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Tour approved successfully']);
    }
}
