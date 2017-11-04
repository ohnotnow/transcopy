<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\FileEntry;
use App\Jobs\CopyFile;
use App\Mail\CopyFailed;
use App\Mail\CopySucceeded;
use App\Filesystem;
use App\FakeTorrent;
use App\TorrentEntry;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_failed_job_will_send_a_notification_if_so_configured()
    {
        Storage::fake('files');
        Storage::fake('destination');
        Mail::fake();
        config(['transcopy' => ['send_failure_notifications' => true, 'notification_address' => 'test@example.com']]);
        $nonExistantFile = factory(FileEntry::class)->create(['type' => 'file']);

        try {
            CopyFile::dispatch($nonExistantFile);
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
        Storage::fake('files');
        Storage::fake('destination');
        Mail::fake();
        config(['transcopy' => ['send_failure_notifications' => false, 'notification_address' => 'test@example.com']]);
        $nonExistantFile = factory(FileEntry::class)->create(['type' => 'file']);

        try {
            CopyFile::dispatch($nonExistantFile);
        } catch (\Exception $e) {
            Mail::assertNotSent(CopyFailed::class);
            return true;
        }

        $this->fail('CopyFile did not throw an exception');
    }

    /** @test */
    public function a_successful_job_will_send_a_notification_if_so_configured()
    {
        Storage::fake('files');
        Storage::fake('destination');
        Mail::fake();
        config(['transcopy' => ['send_success_notifications' => true, 'notification_address' => 'test@example.com']]);
        Storage::disk('files')->put('test', 'hello');
        (new Filesystem)->index();
        $file = FileEntry::first();

        CopyFile::dispatch($file);

        Mail::assertSent(CopySucceeded::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function a_successful_job_will_not_send_a_notification_if_not_configured()
    {
        Storage::fake('files');
        Storage::fake('destination');
        Mail::fake();
        config(['transcopy' => ['send_failure_notifications' => false, 'notification_address' => 'test@example.com']]);
        Storage::disk('files')->put('test', 'hello');
        (new Filesystem)->index();
        $file = FileEntry::first();

        CopyFile::dispatch($file);

        Mail::assertNotSent(CopySucceeded::class);
    }


}
