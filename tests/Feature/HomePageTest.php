<?php

namespace Tests\Feature;

use App\FakeTorrent;
use App\Contracts\TorrentContract;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomePageTest extends TestCase
{
    protected function getTransmissionClient()
    {
        app()->bind(TorrentContract::class, function ($app) {
            return app(FakeTorrent::class);
        });
        return app(TorrentContract::class);
    }

    /** @test */
    public function we_can_get_the_homepage()
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/');

        $response->assertSuccessful();
    }
}
