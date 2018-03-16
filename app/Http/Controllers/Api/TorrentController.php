<?php

namespace App\Http\Controllers\Api;

use App\Contracts\TorrentContract;
use App\TorrentEntry;
use App\RedisStore;
use App\Http\Controllers\Controller;
use App\Http\Resources\TorrentEntryResource;

class TorrentController extends Controller
{
    public function __construct(RedisStore $redis, TorrentContract $transmission)
    {
        $this->redis = $redis;
        $this->transmission = $transmission;
    }

    public function index()
    {
        return $this->orderedTorrents();
    }

    public function show($id)
    {
        return new TorrentEntryResource(
            $this->transmission->update($id)
        );
    }

    public function update()
    {
        $oldTorrents = $this->redis->all()->pluck('id');
        $currentTorrents = $this->transmission->index()->pluck('id');
        $missingIds = $oldTorrents->diff($currentTorrents);
        $this->redis->deleteMany($missingIds);

        return $this->orderedTorrents();
    }

    protected function orderedTorrents()
    {
        return TorrentEntryResource::collection(
            $this->redis->all()->sortByDesc(function ($torrent, $key) {
                return $torrent->id;
            })->values()
        );
    }
}
