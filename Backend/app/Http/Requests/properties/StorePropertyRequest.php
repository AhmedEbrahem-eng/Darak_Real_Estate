<?php

namespace App\Http\Requests\properties;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|unique:properties',
            'description' => 'required|string',
            'num_of_rooms' => 'required|integer',
            'num_of_bathrooms' => 'required|integer',
            'area' => 'required|numeric',
            'price' => 'required|integer',
            'property_type_id' => 'required|exists:property_types,id',
            'listing_type' => 'required|in:rent,buy',
            'amenities' => 'array',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'amenities.*' => 'exists:amenities,id',
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
