<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\Property;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createProperty()
    {
        $user = User::factory()->create();
        $location = Location::create([
            'city' => 'Test City',
            'state' => 'Test State',
            'street' => 'Test Street',
        ]);
        $type = PropertyType::create([
            'name' => 'Apartment',
        ]);

        return Property::create([
            'user_id' => $user->id,
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 2,
            'num_of_bathrooms' => 1,
            'listing_type' => 'rent',
            'availability' => 'available',
            'location_id' => $location->id,
            'property_type_id' => $type->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_wishlist_item()
    {
        $user = User::factory()->create();
        $property = $this->createProperty();
        $wishlistItem = Wishlist::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);

        $response = $this->deleteJson("/api/wishlist/{$wishlistItem->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_delete_another_users_wishlist_item()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = $this->createProperty();

        $wishlistItem = Wishlist::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        $token = $otherUser->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/wishlist/{$wishlistItem->id}");

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_own_wishlist_item()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $property = $this->createProperty();

        $wishlistItem = Wishlist::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        $token = $owner->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/wishlist/{$wishlistItem->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('wishlists', ['id' => $wishlistItem->id]);
    }

    public function test_admin_can_delete_any_wishlist_item()
    {
        $owner = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createProperty();

        $wishlistItem = Wishlist::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
        ]);

        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/wishlist/{$wishlistItem->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('wishlists', ['id' => $wishlistItem->id]);
    }
}
