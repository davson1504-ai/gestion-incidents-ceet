<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class CreateFirstAdminCommand extends Command
{
    protected $signature = 'app:create-first-admin';

    protected $description = 'Interactively create the first production administrator account.';

    public function handle(): int
    {
        $this->callSilent('db:seed', [
            '--class' => RolesAndPermissionsSeeder::class,
            '--force' => true,
        ]);

        $adminRole = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Administrateur', 'admin'])
            ->first();

        if (! $adminRole) {
            $this->error('Admin role is missing after permission sync.');

            return self::FAILURE;
        }

        if (User::role($adminRole->name)->exists()) {
            $this->error('An administrator already exists. This command only creates the first administrator.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Full name'));
        $email = trim((string) $this->ask('Email address'));
        $telephone = trim((string) $this->ask('Telephone'));
        $password = (string) $this->secret('Password');
        $confirmation = (string) $this->secret('Confirm password');

        try {
            validator([
                'name' => $name,
                'email' => $email,
                'telephone' => $telephone,
                'password' => $password,
                'password_confirmation' => $confirmation,
            ], [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'telephone' => ['nullable', 'string', 'max:30'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ])->validate();
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'telephone' => $telephone ?: null,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $user->assignRole($adminRole);

        $this->info('First administrator created. No password was stored or displayed in plain text.');

        return self::SUCCESS;
    }
}
