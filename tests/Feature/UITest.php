<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\FileEntry;
use App\TorrentEntry;

class UITest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_see_a_list_of_available_files()
    {
        $file1 = factory(FileEntry::class)->create();
        $file2 = factory(FileEntry::class)->create();
        $file3 = factory(FileEntry::class)->create();

        $response = $this->get(route('file.index'));

        $response->assertSuccessful();
        $response->assertSee($file1->basename);
        $response->assertSee($file2->basename);
        $response->assertSee($file3->basename);
    }

    /** @test */
    public function can_refresh_the_list_of_available_files()
    {
        $file = factory(FileEntry::class)->create();

        $response = $this->get(route('file.index'));

        $response->assertSee($file->basename);

        $file->delete();
        Storage::fake('source');
        Storage::disk('source')->put('file1', 'hello');

        $response = $this->get(route('file.refresh'));
        $response = $this->get(route('file.index'));

        $response->assertSee('file1');
    }

    /** @test */
    public function can_see_a_list_of_available_torrents()
    {
        $this->withoutExceptionHandling();
        $file1 = factory(TorrentEntry::class)->create();
        $file2 = factory(TorrentEntry::class)->create();
        $file3 = factory(TorrentEntry::class)->create();

        $response = $this->get(route('torrent.index'));

        $response->assertSuccessful();
        $response->assertSee($file1->name);
        $response->assertSee($file2->name);
        $response->assertSee($file3->name);
    }
}
