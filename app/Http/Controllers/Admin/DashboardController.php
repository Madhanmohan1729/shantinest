<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StayRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomHold;
use App\Models\StayRequest;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The admin overview: pending requests, today's holds and upcoming arrivals.
     */
    public function __invoke(): Response
    {
        $now = CarbonImmutable::now();

        $activeRooms = Room::query()->where('is_active', true)->count();
        $heldNow = RoomHold::query()->overlapping($now, $now->addMinute())->distinct('room_id')->count('room_id');

        return Inertia::render('Dashboard', [
            'stats' => [
                'properties' => Property::query()->count(),
                'rooms' => $activeRooms,
                'heldNow' => $heldNow,
                'freeNow' => max($activeRooms - $heldNow, 0),
                'pendingRequests' => StayRequest::query()->where('status', StayRequestStatus::Pending)->count(),
            ],
            'pendingRequests' => StayRequest::query()
                ->with(['property', 'rooms'])
                ->where('status', StayRequestStatus::Pending)
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (StayRequest $stayRequest): array => [
                    'id' => $stayRequest->id,
                    'reference' => $stayRequest->reference,
                    'name' => $stayRequest->name,
                    'property' => $stayRequest->property->name,
                    'rooms' => $stayRequest->rooms->pluck('name')->join(', '),
                    'checkIn' => $stayRequest->check_in->format('Y-m-d'),
                    'checkOut' => $stayRequest->check_out->format('Y-m-d'),
                    'guests' => $stayRequest->guests,
                    'createdAt' => $stayRequest->created_at?->toIso8601String(),
                ]),
            'upcomingHolds' => RoomHold::query()
                ->with(['room.property'])
                ->upcoming()
                ->where('starts_at', '<', $now->addDays(7))
                ->orderBy('starts_at')
                ->limit(8)
                ->get()
                ->map(fn (RoomHold $hold): array => [
                    'id' => $hold->id,
                    'room' => $hold->room->name,
                    'property' => $hold->room->property->name,
                    'guestName' => $hold->guest_name,
                    'startsAt' => $hold->starts_at->format('Y-m-d\TH:i'),
                    'endsAt' => $hold->ends_at->format('Y-m-d\TH:i'),
                    'isActive' => $hold->starts_at->lte($now),
                ]),
        ]);
    }
}
