<?php

namespace Tests\Feature\Api;

use App\Torrent;
use Tests\TestCase;
use App\FakeTorrent;
use App\TorrentEntry;
use App\RedisStore;
use App\Jobs\CopyFile;
use App\Contracts\TorrentContract;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TorrentTest extends TestCase
{
    use RefreshDatabase;

    protected function getTransmissionClient()
    {
        app()->bind(TorrentContract::class, function ($app) {
            return app(FakeTorrent::class);
        });
        return app(TorrentContract::class);
    }

    /** @test */
    public function can_get_a_list_of_all_torrents_ordered_by_newest_torrent_id()
    {
        $this->getTransmissionClient();
        $torrent1 = new TorrentEntry([
            'id' => 3,
            'name' => 'whatever3',
            'path' => 'testeroo3',
            'percent' => 3,
            'size' => 3000000,
        ]);
        $torrent1->save();
        $torrent2 = new TorrentEntry([
            'id' => 1,
            'name' => 'whatever1',
            'path' => 'testeroo1',
            'percent' => 1,
            'size' => 1000000,
        ]);
        $torrent2->save();
        $torrent3 = new TorrentEntry([
            'id' => 5,
            'name' => 'whatever5',
            'path' => 'testeroo5',
            'percent' => 5,
            'size' => 5000000,
        ]);
        $torrent3->save();

        $response = $this->getJson(route('api.torrent.index'));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                [
                    'id' => $torrent3->id,
                    'name' => $torrent3->webFriendlyName(),
                    'eta' => $torrent3->formattedEta(),
                    'size' => $torrent3->formattedSize(),
                    'copied' => $torrent3->wasAlreadyCopied(),
                    'incomplete' => $torrent3->isStillDownloading(),
                    'copy_failed' => $torrent3->copy_failed,
                ],
                [
                    'id' => $torrent1->id,
                    'name' => $torrent1->webFriendlyName(),
                    'eta' => $torrent1->formattedEta(),
                    'size' => $torrent1->formattedSize(),
                    'copied' => $torrent1->wasAlreadyCopied(),
                    'incomplete' => $torrent1->isStillDownloading(),
                    'copy_failed' => $torrent1->copy_failed,
                ],
                [
                    'id' => $torrent2->id,
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
        $this->withoutExceptionHandling();
        $this->getTransmissionClient();
        $torrent1 = new TorrentEntry([
            'id' => 3,
            'name' => 'whatever3',
            'path' => 'testeroo3',
            'percent' => 3,
            'size' => 3000000,
        ]);
        $torrent1->save();
        $torrent2 = new TorrentEntry([
            'id' => 1,
            'name' => 'whatever1',
            'path' => 'testeroo1',
            'percent' => 1,
            'size' => 1000000,
        ]);
        $torrent2->save();

        $response = $this->getJson(route('api.torrent.show', $torrent2->id));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'id' => 1,
                'name' => $torrent2->webFriendlyName(),
                'copy_failed' => $torrent2->copy_failed,
            ],
        ]);
    }

    /** @test */
    public function can_clear_and_refresh_the_list_of_torrents()
    {
        $this->withoutExceptionHandling();
        $this->getTransmissionClient();
        $removedTorrent = new TorrentEntry([
            'id' => 99999,
            'name' => 'whatever3',
            'path' => 'testeroo3',
            'percent' => 3,
            'size' => 3000000,
        ]);
        $removedTorrent->save();
        \Storage::disk('torrents')->put('file1', 'hello');

        $response = $this->postJson(route('api.torrent.refresh'));

        $response->assertSuccessful();
        $this->assertNull(app(RedisStore::class)->find($removedTorrent->id));
        $this->assertEquals('file1', app(RedisStore::class)->first()->name);
    }

    /** @test */
    public function can_trigger_copy_jobs_for_torrents()
    {
        $this->getTransmissionClient();
        Queue::fake();

        $torrent1 = new TorrentEntry([
            'id' => 3,
            'name' => 'whatever3',
            'path' => 'testeroo3',
            'percent' => 3,
            'size' => 3000000,
        ]);
        $torrent1->save();
        $torrent2 = new TorrentEntry([
            'id' => 1,
            'name' => 'whatever1',
            'path' => 'testeroo1',
            'percent' => 1,
            'size' => 1000000,
        ]);
        $torrent2->save();
        $torrent3 = new TorrentEntry([
            'id' => 5,
            'name' => 'whatever5',
            'path' => 'testeroo5',
            'percent' => 5,
            'size' => 5000000,
        ]);
        $torrent3->save();


        $response = $this->post(route('api.torrent.copy'), [
            'copies' => [
                $torrent1->id,
                $torrent3->id,
            ]
        ]);

        $response->assertSuccessful();
        Queue::assertPushed(CopyFile::class, 2); // exactly 2 jobs were queued
        Queue::assertPushed(CopyFile::class, function ($job) use ($torrent1) {
            return $job->getTorrent()->id == $torrent1->id;
        });
        Queue::assertPushed(CopyFile::class, function ($job) use ($torrent3) {
            return $job->getTorrent()->id == $torrent3->id;
        });
    }

    /** @test */
    public function trying_to_copy_a_torrent_which_is_still_downloading_still_queues_an_initial_job()
    {
        // note: the queued job itself checks to see if the file is still downloading, if so
        // it 'requeues' until it is available. See
        // 'CopyTest @ if_a_torrent_is_still_downloading_a_new_job_is_fired_with_a_five_minute_delay_and_a_flag_is_set_on_the_torrent'

        $this->getTransmissionClient();
        Queue::fake();

        $torrent1 = new TorrentEntry([
            'id' => 3,
            'name' => 'whatever3',
            'path' => 'testeroo3',
            'percent' => 3,
            'size' => 3000000,
        ]);
        $torrent1->save();

        $response = $this->post(route('api.torrent.copy'), [
            'copies' => [
                $torrent1->id,
            ]
        ]);

        $response->assertSuccessful();
        Queue::assertPushed(CopyFile::class);
    }

    /** @test */
    public function can_clear_copy_flags_on_a_torrent()
    {
        $this->withoutExceptionHandling();
        $this->getTransmissionClient();
        $torrent1 = new TorrentEntry([
            'id' => 3,
            'name' => 'whatever3',
            'path' => 'testeroo3',
            'percent' => 3,
            'size' => 3000000,
            'was_copied' => true,
            'copy_failed' => true,
            'is_copying' => true,
            'should_copy' => true,
        ]);
        $torrent1->save();

        $response = $this->deleteJson(route('api.torrent.clear_flags', $torrent1->id));

        $response->assertSuccessful();
        $torrent = app(RedisStore::class)->find($torrent1->id);
        $this->assertFalse($torrent->was_copied);
        $this->assertFalse($torrent->copy_failed);
        $this->assertFalse($torrent->is_copying);
        $this->assertFalse($torrent->should_copy);
    }
}
