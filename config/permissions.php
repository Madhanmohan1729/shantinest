<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    |
    | The role that is always granted every permission below. It also passes
    | every authorization check through the Gate::before hook.
    |
    */

    'super_admin_role' => 'super-admin',

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Every permission the application checks, grouped by area. After adding
    | one here, run `php artisan permissions:sync` to create it and grant it
    | to the super admin role.
    |
    */

    'groups' => [
        'Dashboard' => ['view dashboard'],
        'Properties' => ['view properties', 'create properties', 'update properties', 'delete properties'],
        'Images' => ['manage property images'],
        'Rooms' => ['manage rooms'],
        'Holds' => ['view holds', 'manage holds'],
        'Stay requests' => ['view stay requests', 'manage stay requests'],
    ],

];
