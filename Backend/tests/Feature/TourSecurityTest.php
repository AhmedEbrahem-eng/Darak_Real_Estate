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
use Laravel\Sanctum\Sanctum;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure locations and property types exist for factories
        Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        PropertyType::create(['name' => 'Test Type']);
    }

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $response = $this->postJson('/api/tours/1/approve', ['tour_date' => 1]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $response = $this->postJson('/api/tours/1/decline', ['message' => 'Declined']);

        $response->assertStatus(401);
    }

    public function test_unauthorized_landlord_cannot_approve_tour_of_another_landlord()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($renter);

        $property = Property::factory()->create(['user_id' => $landlord1->id]);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id, 'date' => now()]);

        Sanctum::actingAs($landlord2);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_landlord_cannot_decline_tour_of_another_landlord()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($renter);

        $property = Property::factory()->create(['user_id' => $landlord1->id]);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($landlord2);

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Declined by wrong landlord'
        ]);

        $response->assertStatus(403);
    }
}
