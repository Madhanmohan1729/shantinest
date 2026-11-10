<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\RoomHoldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $room_id
 * @property int|null $stay_request_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property string|null $guest_name
 * @property string|null $note
 * @property int|null $created_by
 * @property-read Room $room
 * @property-read StayRequest|null $stayRequest
 * @property-read User|null $creator
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['room_id', 'stay_request_id', 'starts_at', 'ends_at', 'guest_name', 'note', 'created_by'])]
class RoomHold extends Model
{
    /** @use HasFactory<RoomHoldFactory> */
    use HasFactory;

    /**
     * The held room.
     *
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * The stay request this hold was placed for, if any.
     *
     * @return BelongsTo<StayRequest, $this>
     */
    public function stayRequest(): BelongsTo
    {
        return $this->belongsTo(StayRequest::class);
    }

    /**
     * The admin who placed the hold.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Holds that share any moment with the given time. Back-to-back stays
     * (one guest leaves at 11:00, the next arrives at 11:00) do not overlap.
     *
     * @param  Builder<RoomHold>  $query
     */
    #[Scope]
    protected function overlapping(Builder $query, CarbonInterface $startsAt, CarbonInterface $endsAt): void
    {
        $query->where('starts_at', '<', $endsAt)->where('ends_at', '>', $startsAt);
    }

    /**
     * Holds that have not ended yet.
     *
     * @param  Builder<RoomHold>  $query
     */
    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->where('ends_at', '>', now());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }
}
