<?php

namespace App\Actions\Permissions;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Bring the database in line with config/permissions.php: create every
 * listed permission and grant all of them to the super admin role.
 */
class SyncPermissions
{
    public const string GUARD = 'web';

    public function __construct(private PermissionRegistrar $permissionRegistrar) {}

    /**
     * Every permission name listed in the config, in display order.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        /** @var array<string, list<string>> $groups */
        $groups = config('permissions.groups', []);

        return array_values(array_unique(array_merge(...array_values($groups))));
    }

    /**
     * The name of the role that always holds every permission.
     */
    public static function superAdminRole(): string
    {
        return (string) config('permissions.super_admin_role', 'super-admin');
    }

    /**
     * Create the missing permissions, optionally delete ones no longer listed,
     * and grant everything to the super admin role.
     *
     * @return array{created: list<string>, deleted: list<string>, role: Role}
     */
    public function handle(bool $prune = false): array
    {
        $this->permissionRegistrar->forgetCachedPermissions();

        $names = self::names();
        $existing = Permission::query()->where('guard_name', self::GUARD)->pluck('name')->all();

        $created = array_values(array_diff($names, $existing));

        foreach ($created as $name) {
            Permission::create(['name' => $name, 'guard_name' => self::GUARD]);
        }

        $deleted = [];

        if ($prune) {
            $deleted = array_values(array_diff($existing, $names));

            Permission::query()->where('guard_name', self::GUARD)->whereIn('name', $deleted)->delete();
        }

        /** @var Role $role */
        $role = Role::findOrCreate(self::superAdminRole(), self::GUARD);

        $role->syncPermissions(Permission::query()->where('guard_name', self::GUARD)->get());

        $this->permissionRegistrar->forgetCachedPermissions();

        return ['created' => $created, 'deleted' => $deleted, 'role' => $role];
    }
}
