<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PropertyRequest;
use App\Http\Requests\Admin\StorePropertyImagesRequest;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    /**
     * List every property.
     */
    public function index(Request $request): Response
    {
        $properties = Property::query()
            ->with(['images' => fn ($query) => $query->limit(1), 'images.media'])
            ->withCount(['rooms', 'images', 'stayRequests as pending_requests_count' => fn ($query) => $query->where('status', 'pending')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Property $property): array => [
                'id' => $property->id,
                'name' => $property->name,
                'slug' => $property->slug,
                'location' => $property->location(),
                'isPublished' => $property->is_published,
                'roomsCount' => $property->rooms_count,
                'imagesCount' => $property->images_count,
                'pendingRequestsCount' => $property->pending_requests_count,
                'coverUrl' => $property->images->first()?->media->url,
            ]);

        return Inertia::render('admin/properties/Index', [
            'properties' => $properties,
            'can' => [
                'create' => $request->user()->can('create properties'),
                'delete' => $request->user()->can('delete properties'),
            ],
        ]);
    }

    /**
     * Show the form for a new property.
     */
    public function create(): Response
    {
        return Inertia::render('admin/properties/Create', [
            'amenityOptions' => self::amenityOptions(),
        ]);
    }

    /**
     * Create a property.
     */
    public function store(PropertyRequest $request): RedirectResponse
    {
        $property = Property::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name created. Add rooms and photos next.', ['name' => $property->name])]);

        return to_route('admin.properties.edit', $property);
    }

    /**
     * Show a property with its details, photos and rooms.
     */
    public function edit(Request $request, Property $property): Response
    {
        $property->load(['images.media', 'rooms']);
        $user = $request->user();

        return Inertia::render('admin/properties/Edit', [
            'amenityOptions' => self::amenityOptions(),
            'property' => [
                'id' => $property->id,
                'name' => $property->name,
                'slug' => $property->slug,
                'tagline' => $property->tagline,
                'description' => $property->description,
                'propertyType' => $property->property_type,
                'address' => $property->address,
                'city' => $property->city,
                'state' => $property->state,
                'country' => $property->country,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
                'maxGuests' => $property->max_guests,
                'bedrooms' => $property->bedrooms,
                'beds' => $property->beds,
                'bathrooms' => $property->bathrooms,
                'amenities' => $property->amenities ?? [],
                'houseRules' => $property->house_rules ?? [],
                'checkInTime' => substr($property->check_in_time, 0, 5),
                'checkOutTime' => substr($property->check_out_time, 0, 5),
                'contactPhone' => $property->contact_phone,
                'contactEmail' => $property->contact_email,
                'isPublished' => $property->is_published,
            ],
            'images' => $property->images->map(fn (PropertyImage $image): array => [
                ...$image->toPublicArray(),
                'fileName' => $image->media->original_name,
            ])->all(),
            'rooms' => $property->rooms->map(fn (Room $room): array => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'bedType' => $room->bed_type,
                'maxGuests' => $room->max_guests,
                'features' => $room->features ?? [],
                'isActive' => $room->is_active,
            ])->all(),
            'maxKilobytes' => StorePropertyImagesRequest::MAX_KILOBYTES,
            'maxFiles' => StorePropertyImagesRequest::MAX_FILES,
            'can' => [
                'update' => $user->can('update properties'),
                'delete' => $user->can('delete properties'),
                'manageImages' => $user->can('manage property images'),
                'manageRooms' => $user->can('manage rooms'),
            ],
        ]);
    }

    /**
     * Save a property's details.
     */
    public function update(PropertyRequest $request, Property $property): RedirectResponse
    {
        $property->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name saved.', ['name' => $property->name])]);

        return to_route('admin.properties.edit', $property);
    }

    /**
     * Delete a property with its rooms, holds, requests and photos.
     */
    public function destroy(Property $property): RedirectResponse
    {
        DB::transaction(function () use ($property): void {
            $property->images()->get()->each->delete();
            $property->delete();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name deleted.', ['name' => $property->name])]);

        return to_route('admin.properties.index');
    }

    /**
     * Every amenity an admin can tick.
     *
     * @return list<array{key: string, label: string}>
     */
    protected static function amenityOptions(): array
    {
        return collect(Property::AMENITIES)
            ->map(fn (string $label, string $key): array => ['key' => $key, 'label' => $label])
            ->values()
            ->all();
    }
}
