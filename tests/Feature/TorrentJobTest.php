<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\TorrentEntry;
use App\Jobs\CopyFile;

class TorrentJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_dispatch_copy_jobs_for_each_submitted_torrent()
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

        Queue::assertPushed(CopyFile::class, 2); // exactly 2 jobs were queued
        Queue::assertPushed(CopyFile::class, function ($job) use ($torrent1) {
            return $job->file->id == $torrent1->id;
        });
        Queue::assertPushed(CopyFile::class, function ($job) use ($torrent3) {
            return $job->file->id == $torrent3->id;
        });
    }
}
