<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStayRequestRequest;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Room;
use App\Models\RoomHold;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    /**
     * The landing page with every published property.
     */
    public function index(): Response
    {
        $properties = Property::query()
            ->published()
            ->with(['images.media'])
            ->withCount(['rooms' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Property $property): array => [
                ...self::summary($property),
                'roomsCount' => $property->rooms_count,
                'images' => $property->images->take(5)->map(fn (PropertyImage $image): array => $image->toPublicArray())->values()->all(),
            ]);

        return Inertia::render('Welcome', [
            'properties' => $properties,
        ]);
    }

    /**
     * One property with its photos, rooms and the times each room is held.
     */
    public function show(Property $property): Response
    {
        abort_unless($property->is_published, 404);

        $property->load([
            'images.media',
            'rooms' => fn ($query) => $query->where('is_active', true),
            'rooms.images.media',
            'rooms.holds' => fn ($query) => $query->upcoming()->orderBy('starts_at'),
        ]);

        return Inertia::render('properties/Show', [
            'property' => [
                ...self::summary($property),
                'description' => $property->description,
                'address' => $property->address,
                'latitude' => $property->latitude === null ? null : (float) $property->latitude,
                'longitude' => $property->longitude === null ? null : (float) $property->longitude,
                'amenities' => collect($property->amenities ?? [])
                    ->filter(fn (string $key): bool => array_key_exists($key, Property::AMENITIES))
                    ->map(fn (string $key): array => ['key' => $key, 'label' => Property::AMENITIES[$key]])
                    ->values()
                    ->all(),
                'houseRules' => $property->house_rules ?? [],
                'checkInTime' => substr($property->check_in_time, 0, 5),
                'checkOutTime' => substr($property->check_out_time, 0, 5),
                'checkInLabel' => self::timeLabel($property->check_in_time),
                'checkOutLabel' => self::timeLabel($property->check_out_time),
                'contactPhone' => $property->contact_phone,
                'contactEmail' => $property->contact_email,
            ],
            'images' => $property->images->map(fn (PropertyImage $image): array => $image->toPublicArray())->all(),
            'rooms' => $property->rooms->map(fn (Room $room): array => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'bedType' => $room->bed_type,
                'maxGuests' => $room->max_guests,
                'features' => $room->features ?? [],
                'images' => $room->images->map(fn (PropertyImage $image): array => $image->toPublicArray())->all(),
                'holds' => $room->holds->map(fn (RoomHold $hold): array => [
                    'startsAt' => $hold->starts_at->format('Y-m-d\TH:i'),
                    'endsAt' => $hold->ends_at->format('Y-m-d\TH:i'),
                ])->all(),
            ])->all(),
            'maxNights' => StoreStayRequestRequest::MAX_NIGHTS,
            'today' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * The fields shown on property cards and headers.
     *
     * @return array<string, mixed>
     */
    public static function summary(Property $property): array
    {
        return [
            'id' => $property->id,
            'name' => $property->name,
            'slug' => $property->slug,
            'tagline' => $property->tagline,
            'propertyType' => $property->property_type,
            'location' => $property->location(),
            'city' => $property->city,
            'maxGuests' => $property->max_guests,
            'bedrooms' => $property->bedrooms,
            'beds' => $property->beds,
            'bathrooms' => $property->bathrooms,
        ];
    }

    /**
     * A time such as "14:00" as guests read it, e.g. "2:00 PM".
     */
    public static function timeLabel(string $time): string
    {
        return Carbon::createFromFormat('H:i', substr($time, 0, 5))->format('g:i A');
    }
}
