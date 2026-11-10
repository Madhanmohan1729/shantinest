<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyRequest extends FormRequest
{
    /**
     * Normalise the form: slug from the name, the publish switch to a
     * boolean and the house rules textarea (one per line) to a list.
     */
    protected function prepareForValidation(): void
    {
        $houseRules = $this->input('house_rules');

        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('name'))),
            'is_published' => $this->boolean('is_published'),
            'amenities' => array_values((array) $this->input('amenities', [])),
            'house_rules' => is_string($houseRules)
                ? collect(preg_split('/\r\n|\r|\n/', $houseRules) ?: [])->map(fn (string $line): string => trim($line))->filter()->values()->all()
                : (array) $houseRules,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Property|null $property */
        $property = $this->route('property');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('properties', 'slug')->ignore($property?->id)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'property_type' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:100'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:100'],
            'beds' => ['required', 'integer', 'min:0', 'max:100'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:100'],
            'amenities' => ['array'],
            'amenities.*' => ['string', Rule::in(array_keys(Property::AMENITIES))],
            'house_rules' => ['array', 'max:30'],
            'house_rules.*' => ['string', 'max:255'],
            'check_in_time' => ['required', 'date_format:H:i'],
            'check_out_time' => ['required', 'date_format:H:i'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_published' => ['boolean'],
        ];
    }
}
