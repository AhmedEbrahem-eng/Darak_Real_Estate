<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class TourAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup necessary data
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $response = $this->postJson('/api/tour/1/approve', ['tour_date' => 1]);
        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_decline_tour()
    {
        $response = $this->postJson('/api/tour/1/decline', ['message' => 'Sorry']);
        $response->assertStatus(401);
    }

    public function test_landlord_can_approve_their_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);
        $tourDate = TourDate::create([
            'tour_id' => $tour->id,
            'date' => now()->addDays(1)->format('Y-m-d H:i:s'),
        ]);

        Sanctum::actingAs($landlord);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", ['tour_date' => $tourDate->id]);

        $response->assertStatus(200);
        $this->assertEquals('approved', $tour->fresh()->status);
    }

    public function test_user_cannot_approve_someone_elses_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", ['tour_date' => 1]);

        $response->assertStatus(403);
    }

    public function test_landlord_can_decline_their_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($landlord);

        $response = $this->postJson("/api/tour/{$tour->id}/decline", ['message' => 'No thanks']);

        $response->assertStatus(200);
        $this->assertEquals('declined', $tour->fresh()->status);
    }

    public function test_user_cannot_decline_someone_elses_property_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/tour/{$tour->id}/decline", ['message' => 'No thanks']);

        $response->assertStatus(403);
    }

    public function test_requester_can_delete_their_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        $response = $this->deleteJson("/api/tour/{$tour->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tours', ['id' => $tour->id]);
    }

    public function test_landlord_can_delete_tour_on_their_property()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($landlord);

        $response = $this->deleteJson("/api/tour/{$tour->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tours', ['id' => $tour->id]);
    }

    public function test_admin_can_delete_any_tour()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $landlord = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/tour/{$tour->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tours', ['id' => $tour->id]);
    }

    public function test_unauthorized_user_cannot_delete_tour()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $requester = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        Sanctum::actingAs($requester);
        $tour = Tour::create([
            'user_id' => $requester->id,
            'property_id' => $property->id,
            'status' => 'pending'
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/tour/{$tour->id}");

        $response->assertStatus(403);
    }
}
