<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Auth;

class TourSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Location::create([
            'city' => 'Test City',
            'state' => 'Test State',
            'street' => 'Test Street'
        ]);
        PropertyType::create(['name' => 'Test Type']);
    }

    public function test_user_cannot_delete_someone_elses_tour()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $property = Property::factory()->create(['user_id' => $user1->id]);

        // Mock Auth::id() for observer
        Sanctum::actingAs($user1);
        $tour = Tour::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/tour/{$tour->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('tours', ['id' => $tour->id, 'deleted_at' => null]);
    }

    public function test_unauthenticated_user_cannot_approve_tour()
    {
        $response = $this->postJson("/api/tour/1/approve", ['tour_date' => 1]);
        $response->assertStatus(401);
    }

    public function test_non_admin_cannot_approve_tour()
    {
        $user = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);
        $tour = Tour::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/tour/{$tour->id}/approve", ['tour_date' => 1]);

        $response->assertStatus(403);
    }
}
