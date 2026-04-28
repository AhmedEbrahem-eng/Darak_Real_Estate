<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary data
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        // Clear session to be unauthenticated
        $this->app['auth']->forgetGuards();

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherLandlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        Sanctum::actingAs($otherLandlord);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        $this->app['auth']->forgetGuards();

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Sorry, not available'
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_decline_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherLandlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($otherLandlord);

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Sorry, not available'
        ]);

        $response->assertStatus(403);
    }
}
