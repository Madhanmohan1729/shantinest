<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $property_id
 * @property string $name
 * @property string|null $description
 * @property string|null $bed_type
 * @property int $max_guests
 * @property list<string>|null $features
 * @property bool $is_active
 * @property int $sort_order
 * @property-read Property $property
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['property_id', 'name', 'description', 'bed_type', 'max_guests', 'features', 'is_active', 'sort_order'])]
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    /**
     * The property the room is in.
     *
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Photos of this room, in gallery order.
     *
     * @return HasMany<PropertyImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The times the room is held and cannot be requested.
     *
     * @return HasMany<RoomHold, $this>
     */
    public function holds(): HasMany
    {
        return $this->hasMany(RoomHold::class);
    }

    /**
     * The stay requests that asked for this room.
     *
     * @return BelongsToMany<StayRequest, $this>
     */
    public function stayRequests(): BelongsToMany
    {
        return $this->belongsToMany(StayRequest::class);
    }

    /**
     * Whether no hold overlaps the given time, optionally ignoring the hold
     * being edited.
     */
    public function isFreeBetween(CarbonInterface $startsAt, CarbonInterface $endsAt, ?int $ignoreHoldId = null): bool
    {
        return ! $this->holds()
            ->overlapping($startsAt, $endsAt)
            ->when($ignoreHoldId !== null, fn ($query) => $query->whereKeyNot($ignoreHoldId))
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'max_guests' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
