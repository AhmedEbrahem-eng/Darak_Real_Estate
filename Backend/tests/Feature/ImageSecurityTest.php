<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Location;
use App\Models\PropertyType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ImageSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $otherUser;
    protected $admin;
    protected $property;
    protected $image;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create(['role' => 'user']);
        $this->otherUser = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $location = Location::factory()->create();
        $propertyType = PropertyType::factory()->create();

        $this->property = Property::factory()->create([
            'user_id' => $this->owner->id,
            'location_id' => $location->id,
            'property_type_id' => $propertyType->id,
        ]);

        $this->image = PropertyImage::factory()->create([
            'property_id' => $this->property->id,
            'image' => 'images/test.jpg',
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_image()
    {
        $response = $this->deleteJson("/api/images/{$this->image->id}");
        $response->assertStatus(401);
    }

    public function test_non_owner_user_cannot_delete_image()
    {
        Sanctum::actingAs($this->otherUser);
        $response = $this->deleteJson("/api/images/{$this->image->id}");
        $response->assertStatus(403);
    }

    public function test_owner_can_delete_image()
    {
        Sanctum::actingAs($this->owner);
        $response = $this->deleteJson("/api/images/{$this->image->id}");
        $response->assertStatus(200);
    }

    public function test_admin_can_delete_image()
    {
        Sanctum::actingAs($this->admin);
        $response = $this->deleteJson("/api/images/{$this->image->id}");
        $response->assertStatus(200);
    }
}
