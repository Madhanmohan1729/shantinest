<?php

namespace App\Actions\StayRequests;

use App\Enums\StayRequestStatus;
use App\Models\Room;
use App\Models\StayRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Approve a stay request and hold each requested room from check-in to
 * check-out, so nobody else can request those rooms for the same time.
 */
class ApproveStayRequest
{
    /**
     * @throws ValidationException when the request was already answered or a room is held by someone else
     */
    public function handle(StayRequest $stayRequest, User $admin, ?string $adminNote = null): void
    {
        DB::transaction(function () use ($stayRequest, $admin, $adminNote): void {
            $stayRequest = StayRequest::query()->with(['property', 'rooms'])->lockForUpdate()->findOrFail($stayRequest->id);

            if ($stayRequest->status !== StayRequestStatus::Pending) {
                throw ValidationException::withMessages(['status' => __('This request has already been answered.')]);
            }

            $startsAt = $stayRequest->property->checkInOn($stayRequest->check_in);
            $endsAt = $stayRequest->property->checkOutOn($stayRequest->check_out);

            $heldRooms = $stayRequest->rooms->reject(fn (Room $room): bool => $room->isFreeBetween($startsAt, $endsAt));

            if ($heldRooms->isNotEmpty()) {
                throw ValidationException::withMessages(['rooms' => __(':rooms is already held for part of this stay. Free it first or decline the request.', [
                    'rooms' => $heldRooms->pluck('name')->join(', ', ' and '),
                ])]);
            }

            foreach ($stayRequest->rooms as $room) {
                $room->holds()->create([
                    'stay_request_id' => $stayRequest->id,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'guest_name' => $stayRequest->name,
                    'note' => __('Stay request :reference', ['reference' => $stayRequest->reference]),
                    'created_by' => $admin->id,
                ]);
            }

            $stayRequest->update([
                'status' => StayRequestStatus::Approved,
                'admin_note' => $adminNote,
                'responded_by' => $admin->id,
                'responded_at' => now(),
            ]);
        });
    }
}
