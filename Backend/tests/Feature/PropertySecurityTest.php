<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Location::create(['city' => 'Test City', 'state' => 'Test State', 'street' => 'Test Street']);
        PropertyType::create(['name' => 'Test Type', 'slug' => 'test-type']);
    }

    public function test_unauthenticated_user_cannot_delete_property()
    {
        $user = User::factory()->create();
        $property = Property::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_unauthorized_user_cannot_delete_someone_elses_property()
    {
        $user1 = User::factory()->create(['role' => 'landlord']);
        $user2 = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $user1->id]);

        \Laravel\Sanctum\Sanctum::actingAs($user2);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }

    public function test_owner_can_delete_property()
    {
        $user = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $user->id]);

        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_admin_can_delete_any_property()
    {
        $user = User::factory()->create(['role' => 'landlord']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $user->id]);

        \Laravel\Sanctum\Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }
}
