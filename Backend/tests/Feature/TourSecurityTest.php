<?php

namespace Tests\Feature;

use App\Models\Notification;
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

    public function test_unauthenticated_user_cannot_access_tour_approval_or_decline()
    {
        $responseApprove = $this->postJson('/api/tours/1/approve', ['tour_date' => 1]);
        $responseApprove->assertStatus(401);

        $responseDecline = $this->postJson('/api/tours/1/decline', ['message' => 'No']);
        $responseDecline->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_or_decline_tour_of_another_landlord()
    {
        $landlord = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $renter = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($renter);

        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        Sanctum::actingAs($otherUser);

        $responseApprove = $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id]);
        $responseApprove->assertStatus(403);

        $responseDecline = $this->postJson("/api/tours/{$tour->id}/decline", ['message' => 'Declined']);
        $responseDecline->assertStatus(403);
    }

    public function test_landlord_can_approve_or_decline_tour()
    {
        $landlord = User::factory()->create(['role' => 'user']);
        $renter = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($renter);

        $property = Property::factory()->create(['user_id' => $landlord->id]);
        $tour = Tour::factory()->create([
            'property_id' => $property->id,
            'user_id' => $renter->id,
        ]);
        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        // Create initial notification expected by approveTour repository logic
        Notification::create([
            'from_user_id' => $renter->id,
            'to_user_id' => $landlord->id,
            'property_id' => $property->id,
            'tour_id' => $tour->id,
            'message' => 'Tour request',
            'type' => 'request',
            'status' => 'pending',
            'date' => now(),
        ]);

        Sanctum::actingAs($landlord);

        $responseApprove = $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id]);
        $responseApprove->assertStatus(200);
    }
}
