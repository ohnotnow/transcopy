<?php

namespace App\Http\Controllers\Api;

use App\RedisStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TorrentFlagsController extends Controller
{
    public function destroy($id)
    {
        app(RedisStore::class)->find($id)->clearFlags();

        return response()->json([
            'data' => [
                'message' => 'OK',
            ],
        ]);
    }
}
