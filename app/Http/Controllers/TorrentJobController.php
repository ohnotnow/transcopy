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
            return response(422);
        }

        $fileList = [];
        foreach ($request->copies as $fileId) {
            $torrent = TorrentEntry::findOrFail($fileId);
            $filelist[] = $torrent->name;
            CopyFile::dispatch($torrent);
        }

        return response()->json(['data' => ['message' => implode(', ', $fileList)]]);
    }
}
