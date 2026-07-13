<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new \RuntimeException('AdminUserSeeder is disabled in production. Use php artisan app:create-first-admin.');
        }

        $password = env('DEMO_ADMIN_PASSWORD') ?: Str::random(32);

        $admin = User::firstOrCreate(
            ['email' => 'admin@ceet.tg'],
            [
                'name' => 'Simon EDOH',
                'password' => Hash::make($password),
                'telephone' => '90123456',
                'is_active' => true,
            ]
        );

        $admin->assignRole('Administrateur');

        $this->command->info('✅ Utilisateur Admin créé avec succès !');
        $this->command->info('Email : admin@ceet.tg');
        $this->command->warn('Mot de passe de demonstration genere ou fourni par DEMO_ADMIN_PASSWORD.');
    }
}
