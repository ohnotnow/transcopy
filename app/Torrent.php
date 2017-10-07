<?php

namespace App;

use DB;
use App\TorrentEntry;
use Transmission\Client;
use Transmission\Transmission;

class Torrent
{
    public function index()
    {
        $client = new Client();
        if (config('transcopy.username')) {
            $client->authenticate(config('transcopy.username'), config('transcopy.password'));
        }
        $transmission = new Transmission(config('transcopy.host', '127.0.0.1'), config('transcopy.port', 9091));
        $transmission->setClient($client);
        return DB::transaction(function () use ($transmission) {
            return collect($transmission->all())->map(function ($entry) {
                return TorrentEntry::updateOrCreate(['name' => $entry->getName()], [
                    'torrent_id' => $entry->getId(),
                    'name' => $entry->getName(),
                    'size' => $entry->getSize(),
                    'percent' => $entry->getPercentDone(),
                    'path' => $entry->getDownloadDir() . '/' . $entry->getName(),
                    'eta' => $entry->getEta()
                ]);
            });
        });
    }

    public function refresh()
    {
        TorrentEntry::truncate();
        return $this->index();
    }
}
