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
        $stime = time();
        $oldTorrents = $this->redis->all();
        $currentTorrents = $this->transmission->index();
        $missingTorrents = $oldTorrents->reject(function ($torrent) use ($currentTorrents) {
            return $currentTorrents->pluck('id')->contains($torrent->id);
        });
        $this->redis->deleteMany($missingTorrents->pluck('id'));
        \Log::info('Clearing possible old torrents took ' . (time() - $stime) . ' seconds');

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
