<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomHoldRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomHold;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomHoldController extends Controller
{
    /**
     * How many days the hold board shows at once.
     */
    public const int BOARD_DAYS = 14;

    /**
     * The hold board: every room's holds over two weeks.
     */
    public function index(Request $request): Response
    {
        $from = rescue(
            fn () => CarbonImmutable::createFromFormat('Y-m-d', (string) $request->query('from'))->startOfDay(),
            fn () => CarbonImmutable::today(),
            report: false,
        );
        $until = $from->addDays(self::BOARD_DAYS);

        $properties = Property::query()
            ->with([
                'rooms',
                'rooms.holds' => fn ($query) => $query->overlapping($from, $until)->with('stayRequest')->orderBy('starts_at'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Property $property): array => [
                'id' => $property->id,
                'name' => $property->name,
                'checkInTime' => substr($property->check_in_time, 0, 5),
                'checkOutTime' => substr($property->check_out_time, 0, 5),
                'rooms' => $property->rooms->map(fn (Room $room): array => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'isActive' => $room->is_active,
                    'holds' => $room->holds->map(fn (RoomHold $hold): array => self::present($hold))->all(),
                ])->all(),
            ]);

        return Inertia::render('admin/holds/Index', [
            'properties' => $properties,
            'from' => $from->format('Y-m-d'),
            'days' => self::BOARD_DAYS,
            'today' => CarbonImmutable::today()->format('Y-m-d'),
            'can' => [
                'manage' => $request->user()->can('manage holds'),
            ],
        ]);
    }

    /**
     * Hold a room for a stretch of time.
     */
    public function store(RoomHoldRequest $request): RedirectResponse
    {
        $hold = RoomHold::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':room is now held.', ['room' => $hold->room->name])]);

        return back();
    }

    /**
     * Change a hold's room, times or note.
     */
    public function update(RoomHoldRequest $request, RoomHold $hold): RedirectResponse
    {
        $hold->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Hold updated.')]);

        return back();
    }

    /**
     * Release a hold so the room can be requested again.
     */
    public function destroy(RoomHold $hold): RedirectResponse
    {
        $hold->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':room has been released.', ['room' => $hold->room->name])]);

        return back();
    }

    /**
     * A hold as the admin screens show it.
     *
     * @return array<string, mixed>
     */
    public static function present(RoomHold $hold): array
    {
        return [
            'id' => $hold->id,
            'roomId' => $hold->room_id,
            'startsAt' => $hold->starts_at->format('Y-m-d\TH:i'),
            'endsAt' => $hold->ends_at->format('Y-m-d\TH:i'),
            'guestName' => $hold->guest_name,
            'note' => $hold->note,
            'stayRequestReference' => $hold->stayRequest?->reference,
        ];
    }
}
