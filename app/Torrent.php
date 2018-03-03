<?php

namespace App;

use DB;
use App\TorrentEntry;
use Transmission\Transmission;
use Ohffs\LaravelTransmission\Client;

class Torrent
{
    protected $transmission;

    public function __construct(Client $transmission)
    {
        $this->transmission = $transmission;
    }

    public function index()
    {
        $torrents = DB::transaction(function () {
            return collect($this->transmission->authenticate()->all())->map(function ($entry) {
                return $this->store($entry);
            });
        });
        TorrentEntry::whereNotIn('id', $torrents->pluck('id'))->delete();
        return $torrents;
    }

    public function refresh()
    {
        TorrentEntry::truncate();
        return $this->index();
    }

    public function update($id)
    {
        $entry = $this->transmission->authenticate()->find(intval($id));
        return DB::transaction(function () use ($entry) {
            return $this->store($entry);
        });
    }

    protected function store($entry)
    {
        return TorrentEntry::updateOrCreate(['name' => $entry->name], [
            'torrent_id' => $entry->id,
            'name' => $entry->name,
            'size' => $entry->totalSize,
            'percent' => $entry->percentDone * 100,
            'path' => $entry->downloadDir . '/' . $entry->name,
            'eta' => $entry->eta
        ]);
    }
}
