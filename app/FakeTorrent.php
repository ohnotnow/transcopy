<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use App\TorrentEntry;
use App\Filesystem;
use App\RedisStore;
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
            $torrent = new TorrentEntry([
                'id' => md5($entry['path']),
                'name' => $entry['name'] ?? $entry['path'],
                'size' => $entry['type'] === 'file' ? $entry['size'] : 0,
                'percent' => 100,
                'path' => Storage::disk('torrents')->getDriver()->getAdapter()->applyPathPrefix($entry['path']),
                'eta' => $this->faker->randomNumber(),
            ]);
            $torrent->save();
            return $torrent;
        });
    }

    public function refresh()
    {
        app(RedisStore::class)->clear();
        return $this->index();
    }

    public function update($id)
    {
        $torrent = app(RedisStore::class)->find($id);
        $torrent->update([
            'percent' => 100,
            'eta' => $this->faker->randomNumber(),
        ]);
        return $torrent;
    }
}
