<?php

namespace App;

use DB;
use App\TorrentEntry;
use App\RedisStore;
use Ohffs\LaravelTransmission\Client;
use App\Contracts\TorrentContract;

class Torrent implements TorrentContract
{
    protected $transmission;

    protected $redis;

    public function __construct(Client $transmission, RedisStore $redis)
    {
        $this->transmission = $transmission;
        $this->redis = $redis;
    }

    public function index()
    {
        return collect($this->transmission->all())->map(function ($entry) {
            return $this->store($entry);
        });
    }

    public function update($id)
    {
        $entry = $this->transmission->find(intval($id));
        return $this->store($entry);
    }

    protected function store($entry)
    {
        $torrent = $this->redis->find($entry->id);
        if (! $torrent) {
            $torrent = new TorrentEntry;
        }
        $torrent->update([
            'id' => $entry->id,
            'name' => $entry->name,
            'size' => $entry->totalSize,
            'percent' => $entry->percentDone * 100,
            'path' => $entry->downloadDir . '/' . $entry->name,
            'eta' => $entry->eta
        ]);
        return $torrent;

    }
}
