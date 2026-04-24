<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_can_update_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $location = Location::factory()->create();
        $type = PropertyType::factory()->create();
        $property = Property::factory()->create(['location_id' => $location->id, 'property_type_id' => $type->id]);

        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'Original content',
            'rate' => 5
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'content' => 'Malicious update',
            'rate' => 1,
            'property_id' => $property->id
        ]);

        $response->assertStatus(404);
        $this->assertEquals('Original content', $review->fresh()->content);
    }

    public function test_only_owner_can_delete_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $location = Location::factory()->create();
        $type = PropertyType::factory()->create();
        $property = Property::factory()->create(['location_id' => $location->id, 'property_type_id' => $type->id]);

        $review = Review::create([
            'user_id' => $owner->id,
            'property_id' => $property->id,
            'content' => 'To be deleted',
            'rate' => 5
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/reviews/{$review->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'deleted_at' => null]);
    }
}
