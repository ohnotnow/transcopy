<?php

namespace Tests\Feature;

use App\FileEntry;
use Tests\TestCase;
use App\Filesystem;
use App\FakeTorrent;
use App\TorrentEntry;
use App\Jobs\CopyFile;
use App\Torrent;
use App\RedisStore;
use App\Mail\CopyFailed;
use App\Mail\CopySucceeded;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CopyTest extends TestCase
{
    use RefreshDatabase;

    protected function getTransmissionClient()
    {
        app()->bind(Torrent::class, function ($app) {
            return app(FakeTorrent::class);
        });
        return app(Torrent::class);
    }

    /** @test */
    public function can_copy_a_regular_torrent_entry()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        Storage::fake('torrents');
        Storage::fake('destination');
        Storage::disk('torrents')->put('file1', 'hello');
        app(FakeTorrent::class)->index();
        $torrent = app(RedisStore::class)->first();

        CopyFile::dispatch($torrent->id);

        Storage::disk('destination')->assertExists('file1');
    }

    /** @test */
    public function can_recusively_copy_a_torrent_entry_which_is_a_directory()
    {
        Mail::fake();
        Storage::fake('torrents');
        Storage::fake('destination');
        Storage::disk('torrents')->put('dir1/file1', 'dead');
        Storage::disk('torrents')->put('dir1/file2', 'apples');
        Storage::disk('torrents')->put('dir1/dir2/file3', 'dont');
        Storage::disk('torrents')->put('dir1/dir2/file4', 'rot');
        app(FakeTorrent::class)->index();
        $torrent = app(RedisStore::class)->first();

        CopyFile::dispatch($torrent->id);

        Storage::disk('destination')->assertExists('dir1/file1');
        Storage::disk('destination')->assertExists('dir1/file2');
        Storage::disk('destination')->assertExists('dir1/dir2/file3');
        Storage::disk('destination')->assertExists('dir1/dir2/file4');
    }

    /** @test */
    public function a_successful_job_will_mark_its_file_or_torrent_as_copied_in_the_db()
    {
        Storage::fake('destination');
        Mail::fake();
        config(['transcopy' => ['send_success_notifications' => false]]);
        Storage::disk('torrents')->put('file1', 'hello');
        app(FakeTorrent::class)->index();
        $torrent = app(RedisStore::class)->first();
        $this->assertFalse($torrent->wasAlreadyCopied());

        CopyFile::dispatch($torrent->id);

        $torrent = app(RedisStore::class)->first();
        $this->assertTrue($torrent->wasAlreadyCopied());
    }

    /** @test */
    public function a_failed_job_will_mark_itself_as_such()
    {
        $this->getTransmissionClient();
        Storage::fake('destination');
        Mail::fake();
        $nonExistantTorrent = new TorrentEntry([
            'id' => 12345,
            'name' => 'whatever',
            'path' => 'testeroo',
            'percent' => 100,
        ]);
        $nonExistantTorrent->save();
        $this->assertFalse($nonExistantTorrent->copyFailed());

        try {
            CopyFile::dispatch($nonExistantTorrent->id);
            $this->fail('Expected an exception and none thrown');
        } catch (\Exception $e) {
            $this->assertTrue(app(RedisStore::class)->find($nonExistantTorrent->id)->copyFailed());
        }
    }

    /** @test */
    public function if_a_torrent_is_still_downloading_a_new_job_is_fired_with_a_five_minute_delay_and_a_flag_is_set_on_the_torrent()
    {
        Storage::fake('destination');
        $this->getTransmissionClient();
        config(['queue.default' => 'database']);
        Mail::fake();
        Storage::disk('torrents')->put('file1', 'hello');
        app(FakeTorrent::class)->index();
        $torrent = app(RedisStore::class)->first();
        $torrent->update(['percent' => 90]);

        CopyFile::dispatch($torrent->id);

        $this->assertDatabaseMissing('jobs', ['id' => 2]);

        Artisan::call('queue:work', ['--once' => true]);

        $this->assertDatabaseHas('jobs', ['id' => 2]);
        $newJob = \DB::table('jobs')->where('id', '=', 2)->first();
        $this->assertEquals(
            \Carbon\Carbon::createFromTimestamp($newJob->available_at)->format('d/m/Y H:i'),
            now()->addMinutes(5)->format('d/m/Y H:i')
        );
    }
}
