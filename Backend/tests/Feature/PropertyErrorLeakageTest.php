<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PropertyType;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PropertyErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_handles_exceptions_securely_without_leaking_details(): void
    {
        Log::spy();

        $mockRepository = \Mockery::mock(PropertyRepositoryInterface::class);
        $mockRepository->shouldReceive('createProperty')
            ->once()
            ->andThrow(new \Exception('Sensitive DB Connection String: mysql://admin:secret@localhost:3306/db'));

        $this->app->instance(PropertyRepositoryInterface::class, $mockRepository);

        $user = User::factory()->create();
        $propertyType = PropertyType::create(['name' => 'Apartment', 'slug' => 'apartment']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/properties', [
                'title' => 'Test Property Error',
                'description' => 'Test Description',
                'num_of_rooms' => 2,
                'num_of_bathrooms' => 1,
                'area' => 100,
                'price' => 1000,
                'property_type_id' => $propertyType->id,
                'listing_type' => 'rent',
                'city' => 'City',
                'state' => 'State',
                'street' => 'Street',
                'images' => [
                    \Illuminate\Http\UploadedFile::fake()->image('test.jpg')
                ],
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'An error occurred while adding the property'
        ]);
        $response->assertJsonMissing([
            'error' => 'Sensitive DB Connection String: mysql://admin:secret@localhost:3306/db'
        ]);

        Log::shouldHaveReceived('error')
            ->once();
    }
}
