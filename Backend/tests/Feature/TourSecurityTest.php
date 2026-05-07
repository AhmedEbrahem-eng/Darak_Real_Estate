<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $location = \App\Models\Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = \App\Models\PropertyType::create(['name' => 'House']);
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);
        $this->app['auth']->forgetUser();
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(1)->toDateString(),
        ]);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        // It should at least require authentication
        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_tour()
    {
        $location = \App\Models\Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = \App\Models\PropertyType::create(['name' => 'House']);
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(1)->toDateString(),
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", [
            'tour_date' => $tourDate->id,
        ]);

        // Should be forbidden if not the landlord or admin
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $location = \App\Models\Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = \App\Models\PropertyType::create(['name' => 'House']);
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);
        $this->app['auth']->forgetUser();

        $response = $this->postJson("/api/tour/{$tour->id}/decline", [
            'message' => 'Sorry, I am busy.',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_decline_tour()
    {
        $location = \App\Models\Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        $propertyType = \App\Models\PropertyType::create(['name' => 'House']);
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create([
            'user_id' => $landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tour/{$tour->id}/decline", [
            'message' => 'Sorry, I am busy.',
        ]);

        $response->assertStatus(403);
    }
}
