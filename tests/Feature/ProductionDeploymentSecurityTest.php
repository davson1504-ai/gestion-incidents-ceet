<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Log;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\IncidentSeeder;
use Database\Seeders\LogSeeder;
use Database\Seeders\OperatorUserSeeder;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class ProductionDeploymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_reverb_allowed_origins_never_default_to_wildcard(): void
    {
        $origins = config('reverb.apps.apps.0.allowed_origins');

        $this->assertIsArray($origins);
        $this->assertNotContains('*', $origins);
    }

    public function test_production_seeder_excludes_demo_users_incidents_and_logs(): void
    {
        $this->seed(ProductionSeeder::class);

        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Incident::query()->count());
        $this->assertSame(0, Log::query()->count());
        $this->assertDatabaseHas('roles', ['name' => 'Administrateur']);
        $this->assertDatabaseHas('permissions', ['name' => 'incidents.view']);
    }

    public function test_demo_user_seeders_are_disabled_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);
        app(AdminUserSeeder::class)->run();
    }

    public function test_operator_demo_seeder_is_disabled_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);
        app(OperatorUserSeeder::class)->run();
    }

    public function test_demo_incident_and_log_seeders_are_disabled_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        foreach ([IncidentSeeder::class, LogSeeder::class] as $seeder) {
            try {
                app($seeder)->run();
                $this->fail($seeder.' should be disabled in production.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('disabled in production', $exception->getMessage());
            }
        }
    }

    public function test_create_first_admin_command_requires_interactive_password_without_default(): void
    {
        $this->artisan('app:create-first-admin')
            ->expectsQuestion('Full name', 'Production Admin')
            ->expectsQuestion('Email address', 'admin.production@example.test')
            ->expectsQuestion('Telephone', '90000000')
            ->expectsQuestion('Password', 'SecurePass123!')
            ->expectsQuestion('Confirm password', 'SecurePass123!')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'admin.production@example.test')->firstOrFail();

        $this->assertTrue(Hash::check('SecurePass123!', $admin->password));
        $this->assertFalse(Hash::check('password', $admin->password));
        $this->assertTrue($admin->hasRole('Administrateur'));
    }
}
