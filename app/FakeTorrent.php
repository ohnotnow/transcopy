<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use App\TorrentEntry;
use App\Filesystem;
use Faker\Generator as Faker;

class FakeTorrent
{
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function index()
    {
        return collect(Storage::disk('torrents')->listContents())->map(function ($entry) {
            return TorrentEntry::updateOrCreate(['path' => $entry['path']], [
                'torrent_id' => $this->faker->unique()->randomNumber(),
                'name' => $entry['path'],
                'size' => $entry['type'] === 'file' ? $entry['size'] : 0,
                'percent' => $this->faker->numberBetween(1, 100),
                'path' => Storage::disk('torrents')->getDriver()->getAdapter()->applyPathPrefix($entry['path']),
                'eta' => $this->faker->randomNumber(),
            ]);
        });
    }

    public function refresh()
    {
        TorrentEntry::truncate();
        return $this->index();
    }
}
