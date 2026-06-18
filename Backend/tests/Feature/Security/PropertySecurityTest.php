<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\Property;
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

        // Seed necessary data for PropertyFactory to work
        Location::factory()->create();
        PropertyType::factory()->create();
    }

    /** @test */
    public function an_unauthenticated_user_cannot_delete_any_property()
    {
        $owner = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->assertDatabaseHas('properties', ['id' => $property->id]);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(401);
        $this->assertNotSoftDeleted('properties', ['id' => $property->id]);
    }

    /** @test */
    public function an_authenticated_user_cannot_delete_another_users_property()
    {
        $owner = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($otherUser, 'sanctum');

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
        $this->assertNotSoftDeleted('properties', ['id' => $property->id]);
    }

    /** @test */
    public function an_owner_can_delete_their_own_property()
    {
        $owner = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner, 'sanctum');

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    /** @test */
    public function an_admin_can_delete_any_property()
    {
        $owner = User::factory()->create(['role' => 'landlord']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($admin, 'sanctum');

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    /** @test */
    public function a_user_cannot_register_as_an_admin()
    {
        $userData = [
            'first_name' => 'Bad',
            'last_name' => 'Actor',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone_number' => '01012345678',
            'role' => 'admin',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', [
            'email' => 'hacker@example.com',
        ]);
    }
}
