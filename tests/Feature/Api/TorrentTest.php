<?php

namespace Tests\Feature\Api;

use App\Torrent;
use Tests\TestCase;
use App\FakeTorrent;
use App\TorrentEntry;
use App\Jobs\CopyFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TorrentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_a_list_of_all_torrents_ordered_by_newest_torrent_id()
    {
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
                    'copy_failed' => $torrent1->copy_failed,
                ],
                [
                    'id' => $torrent3->id,
                    'torrent_id' => $torrent3->torrent_id,
                    'name' => $torrent3->webFriendlyName(),
                    'eta' => $torrent3->formattedEta(),
                    'size' => $torrent3->formattedSize(),
                    'copied' => $torrent3->wasAlreadyCopied(),
                    'incomplete' => $torrent3->isStillDownloading(),
                    'copy_failed' => $torrent3->copy_failed,
                ],
                [
                    'id' => $torrent2->id,
                    'torrent_id' => $torrent2->torrent_id,
                    'name' => $torrent2->webFriendlyName(),
                    'eta' => $torrent2->formattedEta(),
                    'size' => $torrent2->formattedSize(),
                    'copied' => $torrent2->wasAlreadyCopied(),
                    'incomplete' => $torrent2->isStillDownloading(),
                    'copy_failed' => $torrent2->copy_failed,
                ],
            ],
        ]);
    }

    /** @test */
    public function can_get_a_fresh_copy_of_a_torrent()
    {
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
                'copy_failed' => $torrent2->copy_failed,
            ],
        ]);
    }

    /** @test */
    public function can_trigger_copy_jobs_for_torrents()
    {
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
            return $job->torrent->id == $torrent1->id;
        });
        Queue::assertPushed(CopyFile::class, function ($job) use ($torrent3) {
            return $job->torrent->id == $torrent3->id;
        });
    }

    /** @test */
    public function trying_to_copy_a_torrent_which_is_still_downloading_marks_it_but_doesnt_queue_a_job()
    {
        Queue::fake();

        $torrent = factory(TorrentEntry::class)->create(['percent' => 90, 'should_copy' => false]);

        $response = $this->post(route('api.torrent.copy'), [
            'copies' => [
                $torrent->id,
            ]
        ]);

        $response->assertSuccessful();
        Queue::assertNotPushed(CopyFile::class);
        $this->assertTrue($torrent->fresh()->should_copy);
    }

    /** @test */
    public function can_clear_copy_flags_on_a_torrent()
    {
        $this->withoutExceptionHandling();
        $torrent = factory(TorrentEntry::class)->create([
            'was_copied' => true,
            'copy_failed' => true,
            'is_copying' => true,
            'should_copy' => true,
        ]);

        $response = $this->deleteJson(route('api.torrent.clear_flags', $torrent->id));

        $response->assertSuccessful();
        $torrent = $torrent->fresh();
        $this->assertFalse($torrent->was_copied);
        $this->assertFalse($torrent->copy_failed);
        $this->assertFalse($torrent->is_copying);
        $this->assertFalse($torrent->should_copy);
    }
}
