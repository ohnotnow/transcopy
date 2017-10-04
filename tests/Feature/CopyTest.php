<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Filesystem;
use App\FileEntry;
use App\TorrentEntry;
use App\Jobs\CopyFile;
use App\FakeTorrent;

class CopyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_copy_a_regular_file_entry()
    {
        Storage::fake('source');
        Storage::fake('destination');
        Storage::disk('source')->put('file1', 'hello');
        (new Filesystem)->index();
        $file = FileEntry::first();

        CopyFile::dispatch($file);

        Storage::disk('destination')->assertExists('file1');
    }

    /** @test */
    public function can_recusively_copy_a_file_entry_which_is_a_directory()
    {
        Storage::fake('source');
        Storage::fake('destination');
        Storage::disk('source')->put('dir1/file1', 'dead');
        Storage::disk('source')->put('dir1/file2', 'apples');
        Storage::disk('source')->put('dir1/dir2/file3', 'dont');
        Storage::disk('source')->put('dir1/dir2/file4', 'rot');
        (new Filesystem)->index();
        $file = FileEntry::first();

        CopyFile::dispatch($file);

        Storage::disk('destination')->assertExists('dir1/file1');
        Storage::disk('destination')->assertExists('dir1/file2');
        Storage::disk('destination')->assertExists('dir1/dir2/file3');
        Storage::disk('destination')->assertExists('dir1/dir2/file4');
    }

    /** @test */
    public function can_copy_a_regular_torrrent_entry()
    {
        Storage::fake('torrents');
        Storage::fake('destination');
        Storage::disk('torrents')->put('file1', 'hello');
        app(FakeTorrent::class)->index();
        $torrent = TorrentEntry::first();

        CopyFile::dispatch($torrent);

        Storage::disk('destination')->assertExists('file1');
    }

    /** @test */
    public function can_recusively_copy_a_torrent_entry_which_is_a_directory()
    {
        Storage::fake('torrents');
        Storage::fake('destination');
        Storage::disk('torrents')->put('dir1/file1', 'dead');
        Storage::disk('torrents')->put('dir1/file2', 'apples');
        Storage::disk('torrents')->put('dir1/dir2/file3', 'dont');
        Storage::disk('torrents')->put('dir1/dir2/file4', 'rot');
        app(FakeTorrent::class)->index();
        $torrent = TorrentEntry::first();

        CopyFile::dispatch($torrent);

        Storage::disk('destination')->assertExists('dir1/file1');
        Storage::disk('destination')->assertExists('dir1/file2');
        Storage::disk('destination')->assertExists('dir1/dir2/file3');
        Storage::disk('destination')->assertExists('dir1/dir2/file4');
    }
}
