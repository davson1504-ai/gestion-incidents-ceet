<?php

/**
 * Laravel Test Bootstrap
 * 
 * This file ensures .env.testing is loaded BEFORE phpunit.xml env vars are processed.
 * This guarantees SQLite:memory: + array cache for all tests without MySQL dependency.
 */

require __DIR__ . '/../vendor/autoload.php';

// Load .env.testing FIRST (before Illuminate bootstraps)
if (file_exists(__DIR__ . '/../.env.testing')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env.testing');
    $dotenv->load();
}

// Set testing environment
putenv('APP_ENV=testing');
