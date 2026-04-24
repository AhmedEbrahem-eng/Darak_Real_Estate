<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\PropertyType;
use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_tour_approval_requires_authentication()
    {
        $response = $this->postJson('/api/tours/1/approve', ['tour_date' => 1]);
        $response->assertStatus(401);
    }

    public function test_only_landlord_can_approve_tour()
    {
        $landlord = User::factory()->create();
        $otherUser = User::factory()->create();
        $renter = User::factory()->create();

        $location = Location::factory()->create();
        $type = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $type->id,
        ]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create(['property_id' => $property->id, 'user_id' => $renter->id]);
        $tourDate = TourDate::create(['tour_id' => $tour->id, 'date' => now()->addDay()]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id]);
        $response->assertStatus(403);
    }

    public function test_landlord_can_approve_their_tour()
    {
        $landlord = User::factory()->create();
        $renter = User::factory()->create();

        $location = Location::factory()->create();
        $type = PropertyType::factory()->create();

        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $type->id,
        ]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create(['property_id' => $property->id, 'user_id' => $renter->id]);
        $tourDate = TourDate::create(['tour_id' => $tour->id, 'date' => now()->addDay()]);

        Sanctum::actingAs($landlord);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id]);
        $response->assertStatus(200);
    }
}
