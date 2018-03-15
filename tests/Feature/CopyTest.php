<?php

namespace Tests\Feature;

use App\FakeTorrent;
use App\Contracts\TorrentContract;
use Tests\TestCase;
use Tests\Feature\CommonCopyTests;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CopyTest extends TestCase
{
    use RefreshDatabase;
    use CommonCopyTests;

    protected function getTransmissionClient()
    {
        app()->bind(TorrentContract::class, function ($app) {
            return app(FakeTorrent::class);
        });
        return app(TorrentContract::class);
    }
}
