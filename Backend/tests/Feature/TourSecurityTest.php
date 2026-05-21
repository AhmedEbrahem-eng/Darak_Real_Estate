<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_landlord_can_approve_their_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(1)
        ]);

        Sanctum::actingAs($landlord);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(200);
        $this->assertEquals('approved', $tour->fresh()->status);
    }

    public function test_other_landlord_cannot_approve_tour()
    {
        $landlord1 = User::factory()->create(['role' => 'landlord']);
        $landlord2 = User::factory()->create(['role' => 'landlord']);
        $renter = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord1->id]);

        Sanctum::actingAs($renter);
        $tour = Tour::create([
            'user_id' => $renter->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(1)
        ]);

        Sanctum::actingAs($landlord2);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => $tourDate->id
        ]);

        $response->assertStatus(403);
    }
}
