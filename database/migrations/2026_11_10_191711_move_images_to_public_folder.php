<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

return new class extends Migration
{
    /**
     * Move photos from storage/app/public (served through the /storage
     * symlink) into the public folder, so they load on hosts where the
     * symlink does not work. Rows whose file cannot be found are left as is.
     */
    public function up(): void
    {
        DB::table('media')->where('disk', 'public')->orderBy('id')->each(function (object $media): void {
            $source = storage_path('app/public/'.$media->path);
            $destination = public_path($media->path);

            if (! File::exists($destination)) {
                if (! File::exists($source)) {
                    return;
                }

                File::ensureDirectoryExists(dirname($destination));
                File::copy($source, $destination);
            }

            DB::table('media')->where('id', $media->id)->update(['disk' => 'site']);
        });
    }

    /**
     * Point the rows back at storage/app/public. The copied files are kept.
     */
    public function down(): void
    {
        DB::table('media')->where('disk', 'site')->update(['disk' => 'public']);
    }
};
