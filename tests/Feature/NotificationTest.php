<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\CopyFile;
use App\Mail\CopyFailed;
use App\Mail\CopySucceeded;
use App\FakeTorrent;
use App\TorrentEntry;
use App\Torrent;
use App\RedisStore;

class NotificationTest extends TestCase
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
    public function a_failed_job_will_send_a_notification_if_so_configured()
    {
        Storage::fake('torrents');
        Storage::fake('destination');
        Mail::fake();
        config([
            'transcopy' => [
                'send_failure_notifications' => true,
                'notification_address' => 'test@example.com'
            ]
        ]);
        $nonExistantTorrent = new TorrentEntry([
            'id' => 12345,
            'name' => 'whatever',
            'path' => 'testeroo',
            'percent' => 100,
        ]);
        $nonExistantTorrent->save();

        try {
            CopyFile::dispatch($nonExistantTorrent->id);
        } catch (\Exception $e) {
            Mail::assertSent(CopyFailed::class, function ($mail) {
                return $mail->hasTo('test@example.com');
            });
            return true;
        }

        $this->fail('CopyFile job did not throw an exception');
    }

    /** @test */
    public function a_failed_job_will_not_send_a_notification_if_not_configured()
    {
        Storage::fake('torrents');
        Storage::fake('destination');
        Mail::fake();
        config([
            'transcopy' => [
                'send_failure_notifications' => false,
                'notification_address' => 'test@example.com'
            ]
        ]);
        $nonExistantTorrent = new TorrentEntry([
            'id' => 12345,
            'name' => 'whatever',
            'path' => 'testeroo',
            'percent' => 100,
        ]);
        $nonExistantTorrent->save();

        try {
            CopyFile::dispatch($nonExistantTorrent->id);
        } catch (\Exception $e) {
            Mail::assertNotSent(CopyFailed::class);
            return true;
        }

        $this->fail('CopyFile did not throw an exception');
    }

    /** @test */
    public function a_successful_job_will_send_a_notification_if_so_configured()
    {
        Storage::fake('torrents');
        Storage::fake('destination');
        Mail::fake();
        config([
            'transcopy' => [
                'send_success_notifications' => true,
                'notification_address' => 'test@example.com'
            ]
        ]);
        Storage::disk('torrents')->put('test', 'hello');
        $this->getTransmissionClient();
        app(Torrent::class)->index();
        $torrent = app(RedisStore::class)->first();

        CopyFile::dispatch($torrent->id);

        Mail::assertSent(CopySucceeded::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function a_successful_job_will_not_send_a_notification_if_not_configured()
    {
        Storage::fake('torrents');
        Storage::fake('destination');
        Mail::fake();
        config([
            'transcopy' => [
                'send_failure_notifications' => false,
                'notification_address' => 'test@example.com'
            ]
        ]);
        Storage::disk('files')->put('test', 'hello');
        Storage::disk('torrents')->put('test', 'hello');
        $this->getTransmissionClient();
        app(Torrent::class)->index();
        $torrent = app(RedisStore::class)->first();

        CopyFile::dispatch($torrent->id);

        Mail::assertNotSent(CopySucceeded::class);
    }

    /** @test */
    public function queued_copy_jobs_dont_send_notifications_until_actually_copied()
    {
        // fake the storage
        Storage::fake('torrents');
        Storage::fake('destination');

        // bind our fake torrent API
        $this->getTransmissionClient();

        // we need to use the DB driver for the queue as sync doesn't understand ->delay() jobs :'-/
        config(['queue.default' => 'database']);
        Mail::fake();

        // put a file in storage
        Storage::disk('torrents')->put('file1', 'hello');

        // index the torrents
        app(Torrent::class)->index();
        $torrent = app(RedisStore::class)->first();

        // and fake it as still downloading
        $torrent->update(['percent' => 90]);

        // send the job to the queue
        CopyFile::dispatch($torrent->id);

        // it shouldn't have yet run
        $this->assertDatabaseMissing('jobs', ['id' => 2]);
        Mail::assertNotSent(CopySucceeded::class);

        // run the queue once
        Artisan::call('queue:work', ['--once' => true]);

        // the queued torrent will now be 'finished downloading' (see FakeTorrent.php)
        Mail::assertSent(CopySucceeded::class);
    }
}
