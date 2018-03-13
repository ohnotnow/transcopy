<?php

namespace App\Http\Controllers;

use App\TorrentEntry;
use Illuminate\Http\Request;

class TorrentJobController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'copies' => 'required|array|min:1',
        ]);
        // if (!$request->exists('copies')) {
        //     return response(422);
        // }

        TorrentEntry::findMany($request->copies)->each->queueCopy();

        return response()->json([
            'data' => [
                'message' => count($request->copies) . ' files queued'
            ]
        ]);
    }
}
