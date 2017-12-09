<?php

namespace App\Http\Controllers\Api;

use App\Torrent;
use App\TorrentEntry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TorrentEntryResource;

class TorrentListController extends Controller
{
    public function index()
    {
        return TorrentEntryResource::collection(TorrentEntry::orderByDesc('torrent_id')->get());
    }

    public function show($id)
    {
        return new TorrentEntryResource(app(Torrent::class)->update($id));
    }
}
