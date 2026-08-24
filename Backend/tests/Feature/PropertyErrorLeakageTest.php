<?php

namespace Tests\Feature;

use App\Models\PropertyType;
use App\Models\User;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

use Tests\TestCase;

class PropertyErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_store_hides_raw_exception_message_on_failure(): void
    {
        $user = User::factory()->create();
        $propertyType = PropertyType::create(['name' => 'Apartment']);

        // Mock property repository to throw an exception with sensitive internal details
        $sensitiveErrorMessage = 'Database connection failure: SQLSTATE[HY000] [1045] Access denied for user root@localhost';

        $mockRepository = \Mockery::mock(PropertyRepositoryInterface::class);
        $mockRepository->shouldReceive('createProperty')
            ->once()
            ->andThrow(new \Exception($sensitiveErrorMessage));

        $this->app->instance(PropertyRepositoryInterface::class, $mockRepository);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/properties', [
                'title' => 'Test Property',
                'description' => 'Test Description',
                'price' => 100000,
                'area' => 120,
                'num_of_rooms' => 3,
                'num_of_bathrooms' => 2,
                'listing_type' => 'rent',
                'property_type_id' => $propertyType->id,
                'city' => 'Sample City',
                'state' => 'Sample State',
                'street' => 'Sample Street',
                'images' => [
                    UploadedFile::fake()->image('property1.jpg')
                ],
            ]);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'An error occurred while adding the property'
        ]);
        $response->assertJsonMissing([
            'error' => $sensitiveErrorMessage
        ]);
    }
}
