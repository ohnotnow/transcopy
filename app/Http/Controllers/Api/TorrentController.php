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
        $stime1 = time();
        $oldTorrents = $this->redis->all()->pluck('id');
        $currentTorrents = $this->transmission->index()->pluck('id');
        $missingIds = $oldTorrents->diff($currentTorrents);
        // $missingTorrents = $oldTorrents->reject(function ($torrent) use ($currentTorrents) {
        //     return $currentTorrents->pluck('id')->contains($torrent->id);
        // });
        \Log::info('Finding missing torrents took ' . (time() - $stime1) . ' seconds');
        $stime2 = time();
        // $this->redis->deleteMany($missingTorrents->pluck('id'));
        $this->redis->deleteMany($missingIds);
        \Log::info('Clearing possible old torrents took ' . (time() - $stime2) . ' seconds');

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
