<?php

namespace App\Console\Commands;

use App\Actions\Permissions\SyncPermissions;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('permissions:sync {--prune : Delete permissions that are no longer listed in config/permissions.php}')]
#[Description('Create the permissions listed in config/permissions.php and grant them all to the super admin role')]
class SyncPermissionsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncPermissions $syncPermissions): int
    {
        $result = $syncPermissions->handle(prune: (bool) $this->option('prune'));

        if ($result['created'] === []) {
            $this->components->info('No new permissions to create.');
        } else {
            $this->components->info('Created '.count($result['created']).' permission(s):');
            $this->components->bulletList($result['created']);
        }

        if ($result['deleted'] !== []) {
            $this->components->warn('Deleted '.count($result['deleted']).' permission(s) no longer in the config:');
            $this->components->bulletList($result['deleted']);
        }

        $this->components->info("The [{$result['role']->name}] role now has all ".count(SyncPermissions::names()).' permissions.');

        return self::SUCCESS;
    }
}
