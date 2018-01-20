<?php

namespace App\Http\Controllers\Api;

use App\TorrentEntry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TorrentFlagsController extends Controller
{
    public function destroy($id)
    {
        $torrent = TorrentEntry::findOrFail($id)->clearFlags();

        return response()->json([
            'data' => [
                'message' => 'OK',
            ],
        ]);
    }
}
