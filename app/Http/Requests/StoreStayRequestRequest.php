<?php

namespace App\Http\Requests;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStayRequestRequest extends FormRequest
{
    /**
     * The longest stay a guest may request, in nights.
     */
    public const int MAX_NIGHTS = 60;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $property = $this->property();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+()\-\s]+$/'],
            'guests' => ['required', 'integer', 'min:1', 'max:'.$property->max_guests],
            'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['integer', 'distinct', Rule::exists('rooms', 'id')->where('property_id', $property->id)->where('is_active', true)],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Refuse stays that are too long or ask for a room that is already held.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $property = $this->property();
                $checkIn = $property->checkInOn($this->string('check_in')->toString());
                $checkOut = $property->checkOutOn($this->string('check_out')->toString());

                if ($checkIn->startOfDay()->diffInDays($checkOut->startOfDay()) > self::MAX_NIGHTS) {
                    $validator->errors()->add('check_out', __('Stays can be at most :count nights.', ['count' => self::MAX_NIGHTS]));

                    return;
                }

                $heldRooms = Room::query()
                    ->whereIn('id', (array) $this->input('room_ids'))
                    ->get()
                    ->reject(fn (Room $room): bool => $room->isFreeBetween($checkIn, $checkOut));

                if ($heldRooms->isNotEmpty()) {
                    $validator->errors()->add('room_ids', __(':rooms is not available for these dates. Please pick other dates or rooms.', [
                        'rooms' => $heldRooms->pluck('name')->join(', ', ' and '),
                    ]));
                }
            },
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'room_ids' => 'rooms',
            'check_in' => 'check-in date',
            'check_out' => 'check-out date',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'room_ids.required' => __('Please choose at least one room.'),
            'guests.max' => __('This home welcomes up to :max guests.'),
        ];
    }

    /**
     * The property the guest is asking about.
     */
    protected function property(): Property
    {
        /** @var Property $property */
        $property = $this->route('property');

        return $property;
    }
}
