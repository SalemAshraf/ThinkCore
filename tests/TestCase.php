<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // PHP feature tests exercise routes/views, not compiled assets.
        // CI runs the production asset build separately before browser checks.
        $this->withoutVite();
    }
}
