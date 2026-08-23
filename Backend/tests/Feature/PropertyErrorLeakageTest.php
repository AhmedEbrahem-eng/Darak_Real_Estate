<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PropertyType;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PropertyErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_property_exception_returns_generic_error_and_does_not_leak_details()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $propertyType = PropertyType::create(['name' => 'Apartment']);

        $repositoryMock = Mockery::mock(PropertyRepositoryInterface::class);
        $repositoryMock->shouldReceive('createProperty')
            ->once()
            ->andThrow(new \Exception('Sensitive DB Connection string leaked: mysql://user:secret@localhost:3306/db'));

        $this->app->instance(PropertyRepositoryInterface::class, $repositoryMock);

        $payload = [
            'title' => 'Test Property',
            'description' => 'A property description',
            'price' => 100000,
            'area' => 120,
            'num_of_rooms' => 3,
            'num_of_bathrooms' => 2,
            'listing_type' => 'buy',
            'city' => 'Sample City',
            'state' => 'Sample State',
            'street' => '123 Sample Street',
            'property_type_id' => $propertyType->id,
            'images' => [
                UploadedFile::fake()->image('test.jpg')
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/properties', $payload);

        $response->assertStatus(500);
        $response->assertJson([
            'message' => 'Failed to add property',
            'error' => 'An unexpected error occurred.',
        ]);
        $response->assertJsonMissing(['error' => 'Sensitive DB Connection string leaked: mysql://user:secret@localhost:3306/db']);
    }
}
