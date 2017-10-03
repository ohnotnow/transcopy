<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Filesystem;
use App\FileEntry;
use App\Jobs\CopyFile;

class CopyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_copy_a_file_entry()
    {
        Storage::fake('source');
        Storage::fake('destination');
        Storage::disk('source')->put('file1', 'hello');
        Storage::disk('source')->assertExists('file1');
        (new Filesystem)->index();
        $file = FileEntry::first();

        //dd($file->getBasename());
        $job = CopyFile::dispatch($file);

        //dddd(Storage::disk('destination')->listContents());
        Storage::disk('destination')->assertExists('file1');
    }
}
