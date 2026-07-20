<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrepareDemoAccountsCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> */
    private array $demoVariables = [
        'DEMO_ADMIN_EMAIL',
        'DEMO_ADMIN_PASSWORD',
        'DEMO_SUPERVISOR_EMAIL',
        'DEMO_SUPERVISOR_PASSWORD',
        'DEMO_OPERATOR_EMAIL',
        'DEMO_OPERATOR_PASSWORD',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->setDemoEnvironment();
    }

    protected function tearDown(): void
    {
        foreach ($this->demoVariables as $variable) {
            putenv($variable);
            unset($_ENV[$variable], $_SERVER[$variable]);
        }

        parent::tearDown();
    }

    public function test_it_creates_accounts_with_correct_roles_and_passwords(): void
    {
        $this->artisan('demo:prepare-accounts')->assertSuccessful();

        $admin = User::query()->where('email', 'demo.admin@example.test')->firstOrFail();
        $supervisor = User::query()->where('email', 'demo.supervisor@example.test')->firstOrFail();
        $operator = User::query()->where('email', 'demo.operator@example.test')->firstOrFail();

        $this->assertTrue($admin->hasRole('Administrateur'));
        $this->assertTrue($supervisor->hasRole('Superviseur'));
        $this->assertTrue($operator->isOperateur());
        $this->assertTrue(Hash::check('AdminDemo#2026', $admin->password));
        $this->assertTrue(Hash::check('Supervisor#2026', $supervisor->password));
        $this->assertTrue(Hash::check('OperatorDemo#2026', $operator->password));
        $this->assertTrue($admin->is_active && $supervisor->is_active && $operator->is_active);
    }

    public function test_it_rejects_a_weak_password_without_creating_accounts(): void
    {
        $this->putEnvironment('DEMO_OPERATOR_PASSWORD', 'password');

        $this->artisan('demo:prepare-accounts')->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_it_never_displays_a_password(): void
    {
        $exitCode = Artisan::call('demo:prepare-accounts');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringNotContainsString('AdminDemo#2026', $output);
        $this->assertStringNotContainsString('Supervisor#2026', $output);
        $this->assertStringNotContainsString('OperatorDemo#2026', $output);
    }

    public function test_it_is_idempotent(): void
    {
        $this->artisan('demo:prepare-accounts')->assertSuccessful();

        $ids = User::query()->orderBy('email')->pluck('id', 'email')->all();

        $this->artisan('demo:prepare-accounts')->assertSuccessful();

        $this->assertDatabaseCount('users', 3);
        $this->assertSame($ids, User::query()->orderBy('email')->pluck('id', 'email')->all());
    }

    public function test_it_is_forbidden_in_production(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->artisan('demo:prepare-accounts')->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }

    private function setDemoEnvironment(): void
    {
        $this->putEnvironment('DEMO_ADMIN_EMAIL', 'demo.admin@example.test');
        $this->putEnvironment('DEMO_ADMIN_PASSWORD', 'AdminDemo#2026');
        $this->putEnvironment('DEMO_SUPERVISOR_EMAIL', 'demo.supervisor@example.test');
        $this->putEnvironment('DEMO_SUPERVISOR_PASSWORD', 'Supervisor#2026');
        $this->putEnvironment('DEMO_OPERATOR_EMAIL', 'demo.operator@example.test');
        $this->putEnvironment('DEMO_OPERATOR_PASSWORD', 'OperatorDemo#2026');
    }

    private function putEnvironment(string $key, string $value): void
    {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
