<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReorderPropertyImagesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Property $property */
        $property = $this->route('property');

        return [
            'image_ids' => ['required', 'array'],
            'image_ids.*' => ['integer', 'distinct', Rule::exists('property_images', 'id')->where('property_id', $property->id)],
        ];
    }
}
