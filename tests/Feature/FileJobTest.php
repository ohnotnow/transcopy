<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\FileEntry;
use App\Jobs\CopyFile;

class FileJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_dispatch_copy_jobs_for_each_submitted_file()
    {
        Queue::fake();

        $file1 = factory(FileEntry::class)->create();
        $file2 = factory(FileEntry::class)->create();
        $file3 = factory(FIleEntry::class)->create();

        $response = $this->post(route('file.copy'), [
            'copies' => [
                $file1->id,
                $file3->id,
            ]
        ]);

        Queue::assertPushed(CopyFile::class, 2); // exactly 2 jobs were queued
        Queue::assertPushed(CopyFile::class, function ($job) use ($file1) {
            return $job->file->id == $file1->id;
        });
        Queue::assertPushed(CopyFile::class, function ($job) use ($file3) {
            return $job->file->id == $file3->id;
        });
    }
}
