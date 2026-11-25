<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Databases the test suite is allowed to touch. RefreshDatabase wipes the
     * connected database with `migrate:fresh`, so we hard-stop if the suite is
     * ever pointed anywhere else (e.g. when a cached config silently overrides
     * phpunit.xml and aims it at the real dev/prod database).
     */
    private const ALLOWED_TEST_DATABASES = [
        'izanbakery_ecommerce_test',
        ':memory:',
    ];

    /**
     * Booted once per test, before any trait (including RefreshDatabase) gets
     * a chance to migrate/wipe the database. We read the *resolved* config so a
     * cached config pointing at the real database is caught here.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $default = config('database.default');
        $database = config("database.connections.{$default}.database");

        if (! in_array($database, self::ALLOWED_TEST_DATABASES, true)) {
            throw new RuntimeException(
                "Refusing to run tests against database [{$database}]. "
                .'The test suite must use a dedicated test database (e.g. izanbakery_ecommerce_test). '
                .'If you hit this, clear cached config first: `php artisan config:clear`.'
            );
        }
    }
}
