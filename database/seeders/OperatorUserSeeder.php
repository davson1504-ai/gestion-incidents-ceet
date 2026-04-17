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
            ['name' => 'Superviseur Lomé',     'email' => 'superviseur.lome@ceet.tg',     'role' => 'Superviseur', 'tel' => '90000001'],
            ['name' => 'Superviseur Maritime',  'email' => 'superviseur.maritime@ceet.tg',  'role' => 'Superviseur', 'tel' => '90000002'],
            ['name' => 'Operateur A',           'email' => 'operateur.a@ceet.tg',           'role' => 'Opérateur',   'tel' => '90000003'],
            ['name' => 'Operateur B',           'email' => 'operateur.b@ceet.tg',           'role' => 'Opérateur',   'tel' => '90000004'],
            ['name' => 'Operateur C',           'email' => 'operateur.c@ceet.tg',           'role' => 'Opérateur',   'tel' => '90000005'],
            ['name' => 'Operateur D',           'email' => 'operateur.d@ceet.tg',           'role' => 'Opérateur',   'tel' => '90000006'],
        ];

        foreach ($users as $index => $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    // ✅ CORRECTION #5: Numéros de téléphone uniques et valides (8 chiffres Togo)
                    'telephone' => $row['tel'],
                    'departement_id' => ! empty($departementIds)
                        ? $departementIds[$index % count($departementIds)]
                        : null,
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$row['role']]);
        }

        $this->command?->info('✅ Utilisateurs métier (superviseurs/opérateurs) importés.');
    }
}
