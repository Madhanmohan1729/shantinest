<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStayRequestRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StayRequestController extends Controller
{
    /**
     * Record a guest's request to stay in one or more rooms.
     */
    public function store(StoreStayRequestRequest $request, Property $property): RedirectResponse
    {
        abort_unless($property->is_published, 404);

        $stayRequest = DB::transaction(function () use ($request, $property) {
            $stayRequest = $property->stayRequests()->create($request->safe()->only([
                'name', 'email', 'phone', 'guests', 'check_in', 'check_out', 'message',
            ]));

            $stayRequest->rooms()->sync($request->validated('room_ids'));

            return $stayRequest;
        });

        Inertia::flash('stayRequest', [
            'reference' => $stayRequest->reference,
            'name' => $stayRequest->name,
        ]);

        return to_route('properties.show', $property);
    }
}
