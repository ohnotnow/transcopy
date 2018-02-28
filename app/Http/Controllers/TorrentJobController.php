<?php

namespace App\Http\Controllers;

use App\TorrentEntry;
use Illuminate\Http\Request;

class TorrentJobController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->exists('copies')) {
            return response(422);
        }

        TorrentEntry::findMany($request->copies)->each->queueCopy();

        return response()->json([
            'data' => [
                'message' => count($request->copies) . ' files queued'
            ]
        ]);
    }
}
