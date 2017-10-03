<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CopyFile;
use App\TorrentEntry;

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
            $fileList[] = $torrent->getBasename();
            CopyFile::dispatch($torrent);
        }

        return redirect()->route('torrent.index')->with(['fileList' => $fileList]);
    }
}
