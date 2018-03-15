<?php

namespace App\Http\Controllers;

use App\TorrentEntry;
use App\RedisStore;
use Illuminate\Http\Request;

class TorrentJobController extends Controller
{
    protected $redis;

    public function __construct(RedisStore $redis)
    {
        $this->redis = $redis;
    }

    public function store(Request $request)
    {
        $request->validate([
            'copies' => 'required|array|min:1',
        ]);

        $this->redis->findMany($request->copies)->each->queueCopy();

        return response()->json([
            'data' => [
                'message' => count($request->copies) . ' files queued'
            ]
        ]);
    }
}
