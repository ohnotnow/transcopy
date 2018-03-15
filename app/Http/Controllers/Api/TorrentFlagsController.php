<?php

namespace App\Http\Controllers\Api;

use App\RedisStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TorrentFlagsController extends Controller
{
    protected $redis;

    public function __construct(RedisStore $redis)
    {
        $this->redis = $redis;
    }

    public function destroy($id)
    {
        $this->redis->find($id)->clearFlags();

        return response()->json([
            'data' => [
                'message' => 'OK',
            ],
        ]);
    }
}
