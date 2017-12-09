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

        foreach ($request->copies as $fileId) {
            CopyFile::dispatch(TorrentEntry::findOrFail($fileId));
        }

        return response(200);
    }
}
