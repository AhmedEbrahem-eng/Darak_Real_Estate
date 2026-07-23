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
    protected $otherUser;
    protected $admin;
    protected $location;
    protected $propertyType;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard pre-requisites
        $this->owner = User::factory()->create(['role' => 'landlord']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->location = Location::factory()->create();
        $this->propertyType = PropertyType::factory()->create();

        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $this->location->id,
            'property_type_id' => $this->propertyType->id,
        ]);
    }

    /**
     * Test that an unauthenticated user cannot delete a property.
     */
    public function test_unauthenticated_user_cannot_delete_property()
    {
        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(401);
    }

    /**
     * Test that an authenticated user who is not the owner or admin cannot delete the property.
     */
    public function test_unauthorized_user_cannot_delete_property()
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(403);
        $response->assertJsonFragment([
            'message' => 'You are not authorized to delete this property',
        ]);
    }

    /**
     * Test that the owner of the property can delete the property.
     */
    public function test_owner_can_delete_property()
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'property deleted successfuly',
        ]);

        $this->assertSoftDeleted('properties', [
            'id' => $this->property->id,
        ]);
    }

    /**
     * Test that an admin can delete any property.
     */
    public function test_admin_can_delete_any_property()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'message' => 'property deleted successfuly',
        ]);

        $this->assertSoftDeleted('properties', [
            'id' => $this->property->id,
        ]);
    }
}
