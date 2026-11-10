<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    /**
     * Normalise the form: the active switch to a boolean and the features
     * textarea (one per line) to a list.
     */
    protected function prepareForValidation(): void
    {
        $features = $this->input('features');

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'features' => is_string($features)
                ? collect(preg_split('/\r\n|\r|\n/', $features) ?: [])->map(fn (string $line): string => trim($line))->filter()->values()->all()
                : (array) $features,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'bed_type' => ['nullable', 'string', 'max:255'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'features' => ['array', 'max:20'],
            'features.*' => ['string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }
}
