<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@ceet.tg'],
            [
                'name' => 'Simon EDOH',
                'password' => Hash::make('password'),
                'telephone' => '90123456',
                'is_active' => true,
            ]
        );

        $admin->assignRole('Administrateur');

        $this->command->info('✅ Utilisateur Admin créé avec succès !');
        $this->command->info('Email : admin@ceet.tg');
        $this->command->info('Mot de passe : password');
    }
}
