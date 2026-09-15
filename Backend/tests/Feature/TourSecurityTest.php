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

    protected $landlord;
    protected $renter;
    protected $otherUser;
    protected $admin;
    protected $property;
    protected $tour;
    protected $tourDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->landlord = User::factory()->create(['role' => 'user']);
        $this->renter = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $this->property = Property::factory()->create([
            'user_id' => $this->landlord->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
            'title' => 'Test House',
        ]);

        $this->tour = Tour::create([
            'user_id' => $this->renter->id,
            'property_id' => $this->property->id,
            'status' => 'pending',
        ]);

        $this->tourDate = TourDate::create([
            'tour_id' => $this->tour->id,
            'date' => '2025-06-01 10:00:00',
            'approved' => false,
        ]);
    }

    public function test_unauthenticated_user_cannot_approve_or_decline_tour()
    {
        $responseApprove = $this->postJson("/api/tours/{$this->tour->id}/approve", [
            'tour_date' => $this->tourDate->id,
        ]);
        $responseApprove->assertStatus(401);

        $responseDecline = $this->postJson("/api/tours/{$this->tour->id}/decline", [
            'message' => 'Not available',
        ]);
        $responseDecline->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_approve_or_decline_tour()
    {
        Sanctum::actingAs($this->otherUser);

        $responseApprove = $this->postJson("/api/tours/{$this->tour->id}/approve", [
            'tour_date' => $this->tourDate->id,
        ]);
        $responseApprove->assertStatus(403);

        $responseDecline = $this->postJson("/api/tours/{$this->tour->id}/decline", [
            'message' => 'Not available',
        ]);
        $responseDecline->assertStatus(403);
    }

    public function test_landlord_can_approve_tour()
    {
        Sanctum::actingAs($this->landlord);

        $response = $this->postJson("/api/tours/{$this->tour->id}/approve", [
            'tour_date' => $this->tourDate->id,
        ]);
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_delete_tour()
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->deleteJson("/api/tour/{$this->tour->id}");
        $response->assertStatus(403);
    }

    public function test_requester_can_delete_tour()
    {
        Sanctum::actingAs($this->renter);

        $response = $this->deleteJson("/api/tour/{$this->tour->id}");
        $response->assertStatus(200);
    }
}
