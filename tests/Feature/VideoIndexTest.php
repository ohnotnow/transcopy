<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Filesystem;

class VideoIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_a_list_of_video_files()
    {
        $filesystem = new Filesystem('./tests/data/original_files');

        $contents = $filesystem->index();

        $this->assertCount(6, $contents);
        dd($contents);
    }
}
