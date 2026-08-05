<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\properties\StorePropertyRequest;
use App\Http\Requests\properties\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    protected $propertyRepository;

    public function __construct(PropertyRepositoryInterface $propertyRepository)
    {
        $this->propertyRepository = $propertyRepository;
    }

    public function index(Request $request)
    {
        $properties = $this->propertyRepository->getAllProperties();
        return PropertyResource::collection($properties);
    }

    public function show($slug)
    {
        $property = new PropertyResource($this->propertyRepository->getPropertyBySlug($slug));
        return response()->json(['message' => 'Property fetched successfully', 'data' => $property], 200);
    }

    public function showLatestRent()
    {
        return $this->showLatestProperties('rent');
    }

    public function showLatestBuy()
    {
        return $this->showLatestProperties('buy');
    }

    private function showLatestProperties($listing_type)
    {
        $latestProperties = $this->propertyRepository->getLatestProperties($listing_type);

        if ($latestProperties->isEmpty()) {
            return response()->json(['message' => 'No properties found for ' . $listing_type ], 404);
        }

        return response()->json(['message' => 'Latest ' . $listing_type . ' properties fetched successfully', 'properties' => PropertyResource::collection($latestProperties)], 200);
    }
    public function showAcceptedProperties()
    {
        $properties = $this->propertyRepository->getAcceptedProperties();
        if (!$properties) {
            return response()->json(['message' => 'No accepted properties found.'], 404);
        }
        return PropertyResource::collection($properties);
    }
    public function showPendingProperties()
    {
        $properties = $this->propertyRepository->getPendingProperties();
        if (!$properties) {
            return response()->json(['message' => 'No pending properties found.'], 404);
        }
        return PropertyResource::collection($properties);
    }
    public function changePropertyStatus(Request $request, $propertyId)
    {
        $status = $request->input('status');

        $updatedProperty = $this->propertyRepository->updateStatus($propertyId, $status);
        if (!$updatedProperty) {
            return response()->json(['message' => 'Property not found', 'property' => $updatedProperty]);
        }
        return response()->json(['message' => 'Property status updated successfully.', 'property' => $updatedProperty]);
    }
    public function store(StorePropertyRequest $request)
    {
        try {
            $property = $this->propertyRepository->createProperty($request->validated());
            return response()->json(['message' => 'Property added successfully', 'data' => new PropertyResource($property)], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add property', 'error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $filters = $request->only(['property_type', 'listing_type', 'location', 'num_of_rooms', 'num_of_bathrooms', 'min_price', 'max_price', 'amenities']);
        $properties = $this->propertyRepository->searchProperties($filters);

        if ($properties->isEmpty()) {
            return response()->json(['message' => 'No Result found'], 404);
        }

        return response()->json(['data' => PropertyResource::collection($properties)]);
    }
    public function showUserProperties()
    {
        $properties = $this->propertyRepository->showUserProperties(Auth::id());
        if ($properties->isEmpty()) {
            return response()->json(['message' => 'No Result found'], 404);
        }
        return response()->json(['data' => PropertyResource::collection($properties)]);
    }
    public function update(UpdatePropertyRequest $request, $slug)
    {
        $validatedData = $request->validated();
        $property = $this->propertyRepository->updateProperty($validatedData, $slug);
        if(!$property){ 
            return response()->json(['message' => 'Property or admin not found'], 400); 
        }
        return response()->json(['message' => 'Property updated successfully', 'data' => new PropertyResource($property)], 200);
    }

    public function deleteProperty($propertyId)
    {
        $property = \App\Models\Property::find($propertyId);
        if (!$property) {
            return response()->json(['message' => 'property not found'], 404);
        }

        // Only the owner or an admin can delete the property
        if (Auth::user()->role !== 'admin' && Auth::id() !== $property->user_id) {
            return response()->json(['message' => 'You are not authorized to delete this property'], 403);
        }

        $deleted = $this->propertyRepository->delete($propertyId);
        if ($deleted) {
            return response()->json(['message' => 'property deleted successfuly'], 200);
        } else {
            return response()->json(['message' => 'property not found'], 404);
        }
    }
}
