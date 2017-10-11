<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Torrent;
use App\TorrentEntry;

class TorrentController extends Controller
{
    public function update($id)
    {
        app(Torrent::class)->update($id);
        return redirect()->route('torrent.index');
    }
}
