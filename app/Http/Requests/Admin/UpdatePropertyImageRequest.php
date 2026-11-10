<?php

namespace App\Http\Requests\Admin;

use App\Models\PropertyImage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyImageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var PropertyImage $image */
        $image = $this->route('image');

        return [
            'caption' => ['nullable', 'string', 'max:255'],
            'room_id' => ['nullable', 'integer', Rule::exists('rooms', 'id')->where('property_id', $image->property_id)],
        ];
    }
}
