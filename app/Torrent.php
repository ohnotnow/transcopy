<?php

namespace App;

use DB;
use App\TorrentEntry;
use Transmission\Client;
use Transmission\Transmission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Torrent
{
    protected $transmission;

    public function __construct(Transmission $transmission)
    {
        $this->transmission = $transmission;
    }

    public function index()
    {
        return DB::transaction(function () {
            return collect($this->transmission->all())->map(function ($entry) {
                return $this->store($entry);
            });
        });
    }

    public function refresh()
    {
        TorrentEntry::truncate();
        return $this->index();
    }

    public function update($id)
    {
        $entry = $this->transmission->get(intval($id));
        return $this->store($entry);
    }

    protected function store($entry)
    {
        return TorrentEntry::updateOrCreate(['name' => $entry->getName()], [
            'torrent_id' => $entry->getId(),
            'name' => $entry->getName(),
            'size' => $entry->getSize(),
            'percent' => $entry->getPercentDone(),
            'path' => $entry->getDownloadDir() . '/' . $entry->getName(),
            'eta' => $entry->getEta()
        ]);
    }
}
