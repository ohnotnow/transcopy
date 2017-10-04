<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Filesystem;
use App\FileEntry;

class FilesystemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_a_list_of_files_in_a_directory()
    {
        Storage::fake('source');
        Storage::disk('source')->put('file1', 'hello');
        Storage::disk('source')->put('file2', 'there');
        Storage::disk('source')->put('dir1/file3', 'trout');
        Storage::disk('source')->put('dir1/file4', 'mask');
        Storage::disk('source')->put('dir2/file5', 'replica');

        $files = (new Filesystem)->index();

        $this->assertCount(4, FileEntry::all());
        $this->assertCount(4, $files);
        tap($files->pluck('path'), function ($names) {
            $this->assertTrue($names->contains('file1'));
            $this->assertTrue($names->contains('file2'));
            $this->assertTrue($names->contains('dir1'));
            $this->assertTrue($names->contains('dir2'));
        });
    }

    /** @test */
    public function can_refresh_the_list_of_files()
    {
        Storage::fake('source');
        factory(FileEntry::class, 8)->create();
        $this->assertCount(8, FileEntry::all());

        Storage::disk('source')->put('file1', 'hello'); // 1
        Storage::disk('source')->put('file2', 'there'); // 2
        Storage::disk('source')->put('dir1/file3', 'trout'); // 3
        Storage::disk('source')->put('dir1/file4', 'mask'); //
        Storage::disk('source')->put('dir2/file5', 'replica'); // 4
        $files = (new Filesystem)->refresh();

        $this->assertCount(4, FileEntry::all());
        $this->assertCount(4, $files);
        tap($files->pluck('path'), function ($names) {
            $this->assertTrue($names->contains('file1'));
            $this->assertTrue($names->contains('file2'));
            $this->assertTrue($names->contains('dir1'));
            $this->assertTrue($names->contains('dir2'));
        });
    }
}
