<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use App\Models\RoomHold;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class RoomHoldRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Refuse a hold that overlaps another hold on the same room.
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

                /** @var RoomHold|null $hold */
                $hold = $this->route('hold');
                $room = Room::query()->findOrFail($this->integer('room_id'));

                if (! $room->isFreeBetween(Carbon::parse($this->input('starts_at')), Carbon::parse($this->input('ends_at')), $hold?->id)) {
                    $validator->errors()->add('starts_at', __(':room is already held for part of this time.', ['room' => $room->name]));
                }
            },
        ];
    }
}
