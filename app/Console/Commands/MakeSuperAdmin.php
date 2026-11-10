<?php

namespace App\Console\Commands;

use App\Actions\Permissions\SyncPermissions;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('make:super-admin {--name= : The name of the super admin} {--email= : The email address of the super admin} {--password= : The password of the super admin}')]
#[Description('Create a super admin user with every permission')]
class MakeSuperAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SyncPermissions $syncPermissions): int
    {
        $email = $this->option('email');

        if (! is_string($email)) {
            $email = text(label: 'Email', required: true, validate: ['email' => 'email']);
        }

        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email', 'max:255']]);

        if ($validator->fails()) {
            $this->components->error($validator->errors()->first());

            return self::FAILURE;
        }

        $role = $syncPermissions->handle()['role'];

        $user = User::query()->where('email', $email)->first();

        if ($user !== null) {
            $user->assignRole($role);

            $this->components->info("Existing user [{$email}] has been promoted to super admin.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?? text(label: 'Name', required: true);
        $password = $this->option('password') ?? password(label: 'Password', required: true, validate: ['password' => 'min:8']);

        $user = User::query()->forceCreate([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        $this->components->info("Super admin [{$email}] created successfully.");

        return self::SUCCESS;
    }
}
