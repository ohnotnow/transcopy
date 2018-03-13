<?php

namespace App\Http\Controllers\Api;

use App\Torrent;
use App\TorrentEntry;
use App\Http\Controllers\Controller;
use App\Http\Resources\TorrentEntryResource;

class TorrentController extends Controller
{
    public function index()
    {
        return $this->orderedTorrents();
    }

    public function show($id)
    {
        return new TorrentEntryResource(
            app(Torrent::class)->update($id)
        );
    }

    public function update()
    {
        app(Torrent::class)->index();

        return $this->orderedTorrents();
    }

    protected function orderedTorrents()
    {
        return TorrentEntryResource::collection(
            TorrentEntry::orderByDesc('torrent_id')->get()
        );
    }
}
