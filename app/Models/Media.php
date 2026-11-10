<?php

namespace App\Models;

use App\Console\Commands\UploadImages;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * @property int $id
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string|null $mime_type
 * @property int $size
 * @property string|null $alt
 * @property-read string $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['disk', 'path', 'original_name', 'mime_type', 'size', 'alt'])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * The property photos that show this file.
     *
     * @return HasMany<PropertyImage, $this>
     */
    public function propertyImages(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    /**
     * Store an uploaded image under images/{folder} and record it.
     *
     * @throws ValidationException when the disk refuses the file
     */
    public static function storeUpload(UploadedFile $file, string $folder, ?string $disk = null, ?string $alt = null, string $errorKey = 'image'): self
    {
        $disk ??= (string) config('filesystems.images_disk');
        $path = $file->store(UploadImages::PREFIX.'/'.trim($folder, '/'), $disk);

        if ($path === false) {
            throw ValidationException::withMessages([$errorKey => __('The image could not be uploaded. Please try again.')]);
        }

        return self::query()->create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $alt,
        ]);
    }

    /**
     * Delete the stored file and its record.
     */
    public function deleteWithFile(): void
    {
        Storage::disk($this->disk)->delete($this->path);

        $this->delete();
    }

    /**
     * A URL the browser can load the file from.
     *
     * S3 objects stay private, so they get a temporary signed URL. It is cached
     * for half its lifetime so repeat visits reuse the same URL and the browser
     * cache keeps working. Local disks get a plain URL.
     *
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::get(function (): string {
            $disk = Storage::disk($this->disk);

            if (config("filesystems.disks.{$this->disk}.driver") !== 's3') {
                return $disk->url($this->path);
            }

            $lifetimeMinutes = (int) config('filesystems.images_url_ttl_minutes');

            return Cache::remember(
                "media.{$this->id}.signed_url.".md5($this->disk.'|'.$this->path),
                now()->addMinutes(intdiv($lifetimeMinutes, 2)),
                fn (): string => $disk->temporaryUrl($this->path, now()->addMinutes($lifetimeMinutes)),
            );
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }
}
