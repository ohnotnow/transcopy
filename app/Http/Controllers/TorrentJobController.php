<?php

namespace App\Http\Controllers;

use App\TorrentEntry;
use App\RedisStore;
use Illuminate\Http\Request;

class TorrentJobController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'copies' => 'required|array|min:1',
        ]);

        app(RedisStore::class)->findMany($request->copies)->each->queueCopy();

        return response()->json([
            'data' => [
                'message' => count($request->copies) . ' files queued'
            ]
        ]);
    }
}
