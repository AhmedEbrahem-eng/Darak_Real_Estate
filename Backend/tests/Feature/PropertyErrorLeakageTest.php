<?php

namespace Tests\Feature;

use App\Models\PropertyType;
use App\Models\User;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class PropertyErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_does_not_leak_exception_details(): void
    {
        Log::spy();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $propertyType = PropertyType::factory()->create();

        $mockRepository = Mockery::mock(PropertyRepositoryInterface::class);
        $mockRepository->shouldReceive('createProperty')
            ->once()
            ->andThrow(new \Exception('Sensitive DB error: table users column secret failed'));

        $this->app->instance(PropertyRepositoryInterface::class, $mockRepository);

        $response = $this->postJson('/api/properties', [
            'title' => 'Unique Luxury Apartment',
            'description' => 'A nice place to live',
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'area' => 120.5,
            'price' => 2500,
            'property_type_id' => $propertyType->id,
            'listing_type' => 'rent',
            'city' => 'Cairo',
            'state' => 'Cairo',
            'street' => 'Main Street',
            'images' => [
                UploadedFile::fake()->image('property.jpg')
            ],
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Failed to add property',
            'error' => 'An error occurred while processing your request.',
        ]);

        $response->assertJsonMissing([
            'error' => 'Sensitive DB error: table users column secret failed'
        ]);

        Log::shouldHaveReceived('error')
            ->once();
    }
}
