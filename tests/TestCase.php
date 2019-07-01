<?php

namespace Tests;

use App\RedisStore;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp() : void
    {
        parent::setUp();

        app(RedisStore::class)->clear();

        $this->getTransmissionClient();

        Mail::fake();
        Storage::fake('torrents');
        Storage::fake('destination');
    }
}
