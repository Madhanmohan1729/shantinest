<?php

use App\Models\Media;
use App\Models\Property;

test('default photos are served straight from the public folder', function () {
    Property::factory()->create(['slug' => 'casa-bloom']);

    $this->artisan('images:upload')->assertSuccessful();

    $media = Media::query()->where('path', 'images/properties/casa-bloom/01-living-room.jpg')->firstOrFail();

    expect($media->disk)->toBe('site')
        ->and($media->url)->toBe('/images/properties/casa-bloom/01-living-room.jpg')
        ->and(public_path($media->path))->toBeFile();
});
