<?php

namespace App\Models;

use App\Enums\StayRequestStatus;
use Database\Factories\StayRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $property_id
 * @property string $reference
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property int $guests
 * @property Carbon $check_in
 * @property Carbon $check_out
 * @property string|null $message
 * @property StayRequestStatus $status
 * @property string|null $admin_note
 * @property int|null $responded_by
 * @property Carbon|null $responded_at
 * @property-read Property $property
 * @property-read User|null $responder
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'property_id', 'name', 'email', 'phone', 'guests', 'check_in', 'check_out', 'message',
    'status', 'admin_note', 'responded_by', 'responded_at',
])]
class StayRequest extends Model
{
    /** @use HasFactory<StayRequestFactory> */
    use HasFactory;

    /**
     * Give every new request a short reference guests can quote.
     */
    protected static function booted(): void
    {
        static::creating(function (StayRequest $stayRequest): void {
            $stayRequest->reference ??= self::newReference();
        });
    }

    /**
     * A reference such as "SN-7K2QHD" that no other request uses.
     */
    public static function newReference(): string
    {
        do {
            $reference = 'SN-'.Str::upper(Str::random(6));
        } while (self::query()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * The property the guest wants to stay at.
     *
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The rooms the guest asked for.
     *
     * @return BelongsToMany<Room, $this>
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }

    /**
     * The holds placed when the request was approved.
     *
     * @return HasMany<RoomHold, $this>
     */
    public function holds(): HasMany
    {
        return $this->hasMany(RoomHold::class);
    }

    /**
     * The admin who approved or declined the request.
     *
     * @return BelongsTo<User, $this>
     */
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    /**
     * How many nights the guest wants to stay.
     */
    public function nights(): int
    {
        return (int) $this->check_in->diffInDays($this->check_out);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StayRequestStatus::class,
            'check_in' => 'date',
            'check_out' => 'date',
            'responded_at' => 'datetime',
            'guests' => 'integer',
        ];
    }
}
