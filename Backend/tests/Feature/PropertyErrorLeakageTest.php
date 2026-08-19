<?php

namespace Tests\Feature;

use App\Models\PropertyType;
use App\Models\User;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class PropertyErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_store_exception_does_not_leak_error_message()
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $propertyType = PropertyType::create(['name' => 'Apartment']);

        $mockRepository = Mockery::mock(PropertyRepositoryInterface::class);
        $mockRepository->shouldReceive('createProperty')
            ->once()
            ->andThrow(new \Exception('Sensitive DB Connection String Error: password=Secret123'));

        $this->app->instance(PropertyRepositoryInterface::class, $mockRepository);

        $payload = [
            'title' => 'Test Property',
            'description' => 'Test Description',
            'price' => 1000,
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
            ]
        ];

        $response = $this->postJson('/api/properties', $payload);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'An error occurred while creating the property.'
        ]);
        $response->assertJsonMissing(['error' => 'Sensitive DB Connection String Error: password=Secret123']);
        $this->assertStringNotContainsString('Sensitive DB Connection String Error', $response->getContent());
    }
}
