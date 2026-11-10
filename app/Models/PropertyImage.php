<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $property_id
 * @property int|null $room_id
 * @property int $media_id
 * @property string|null $caption
 * @property int $sort_order
 * @property-read Property $property
 * @property-read Room|null $room
 * @property-read Media $media
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['property_id', 'room_id', 'media_id', 'caption', 'sort_order'])]
class PropertyImage extends Model
{
    /**
     * Remove the stored file once no photo uses it any more.
     */
    protected static function booted(): void
    {
        static::deleted(function (PropertyImage $image): void {
            $media = $image->media;

            if ($media !== null && ! $media->propertyImages()->exists()) {
                $media->deleteWithFile();
            }
        });
    }

    /**
     * The property this photo belongs to.
     *
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The room this photo shows, if any.
     *
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * The stored file.
     *
     * @return BelongsTo<Media, $this>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * The photo as the storefront and admin screens show it.
     *
     * @return array{id: int, url: string, caption: string|null, alt: string, roomId: int|null}
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->media->url,
            'caption' => $this->caption,
            'alt' => $this->media->alt ?? $this->caption ?? 'Property photo',
            'roomId' => $this->room_id,
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
