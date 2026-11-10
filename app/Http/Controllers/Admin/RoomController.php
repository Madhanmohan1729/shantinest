<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomRequest;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class RoomController extends Controller
{
    /**
     * Add a room to a property.
     */
    public function store(RoomRequest $request, Property $property): RedirectResponse
    {
        $room = $property->rooms()->create([
            ...$request->validated(),
            'sort_order' => (int) $property->rooms()->max('sort_order') + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name added.', ['name' => $room->name])]);

        return back();
    }

    /**
     * Save a room.
     */
    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $room->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name saved.', ['name' => $room->name])]);

        return back();
    }

    /**
     * Delete a room with its holds. Its photos stay in the property gallery.
     */
    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name deleted.', ['name' => $room->name])]);

        return back();
    }
}
