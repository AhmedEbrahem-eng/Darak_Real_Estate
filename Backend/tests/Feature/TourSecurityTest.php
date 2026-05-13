<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $response = $this->postJson('/api/tour/1/approve', ['tour_date' => 1]);
        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $attacker = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id
        ]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        Sanctum::actingAs($attacker);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", ['tour_date' => $tourDate->id]);

        // Currently it might return 200 or 404 because of missing auth check, but it should be 403.
        // For now let's just assert it's not 200 if we want it to fail.
        // Or if it's currently unprotected, it will return 200.
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $response = $this->postJson('/api/tour/1/decline', ['message' => 'Sorry']);
        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_decline_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $attacker = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id
        ]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->postJson("/api/tour/{$tour->id}/decline", ['message' => 'Sorry']);
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_delete_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $attacker = User::factory()->create(['role' => 'user']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id
        ]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($attacker);

        $response = $this->deleteJson("/api/tour/{$tour->id}");
        $response->assertStatus(403);
    }
}
