<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use App\Models\Property;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class SecuritySentinelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed basic data if needed, or create on the fly
    }

    /** @test */
    public function guests_cannot_access_administrative_endpoints()
    {
        $this->getJson('/api/dashboard/home')->assertStatus(401);
        $this->getJson('/api/amenities')->assertStatus(401);
        $this->getJson('/api/report-users')->assertStatus(401);
        $this->getJson('/api/report-properties')->assertStatus(401);
        $this->getJson('/api/reason-report')->assertStatus(401);
    }

    /** @test */
    public function regular_users_cannot_access_administrative_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard/home')->assertStatus(403);
        $this->getJson('/api/amenities')->assertStatus(403);
        $this->getJson('/api/report-users')->assertStatus(403);
        $this->getJson('/api/report-properties')->assertStatus(403);
        $this->getJson('/api/reason-report')->assertStatus(403);
    }

    /** @test */
    public function property_types_index_and_show_are_public()
    {
        $propertyType = PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment']);
        $this->getJson('/api/property-types')->assertStatus(200);
        $this->getJson("/api/property-types/{$propertyType->slug}")->assertStatus(200);
    }

    /** @test */
    public function regular_users_cannot_create_property_types()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);
        $this->postJson('/api/property-types', ['name' => 'Villa'])->assertStatus(403);
    }

    /** @test */
    public function user_cannot_delete_others_review_idor()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $location = Location::create(['city' => 'Test', 'state' => 'Test', 'street' => 'Test']);
        $propertyType = PropertyType::create(['name' => 'House']);
        $property = Property::create([
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available'
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Nice property',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        // Protected: User2 cannot delete User1's review
        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'deleted_at' => null]);
    }

    /** @test */
    public function user_cannot_update_others_review_idor()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $location = Location::create(['city' => 'Test', 'state' => 'Test', 'street' => 'Test']);
        $propertyType = PropertyType::create(['name' => 'House']);
        $property = Property::create([
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available'
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Nice property',
            'rate' => 5
        ]);

        Sanctum::actingAs($user2);

        // Protected: User2 cannot update User1's review
        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Hacked content',
            'rate' => 1
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'content' => 'Nice property'
        ]);
    }

    /** @test */
    public function admin_can_delete_any_review()
    {
        $user1 = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $location = Location::create(['city' => 'Test', 'state' => 'Test', 'street' => 'Test']);
        $propertyType = PropertyType::create(['name' => 'House']);
        $property = Property::create([
            'user_id' => $user1->id,
            'property_type_id' => $propertyType->id,
            'location_id' => $location->id,
            'title' => 'Test Property',
            'description' => 'Test',
            'price' => 1000,
            'area' => 100,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'rent',
            'availability' => 'available'
        ]);

        $review = Review::create([
            'user_id' => $user1->id,
            'property_id' => $property->id,
            'content' => 'Nice property',
            'rate' => 5
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
