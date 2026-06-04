<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup necessary data for factories to work if they pluck from DB
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $tour = Tour::factory()->create();
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $tour = Tour::factory()->create();

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Declined',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_tour_of_another_landlord()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);

        $property = Property::factory()->create(['user_id' => $landlord1->id]);
        $tour = Tour::factory()->create(['property_id' => $property->id]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        $this->actingAs($landlord2, 'sanctum');

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_decline_tour_of_another_landlord()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);

        $property = Property::factory()->create(['user_id' => $landlord1->id]);
        $tour = Tour::factory()->create(['property_id' => $property->id]);

        $this->actingAs($landlord2, 'sanctum');

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Declined',
        ]);

        $response->assertStatus(403);
    }

    public function test_landlord_can_approve_their_own_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create(['property_id' => $property->id, 'status' => 'pending']);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        $this->actingAs($landlord, 'sanctum');

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('approved', $tour->fresh()->status);
    }

    public function test_landlord_can_decline_their_own_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create(['property_id' => $property->id, 'status' => 'pending']);

        $this->actingAs($landlord, 'sanctum');

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Sorry, not available',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('declined', $tour->fresh()->status);
    }
}
