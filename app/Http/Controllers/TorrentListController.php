<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Torrent;
use App\TorrentEntry;

class TorrentListController extends Controller
{
    public function index()
    {
        $torrents = TorrentEntry::orderByDesc('torrent_id')->get();
        return view('torrents.index', compact('torrents'));
    }

    public function update($id = null)
    {
        app(Torrent::class)->index();
        return redirect()->route('torrent.index');
    }
}
