<?php

namespace Tests\Feature;

use App\Torrent;
use Tests\TestCase;
use App\FakeTorrent;
use App\TorrentEntry;
use App\Jobs\CopyFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TorrentJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_dispatch_copy_jobs_for_each_submitted_torrent()
    {
        $this->withoutExceptionHandling();
        Queue::fake();

        $torrent1 = factory(TorrentEntry::class)->create();
        $torrent2 = factory(TorrentEntry::class)->create();
        $torrent3 = factory(TorrentEntry::class)->create();

        $response = $this->post(route('api.torrent.copy'), [
            'copies' => [
                $torrent1->id,
                $torrent3->id,
            ]
        ]);

        $response->assertSuccessful();
        Queue::assertPushed(CopyFile::class, 2); // exactly 2 jobs were queued
        Queue::assertPushed(CopyFile::class, function ($job) use ($torrent1) {
            return $job->file->id == $torrent1->id;
        });
        Queue::assertPushed(CopyFile::class, function ($job) use ($torrent3) {
            return $job->file->id == $torrent3->id;
        });
    }

    /** @test */
    public function can_get_details_of_a_single_torrent()
    {
        app()->singleton(Torrent::class, function ($app) {
            return app(FakeTorrent::class);
        });
        $torrent = factory(TorrentEntry::class)->create();

        $response = $this->getJson(route('api.torrent.show', $torrent->torrent_id));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'id' => $torrent->id,
                'torrent_id' => $torrent->torrent_id,
            ],
        ]);
    }
}
