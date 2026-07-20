<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\RoleAliases;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class PrepareDemoAccountsCommand extends Command
{
    protected $signature = 'demo:prepare-accounts';

    protected $description = 'Prepare three local demonstration accounts without exposing their passwords.';

    public function handle(): int
    {
        if ($this->laravel->environment('production')) {
            $this->error('Cette commande de demonstration est interdite en production.');

            return self::FAILURE;
        }

        $roles = $this->resolveRoles();

        if ($roles === null) {
            return self::FAILURE;
        }

        try {
            $accounts = [
                'admin' => [
                    'name' => 'Administrateur Démo',
                    'email' => $this->email('DEMO_ADMIN_EMAIL', 'Adresse e-mail Administrateur'),
                    'password' => $this->password('DEMO_ADMIN_PASSWORD', 'Mot de passe Administrateur'),
                    'role' => $roles['admin'],
                ],
                'supervisor' => [
                    'name' => 'Superviseur Démo',
                    'email' => $this->email('DEMO_SUPERVISOR_EMAIL', 'Adresse e-mail Superviseur'),
                    'password' => $this->password('DEMO_SUPERVISOR_PASSWORD', 'Mot de passe Superviseur'),
                    'role' => $roles['supervisor'],
                ],
                'operator' => [
                    'name' => 'Opérateur Démo',
                    'email' => $this->email('DEMO_OPERATOR_EMAIL', 'Adresse e-mail Opérateur'),
                    'password' => $this->password('DEMO_OPERATOR_PASSWORD', 'Mot de passe Opérateur'),
                    'role' => $roles['operator'],
                ],
            ];

            $this->validateAccounts($accounts);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($accounts): void {
            foreach ($accounts as $account) {
                $user = User::query()->firstOrNew(['email' => $account['email']]);
                $user->forceFill([
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'is_active' => true,
                ])->save();

                $user->syncRoles([$account['role']]);
            }
        });

        $this->info('Les trois comptes de demonstration sont prets. Aucun mot de passe n’a ete affiche.');

        return self::SUCCESS;
    }

    /** @return array{admin: Role, supervisor: Role, operator: Role}|null */
    private function resolveRoles(): ?array
    {
        $roles = [
            'admin' => $this->firstExistingRole(RoleAliases::adminNames()),
            'supervisor' => $this->firstExistingRole(RoleAliases::supervisorNames()),
            'operator' => $this->firstExistingRole(RoleAliases::operatorNames()),
        ];

        foreach ($roles as $key => $role) {
            if ($role === null) {
                $this->error("Le role requis '{$key}' est absent. La commande n'a modifie aucun compte.");

                return null;
            }
        }

        /** @var array{admin: Role, supervisor: Role, operator: Role} $roles */
        return $roles;
    }

    /** @param array<int, string> $names */
    private function firstExistingRole(array $names): ?Role
    {
        foreach ($names as $name) {
            $role = Role::query()
                ->where('guard_name', 'web')
                ->where('name', $name)
                ->first();

            if ($role !== null) {
                return $role;
            }
        }

        return null;
    }

    private function email(string $variable, string $question): string
    {
        $value = trim((string) getenv($variable));

        return $value !== '' ? $value : trim((string) $this->ask($question));
    }

    private function password(string $variable, string $question): string
    {
        $value = (string) getenv($variable);

        if ($value !== '') {
            return $value;
        }

        $password = (string) $this->secret($question);
        $confirmation = (string) $this->secret("Confirmer le {$question}");

        if (! hash_equals($password, $confirmation)) {
            throw ValidationException::withMessages([
                $variable => ['La confirmation du mot de passe ne correspond pas.'],
            ]);
        }

        return $password;
    }

    /** @param array<string, array{name: string, email: string, password: string, role: Role}> $accounts */
    private function validateAccounts(array $accounts): void
    {
        $passwordRule = static fn (): Password => Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols();

        validator([
            'admin_email' => $accounts['admin']['email'],
            'admin_password' => $accounts['admin']['password'],
            'supervisor_email' => $accounts['supervisor']['email'],
            'supervisor_password' => $accounts['supervisor']['password'],
            'operator_email' => $accounts['operator']['email'],
            'operator_password' => $accounts['operator']['password'],
        ], [
            'admin_email' => ['required', 'email:rfc', 'max:255', 'different:supervisor_email', 'different:operator_email'],
            'supervisor_email' => ['required', 'email:rfc', 'max:255', 'different:admin_email', 'different:operator_email'],
            'operator_email' => ['required', 'email:rfc', 'max:255', 'different:admin_email', 'different:supervisor_email'],
            'admin_password' => ['required', $passwordRule()],
            'supervisor_password' => ['required', $passwordRule()],
            'operator_password' => ['required', $passwordRule()],
        ])->validate();
    }
}
