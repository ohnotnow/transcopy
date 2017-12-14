<?php

namespace App\Http\Controllers;

class TorrentController extends Controller
{
    public function index()
    {
        return view('torrents.index');
    }
}
