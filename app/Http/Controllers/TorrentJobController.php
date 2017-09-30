<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CopyTorrent;
use App\TorrentEntry;
use Carbon\Carbon;

class TorrentJobController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->exists('copies')) {
            return redirect()->back();
        }

        $fileList = [];
        foreach ($request->copies as $fileId) {
            $torrent = TorrentEntry::findOrFail($fileId);
            $fileList[] = $torrent->basename();
            CopyTorrent::dispatch($torrent);
        }

        return redirect()->route('torrent.index')->with(['fileList' => $fileList]);
    }
}
