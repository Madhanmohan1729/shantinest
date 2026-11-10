<?php

namespace Database\Seeders;

use App\Console\Commands\UploadImages;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

/**
 * Create the default property with its rooms and photos. Safe to run again:
 * existing records are left as an admin edited them.
 */
class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $property = Property::query()->firstOrCreate(['slug' => 'casa-bloom'], [
            'name' => 'Casa Bloom',
            'tagline' => 'Bright, cute, cheerful and welcoming',
            'description' => "Welcome to Casa Bloom, a cute, bright and welcoming home designed for a comfortable stay. Enjoy cosy bedrooms, a modern kitchen and a relaxing living space with warm interiors and natural light.\n\nPerfect for couples, small families or friends, this thoughtfully furnished apartment offers a peaceful retreat to unwind after a busy day. Whether you are visiting for a weekend getaway or a longer stay, Casa Bloom is a lovely place to relax and feel at home.",
            'property_type' => 'Entire rental unit',
            'address' => 'Kondapur',
            'city' => 'Hyderabad',
            'state' => 'Telangana',
            'country' => 'India',
            'latitude' => 17.4659300,
            'longitude' => 78.3668700,
            'max_guests' => 4,
            'bedrooms' => 2,
            'beds' => 2,
            'bathrooms' => 2,
            'amenities' => [
                'wifi', 'kitchen', 'air_conditioning', 'tv', 'washing_machine', 'fridge',
                'hot_water', 'self_check_in', 'building_staff', 'essentials', 'security_cameras',
            ],
            'house_rules' => [
                'Up to 4 overnight guests',
                'No smoking inside the apartment',
                'No parties or events',
                'Service animals are welcome',
            ],
            'check_in_time' => '14:00',
            'check_out_time' => '11:00',
            'is_published' => true,
        ]);

        $rooms = [
            ['name' => 'Bedroom 1', 'bed_type' => 'Queen bed', 'description' => 'A calm, sunlit bedroom with a queen bed, full-height wardrobe and en-suite bathroom.', 'features' => ['Air conditioning', 'Wardrobe', 'En-suite bathroom']],
            ['name' => 'Bedroom 2', 'bed_type' => 'Queen bed', 'description' => 'A cosy second bedroom with a queen bed, soft lighting and its own bathroom.', 'features' => ['Air conditioning', 'Wardrobe', 'Private bathroom']],
        ];

        foreach ($rooms as $sortOrder => $room) {
            $property->rooms()->firstOrCreate(['name' => $room['name']], [...$room, 'max_guests' => 2, 'sort_order' => $sortOrder]);
        }

        Artisan::call(UploadImages::class);

        $property->load('rooms');

        $property->images()->whereNull('room_id')->get()->each(function ($image) use ($property): void {
            $room = $property->rooms->first(fn (Room $room): bool => $room->name === $image->caption);

            if ($room !== null) {
                $image->update(['room_id' => $room->id]);
            }
        });
    }
}
