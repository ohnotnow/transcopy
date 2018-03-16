<?php

namespace App;

use App\TorrentEntry;
use Illuminate\Support\Facades\Redis;

class RedisStore
{
    protected $prefix = 'transcopy';

    public function find($torrentId)
    {
        $torrentData = Redis::hgetall("{$this->prefix}:{$torrentId}");
        if (! array_key_exists('id', $torrentData)) {
            return null;
        }
        return new TorrentEntry($torrentData);
    }

    public function findMany($torrentIds)
    {
        return collect($torrentIds)->map(function ($id) {
            return $this->find($id);
        });
    }

    public function all()
    {
        $members = Redis::smembers("{$this->prefix}:all");
        $list = [];
        foreach ($members as $torrentId) {
            $list[] = new TorrentEntry(Redis::hgetall("{$this->prefix}:{$torrentId}"));
        }
        return collect($list);
    }

    public function update($torrentData)
    {
        Redis::sadd("{$this->prefix}:all", $torrentData['id']);
        Redis::hmset("{$this->prefix}:{$torrentData['id']}", $torrentData);
        return $this->find($torrentData['id']);
    }

    public function delete($torrentId)
    {
        Redis::srem("{$this->prefix}:all", $torrentId);
        return Redis::del("{$this->prefix}:{$torrentId}");
    }

    public function first()
    {
        return $this->all()->first();
    }

    public function clear()
    {
        $members = Redis::smembers("{$this->prefix}:all");
        Redis::pipeline(function ($pipe) use ($members) {
            foreach ($members as $torrentId) {
                $pipe->del("{$this->prefix}:{$torrentId}");
            }
        });
        return Redis::del("{$this->prefix}:all");
    }

    public function deleteMany($torrentIds)
    {
        Redis::pipeline(function ($pipe) use ($torrentIds) {
            foreach ($torrentIds as $key => $torrentId) {
                $pipe->del("{$this->prefix}:{$torrentId}");
                $pipe->srem("{$this->prefix}:all", $torrentId);
            }
        });
    }
}
