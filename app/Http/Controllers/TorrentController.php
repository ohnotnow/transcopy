<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Torrent;
use App\TorrentEntry;

class TorrentController extends Controller
{
    public function index()
    {
        return view('torrents.index');
    }
}
