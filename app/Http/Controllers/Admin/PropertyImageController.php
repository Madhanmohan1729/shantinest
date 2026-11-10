<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderPropertyImagesRequest;
use App\Http\Requests\Admin\StorePropertyImagesRequest;
use App\Http\Requests\Admin\UpdatePropertyImageRequest;
use App\Models\Media;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PropertyImageController extends Controller
{
    /**
     * Upload photos to the end of a property's gallery.
     */
    public function store(StorePropertyImagesRequest $request, Property $property): RedirectResponse
    {
        $nextSortOrder = (int) $property->images()->max('sort_order') + 1;
        $files = $request->file('images', []);

        foreach ($files as $offset => $file) {
            $media = Media::storeUpload($file, 'properties/'.$property->slug, alt: $property->name, errorKey: 'images');

            $property->images()->create([
                'media_id' => $media->id,
                'sort_order' => $nextSortOrder + $offset,
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => trans_choice('{1} Photo added.|[2,*] :count photos added.', count($files))]);

        return back();
    }

    /**
     * Change a photo's caption or the room it shows.
     */
    public function update(UpdatePropertyImageRequest $request, PropertyImage $image): RedirectResponse
    {
        $image->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photo updated.')]);

        return back();
    }

    /**
     * Put a property's photos in the given order. The first photo is the cover.
     */
    public function reorder(ReorderPropertyImagesRequest $request, Property $property): RedirectResponse
    {
        DB::transaction(function () use ($request, $property): void {
            foreach ($request->validated('image_ids') as $sortOrder => $imageId) {
                $property->images()->whereKey($imageId)->update(['sort_order' => $sortOrder]);
            }
        });

        return back();
    }

    /**
     * Remove a photo and its file.
     */
    public function destroy(PropertyImage $image): RedirectResponse
    {
        $image->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Photo removed.')]);

        return back();
    }
}
