<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'renter']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        // Expectation: 401 Unauthorized or 403 Forbidden
        // Current state: Likely 200 or 404/500 if it fails for other reasons, but NOT 401.
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'renter']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Sorry, not available'
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthorized_landlord_cannot_approve_another_landlords_tour()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'renter']);

        $property = Property::factory()->create(['user_id' => $landlord1->id]);
        $tour = Tour::factory()->create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        $this->actingAs($landlord2, 'sanctum');

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard()
    {
        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'renter']);
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/dashboard/home');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_reports()
    {
        $response = $this->getJson('/api/report-users');
        $response->assertStatus(401);

        $response = $this->getJson('/api/report-properties');
        $response->assertStatus(401);
    }
}
