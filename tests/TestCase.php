<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Vite;

abstract class TestCase extends BaseTestCase
{
    /**
     * Indicates whether the default seeding should be performed before each test.
     */
    protected bool $seed = false;

    /**
     * Setup the test environment.
     * 
     * Called before each test method.
     * Disables CSRF token verification and Vite for testing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Disable Vite manifest checking in tests
        // Prevents: "Vite manifest not found at: public/build/manifest.json"
        try {
            Vite::useFakeManifest();
        } catch (\Exception $e) {
            // If useFakeManifest() not available, try withoutVite()
            if (method_exists($this, 'withoutVite')) {
                $this->withoutVite();
            }
        }

        // Disable CSRF token verification for all stateful requests
        // This is safe because we're not testing CSRF protection itself
        // Production has CSRF always enabled
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * Create application instance.
     * 
     * Ensures the app is properly bootstrapped for testing with SQLite in-memory DB.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        // Database configured in phpunit.xml to use SQLite :memory:
        // Cache configured to use 'array' driver

        return $app;
    }
}
