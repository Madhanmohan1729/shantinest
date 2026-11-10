<?php

namespace Database\Seeders;

use App\Actions\Permissions\SyncPermissions;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(SyncPermissions $syncPermissions): void
    {
        $syncPermissions->handle();

        $this->call(PropertySeeder::class);
    }
}
