<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OperatorUserSeeder extends Seeder
{
    public function run(): void
    {
        $departementIds = Departement::pluck('id')->all();

        $users = [
            ['name' => 'Superviseur Lomé', 'email' => 'superviseur.lome@ceet.tg', 'role' => 'Superviseur'],
            ['name' => 'Superviseur Maritime', 'email' => 'superviseur.maritime@ceet.tg', 'role' => 'Superviseur'],
            ['name' => 'Operateur A', 'email' => 'operateur.a@ceet.tg', 'role' => 'Opérateur'],
            ['name' => 'Operateur B', 'email' => 'operateur.b@ceet.tg', 'role' => 'Opérateur'],
            ['name' => 'Operateur C', 'email' => 'operateur.c@ceet.tg', 'role' => 'Opérateur'],
            ['name' => 'Operateur D', 'email' => 'operateur.d@ceet.tg', 'role' => 'Opérateur'],
        ];

        foreach ($users as $index => $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name'           => $row['name'],
                    'password'       => Hash::make('password'),
                    'telephone'      => '90'.str_pad((string) ($index + 100000), 6, '0', STR_PAD_LEFT),
                    'departement_id' => $departementIds[$index % max(count($departementIds), 1)] ?? null,
                    'is_active'      => true,
                ]
            );

            $user->syncRoles([$row['role']]);
        }

        $this->command?->info('✅ Utilisateurs metier (superviseurs/operateurs) importes.');
    }
}

