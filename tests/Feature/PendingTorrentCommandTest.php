<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\TorrentEntry;
use App\Jobs\CopyFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PendingTorrentCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function torrents_which_are_marked_as_should_copy_are_queued_once_they_finish_downloading()
    {
        Queue::fake();
        $plainTorrent = factory(TorrentEntry::class)->create(['percent' => 90]);
        $shouldBeCopiedTorrent = factory(TorrentEntry::class)->create(['percent' => 90, 'should_copy' => true]);

        Artisan::call('transcopy:queuepending');

        Queue::assertNotPushed(CopyFile::class);

        $plainTorrent->update(['percent' => 100]);
        $shouldBeCopiedTorrent->update(['percent' => 100]);

        Artisan::call('transcopy:queuepending');

        Queue::assertPushed(CopyFile::class, function ($job) use ($shouldBeCopiedTorrent) {
            return $job->torrent->id == $shouldBeCopiedTorrent->id;
        });
        $this->assertFalse($shouldBeCopiedTorrent->fresh()->should_copy);
    }
}
