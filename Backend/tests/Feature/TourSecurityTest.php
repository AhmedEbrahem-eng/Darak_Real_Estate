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
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    public function test_landlord_can_approve_their_own_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(2),
        ]);

        Sanctum::actingAs($landlord);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_landlord_cannot_approve_other_landlord_property_tour()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord1->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(2),
        ]);

        Sanctum::actingAs($landlord2);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_landlord_cannot_decline_other_landlord_property_tour()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord1->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($landlord2);

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Sorry, I cannot do it.',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_tour_approval()
    {
        $response = $this->postJson("/api/tours/1/approve", [
            'tour_date' => 1,
        ]);

        $response->assertStatus(401);
    }
}
