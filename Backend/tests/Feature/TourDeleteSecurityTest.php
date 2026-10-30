<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourDeleteSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createPropertyForUser($user)
    {
        $propertyType = PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment',
        ]);

        $location = Location::create([
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Main St',
        ]);

        return Property::create([
            'user_id' => $user->id,
            'title' => 'Test Property',
            'slug' => 'test-property',
            'description' => 'A test property',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'listing_type' => 'rent',
            'availability' => 'available',
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_tour()
    {
        $user = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($user);
        $tour = Tour::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $response = $this->deleteJson('/api/tour/' . $tour->id);

        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_delete_another_users_tour()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $attacker = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($owner);
        $tour = Tour::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $attacker->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/tour/' . $tour->id);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized action']);
    }

    public function test_tour_creator_can_delete_own_tour()
    {
        $creator = User::factory()->create(['role' => 'user']);
        $landlord = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($landlord);
        $tour = Tour::create([
            'user_id' => $creator->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $creator->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/tour/' . $tour->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'tour cancelled successfully']);
    }

    public function test_property_landlord_can_delete_tour()
    {
        $creator = User::factory()->create(['role' => 'user']);
        $landlord = User::factory()->create(['role' => 'user']);
        $property = $this->createPropertyForUser($landlord);
        $tour = Tour::create([
            'user_id' => $creator->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $landlord->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/tour/' . $tour->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'tour cancelled successfully']);
    }

    public function test_admin_can_delete_any_tour()
    {
        $creator = User::factory()->create(['role' => 'user']);
        $landlord = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createPropertyForUser($landlord);
        $tour = Tour::create([
            'user_id' => $creator->id,
            'property_id' => $property->id,
            'status' => 'pending',
        ]);

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/tour/' . $tour->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'tour cancelled successfully']);
    }
}
