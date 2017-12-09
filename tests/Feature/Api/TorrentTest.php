<?php

namespace Tests\Feature\Api;

use App\Torrent;
use Tests\TestCase;
use App\FakeTorrent;
use App\TorrentEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TorrentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_a_list_of_all_torrents()
    {
        $this->withoutExceptionHandling();
        $torrent1 = factory(TorrentEntry::class)->create(['torrent_id' => 10]);
        $torrent2 = factory(TorrentEntry::class)->create(['torrent_id' => 1]);
        $torrent3 = factory(TorrentEntry::class)->create(['torrent_id' => 5]);

        $response = $this->getJson(route('api.torrent.index'));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                [
                    'id' => $torrent1->id,
                    'torrent_id' => $torrent1->torrent_id,
                    'name' => $torrent1->webFriendlyName(),
                    'eta' => $torrent1->formattedEta(),
                    'size' => $torrent1->formattedSize(),
                    'copied' => $torrent1->wasAlreadyCopied(),
                    'incomplete' => $torrent1->isStillDownloading(),
                ],
                [
                    'id' => $torrent3->id,
                    'torrent_id' => $torrent3->torrent_id,
                    'name' => $torrent3->webFriendlyName(),
                    'eta' => $torrent3->formattedEta(),
                    'size' => $torrent3->formattedSize(),
                    'copied' => $torrent3->wasAlreadyCopied(),
                    'incomplete' => $torrent3->isStillDownloading(),
                ],
                [
                    'id' => $torrent2->id,
                    'torrent_id' => $torrent2->torrent_id,
                    'name' => $torrent2->webFriendlyName(),
                    'eta' => $torrent2->formattedEta(),
                    'size' => $torrent2->formattedSize(),
                    'copied' => $torrent2->wasAlreadyCopied(),
                    'incomplete' => $torrent2->isStillDownloading(),
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_a_fresh_copy_of_a_torrent()
    {
        $this->withoutExceptionHandling();
        app()->singleton(Torrent::class, function ($app) {
            return app(FakeTorrent::class);
        });
        $torrent1 = factory(TorrentEntry::class)->create();
        $torrent2 = factory(TorrentEntry::class)->create();

        $response = $this->getJson(route('api.torrent.show', $torrent2->torrent_id));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'id' => 2,
                'name' => $torrent2->name,
            ],
        ]);
    }
}
