<?php

namespace Tests;

use App\RedisStore;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();

        app(RedisStore::class)->clear();
    }
}
