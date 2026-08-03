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
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'landlord']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
    }

    /** @test */
    public function guests_cannot_delete_property()
    {
        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function unauthorized_users_cannot_delete_someone_elses_property()
    {
        Sanctum::actingAs($this->otherUser);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Unauthorized']);
    }

    /** @test */
    public function property_owners_can_delete_their_own_properties()
    {
        Sanctum::actingAs($this->owner);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'property deleted successfuly']);
        $this->assertSoftDeleted('properties', ['id' => $this->property->id]);
    }

    /** @test */
    public function admins_can_delete_any_property()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/properties/{$this->property->id}");

        $response->assertStatus(200);
        $response->assertJson(['message' => 'property deleted successfuly']);
        $this->assertSoftDeleted('properties', ['id' => $this->property->id]);
    }
}
