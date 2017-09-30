<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Torrent;
use App\TorrentEntry;

class TorrentController extends Controller
{
    public function index()
    {
        $torrents = TorrentEntry::orderByDesc('torrent_id')->get();
        return view('torrents.index', compact('torrents'));
    }

    public function update()
    {
        (new Torrent)->refresh();
        return redirect()->route('torrent.index');
    }
}
