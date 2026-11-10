<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $property_type
 * @property string|null $address
 * @property string $city
 * @property string|null $state
 * @property string $country
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int $max_guests
 * @property int $bedrooms
 * @property int $beds
 * @property int $bathrooms
 * @property list<string>|null $amenities
 * @property list<string>|null $house_rules
 * @property string $check_in_time
 * @property string $check_out_time
 * @property string|null $contact_phone
 * @property string|null $contact_email
 * @property bool $is_published
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name', 'slug', 'tagline', 'description', 'property_type', 'address', 'city', 'state', 'country',
    'latitude', 'longitude', 'max_guests', 'bedrooms', 'beds', 'bathrooms', 'amenities', 'house_rules',
    'check_in_time', 'check_out_time', 'contact_phone', 'contact_email', 'is_published', 'sort_order',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory;

    /**
     * Every amenity an admin can tick, in display order. The storefront picks
     * an icon for each by its key.
     *
     * @var array<string, string>
     */
    public const array AMENITIES = [
        'wifi' => 'Wifi',
        'kitchen' => 'Kitchen',
        'air_conditioning' => 'Air conditioning',
        'tv' => 'TV',
        'washing_machine' => 'Washing machine',
        'fridge' => 'Fridge',
        'hot_water' => 'Hot water',
        'self_check_in' => 'Self check-in',
        'building_staff' => 'Building staff',
        'essentials' => 'Essentials',
        'workspace' => 'Dedicated workspace',
        'parking' => 'Free parking',
        'lift' => 'Lift',
        'power_backup' => 'Power backup',
        'security_cameras' => 'Exterior security cameras',
        'smoke_alarm' => 'Smoke alarm',
    ];

    /**
     * Use the slug in URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The rooms guests can request.
     *
     * @return HasMany<Room, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Every photo of the property, in gallery order.
     *
     * @return HasMany<PropertyImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The stay requests guests have sent for this property.
     *
     * @return HasMany<StayRequest, $this>
     */
    public function stayRequests(): HasMany
    {
        return $this->hasMany(StayRequest::class);
    }

    /**
     * Only properties visitors can see.
     *
     * @param  Builder<Property>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * The city, state and country on one line.
     */
    public function location(): string
    {
        return collect([$this->city, $this->state, $this->country])->filter()->implode(', ');
    }

    /**
     * The moment a guest arriving on the given date checks in.
     */
    public function checkInOn(CarbonInterface|string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date)->setTimeFromTimeString($this->check_in_time);
    }

    /**
     * The moment a guest leaving on the given date checks out.
     */
    public function checkOutOn(CarbonInterface|string $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date)->setTimeFromTimeString($this->check_out_time);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'house_rules' => 'array',
            'is_published' => 'boolean',
            'max_guests' => 'integer',
            'bedrooms' => 'integer',
            'beds' => 'integer',
            'bathrooms' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
