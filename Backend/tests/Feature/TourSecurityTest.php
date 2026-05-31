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

    public function test_guest_cannot_approve_decline_or_delete_tour()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $tour = Tour::factory()->create(['user_id' => $user->id]);

        auth()->forgetUser(); // Be a guest

        $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => 1])->assertStatus(401);
        $this->postJson("/api/tours/{$tour->id}/decline", ['message' => 'Sorry'])->assertStatus(401);
        $this->deleteJson("/api/tour/{$tour->id}")->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        $requester = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($requester);
        $tour = Tour::factory()->create(['property_id' => $property->id, 'user_id' => $requester->id]);

        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        $attacker = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($attacker);

        $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id])
             ->assertStatus(403);
    }

    public function test_landlord_can_approve_their_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        $requester = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($requester);
        $tour = Tour::factory()->create(['property_id' => $property->id, 'user_id' => $requester->id, 'status' => 'pending']);

        $tourDate = TourDate::factory()->create(['tour_id' => $tour->id]);

        Sanctum::actingAs($landlord);

        $this->postJson("/api/tours/{$tour->id}/approve", ['tour_date' => $tourDate->id])
             ->assertStatus(200);

        $this->assertEquals('approved', $tour->fresh()->status);
    }

    public function test_user_cannot_delete_someone_elses_tour()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $tour = Tour::factory()->create(['user_id' => $user->id]);

        $attacker = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($attacker);

        $this->deleteJson("/api/tour/{$tour->id}")
             ->assertStatus(403);
    }

    public function test_requester_can_delete_their_own_tour()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $tour = Tour::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/tour/{$tour->id}")
             ->assertStatus(200);

        $this->assertSoftDeleted('tours', ['id' => $tour->id]);
    }
}
