<?php

namespace App\Repositories;

use App\Mail\PropertyUpdateApprovedMail;
use App\Mail\PropertyUpdateRejectedMail;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyUpdate;
use App\Models\User;
use App\Repositories\Contracts\PropertyRepositoryInterface;
use App\Utils\ImageUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Mail\PropertyStatusUpdateMail;
use Illuminate\Support\Facades\Mail;

class PropertyRepository implements PropertyRepositoryInterface
{
    public function getAllProperties()
    {
        return Property::with('images', 'location', 'amenities', 'propertyType', 'user')->where('status','accepted')->get();
    }

    public function getPropertyBySlug(string $slug)
    {
        return Property::where('slug', $slug)->with('location', 'images', 'amenities', 'propertyType', 'user')->firstOrFail();
    }

    public function getLatestProperties(string $listing_type)
    {
        return Property::with('location', 'images', 'amenities', 'propertyType')
            ->where('listing_type', $listing_type)
            ->where('status','accepted')
            ->latest()
            ->take(6)
            ->get();
    }
    public function getAcceptedProperties()
    {
        $properties = Property::where('status', 'accepted')
            ->with('images', 'location', 'amenities', 'propertyType', 'user')->get();
        if ($properties->isEmpty()) {
            return null;
        }

        return $properties;
    }
    public function getPendingProperties()
    {
        $properties = Property::where('status', 'pending')
            ->with('images', 'location', 'amenities', 'propertyType', 'user')->get();
        if ($properties->isEmpty()) {
            return null;
        }

        return $properties;
    }
    public function updateStatus(int $id, string $status)
    {
        $property = Property::find($id);

        if (!$property) {
            return null;
        }

        $property->status = $status;
        $property->save();

        $user = $property->user;

        if (!$user) {
            return null;
        }

        return $property;
    }

    public function createProperty(array $data)
    {
        DB::beginTransaction();

        try {
            $location = Location::firstOrCreate(
                [
                    'city' => $data['city'],
                    'state' => $data['state'],
                    'street' => $data['street']
                ],
                $data
            );
            $data['availability'] = 'available';
            $data['user_id'] = Auth::id();
            $slug = Str::slug($data['title']);
            $property = Property::create($data + ['slug' => $slug, 'location_id' => $location->id]);

            if (isset($data['images'])) {
                $uploadedImages = ImageUpload::uploadImages($data['images'], 'images/properties');
                foreach ($uploadedImages as $uploadedImage) {
                    PropertyImage::create([
                        'property_id' => $property->id,
                        'image' => $uploadedImage,
                    ]);
                }
            }

            if (isset($data['amenities'])) {
                $property->amenities()->attach($data['amenities']);
            }

            $property->load('location', 'propertyType', 'user', 'images', 'amenities');
            DB::commit();
            return $property;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }


    public function searchProperties(array $filters)
    {
        $query = Property::where('status','accepted')->with('images', 'location', 'amenities', 'propertyType', 'user');

        if (isset($filters['property_type'])) {
            $query->where('property_type_id', $filters['property_type']);
        }
        if (isset($filters['listing_type'])) {
            $query->where('listing_type', $filters['listing_type']);
        }
        if (isset($filters['num_of_rooms'])) {
            if ($filters['num_of_rooms'] === '+7') {
                $query->where('num_of_rooms', '>', 7);
            } else {
                $query->where('num_of_rooms', $filters['num_of_rooms']);
            }
        }
        if (isset($filters['num_of_bathrooms'])) {
            if ($filters['num_of_bathrooms'] === '+7') {
                $query->where('num_of_bathrooms', '>', 7);
            } else {
                $query->where('num_of_bathrooms', $filters['num_of_bathrooms']);
            }
        }
        if (isset($filters['price'])) {
            $query->where('price', $filters['price']);
        }
        if (isset($filters['location'])) {
            $searchTerms = explode(' ', $filters['location']);
            $query->whereHas('location', function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($query) use ($term) {
                        $query->where('city', 'like', '%' . $term . '%')
                              ->orWhere('state', 'like', '%' . $term . '%')
                              ->orWhere('street', 'like', '%' . $term . '%');
                    });
                }
            });
        }
        if (isset($filters['min_price']) && isset($filters['max_price'])) {
            $query->whereBetween('price', [$filters['min_price'], $filters['max_price']]);
        } elseif (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        } elseif (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (isset($filters['amenities']) && is_array($filters['amenities'])) {
            $query->whereHas('amenities', function ($q) use ($filters) {
                $q->whereIn('amenities.id', $filters['amenities']);
            });
        }

        return $query->get();
    }

    public function showUserProperties(int $id)
    {
        return Property::where('user_id', $id)->whereIn('status',['accepted','pending'])->with('images', 'location', 'amenities', 'propertyType', 'user')->get();
    }

    public function getPropertyById(int $id)
    {
        return Property::find($id);
    }

    public function updateProperty(array $data, string $slug)
    {
        return DB::transaction(function () use ($data, $slug) {
            $property = Property::where('slug', $slug)->first();

            if (!$property) {
                return null;
            }

            PropertyUpdate::create([
                'property_id' => $property->id,
                'data' => $data,
            ]);

            $admin = User::where('role', 'admin')->first();

            if (!$admin) {
                return null;
            }

            Notification::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $admin->id,
                'property_id' => $property->id,
                'message' => 'Property update requires approval',
                'type' => 'update_request',
                'date' => now(),
            ]);

            return $property;
        });
    }

    public function delete($property)
    {
        if (!$property instanceof Property) {
            $property = Property::find($property);
        }

        if (!$property) {
            return null;
        }
        $property->delete();
        return true;
    }
}
