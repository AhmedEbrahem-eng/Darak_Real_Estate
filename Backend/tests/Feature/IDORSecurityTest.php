<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IDORSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_cannot_delete_another_users_property()
    {
        $owner = User::factory()->create(['role' => 'landlord']);
        $otherUser = User::factory()->create(['role' => 'user']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'deleted_at' => null]);
    }

    /** @test */
    public function an_admin_can_delete_any_property()
    {
        $owner = User::factory()->create(['role' => 'landlord']);
        $admin = User::factory()->create(['role' => 'admin']);
        $property = Property::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    /** @test */
    public function a_user_cannot_update_another_users_review()
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $property = Property::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id, 'property_id' => $property->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/reviews/{$review->id}", [
            'property_id' => $property->id,
            'content' => 'Updated content',
            'rate' => 5,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function a_user_cannot_approve_a_tour_for_another_landlords_property()
    {
        $landlord = User::factory()->create(['role' => 'landlord']);
        $otherLandlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create(['user_id' => $landlord->id]);

        $tourRequester = User::factory()->create();
        Sanctum::actingAs($tourRequester);

        $tour = Tour::factory()->create([
            'property_id' => $property->id,
            'user_id' => $tourRequester->id,
        ]);

        Sanctum::actingAs($otherLandlord);

        $response = $this->postJson("/api/tours/{$tour->id}/approve", [
            'tour_date' => 1, // Dummy, should fail auth before validation
        ]);

        $response->assertStatus(403);
    }
}
