<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthorized_user_cannot_approve_tour()
    {
        // Landlord who owns the property
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        // Renter who requested the tour
        $renter = User::factory()->create(['role' => 'renter']);

        // Act as renter to create the tour (so Auth::id() is set for the observer)
        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        // Malicious user
        $attacker = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($attacker);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_decline_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        $renter = User::factory()->create(['role' => 'renter']);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
            'status' => 'pending'
        ]);

        $attacker = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($attacker);

        $response = $this->postJson("/api/tours/{$tour->id}/decline", [
            'message' => 'Declined by attacker'
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_delete_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        $renter = User::factory()->create(['role' => 'renter']);

        Sanctum::actingAs($renter);
        $tour = Tour::factory()->create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
            'status' => 'pending'
        ]);

        $attacker = User::factory()->create(['role' => 'renter']);
        Sanctum::actingAs($attacker);

        $response = $this->deleteJson("/api/tour/{$tour->id}");

        $response->assertStatus(403);
    }
}
