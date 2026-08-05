<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $nonOwner;
    protected $admin;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->owner = User::factory()->create(['role' => 'landlord']);
        $this->nonOwner = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create pre-requisites for PropertyFactory to avoid empty pluck errors
        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        // Create a property
        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_property()
    {
        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(401);
        $this->assertDatabaseHas('properties', ['id' => $this->property->id, 'deleted_at' => null]);
    }

    public function test_authenticated_non_owner_cannot_delete_property()
    {
        Sanctum::actingAs($this->nonOwner);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment(['message' => 'You are not authorized to delete this property']);
        $this->assertDatabaseHas('properties', ['id' => $this->property->id, 'deleted_at' => null]);
    }

    public function test_property_owner_can_delete_their_property()
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'property deleted successfuly']);

        // Assert property is soft-deleted
        $this->assertSoftDeleted('properties', ['id' => $this->property->id]);
    }

    public function test_admin_can_delete_any_property()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'property deleted successfuly']);

        // Assert property is soft-deleted
        $this->assertSoftDeleted('properties', ['id' => $this->property->id]);
    }

    public function test_delete_non_existent_property_returns_404()
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/properties/99999");

        $response->assertStatus(404);
        $response->assertJsonFragment(['message' => 'property not found']);
    }
}
