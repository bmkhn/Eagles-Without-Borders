<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use array session driver to avoid requiring a sessions table
        // in the in-memory SQLite database used during tests.
        config()->set('session.driver', 'array');

        // Disable CSRF verification for all tests.
        // withoutMiddleware(ClassName) replaces the class binding in the
        // container so the kernel resolves the no-op instead.
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }
}
