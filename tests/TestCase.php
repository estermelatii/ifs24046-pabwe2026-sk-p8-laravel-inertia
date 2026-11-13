<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Halaman Inertia merender app.blade.php; manifest Vite tidak dibutuhkan saat pengujian.
        $this->withoutVite();
    }
}
