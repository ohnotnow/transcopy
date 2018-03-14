<?php

namespace App;

use App\TorrentEntry;
use Illuminate\Support\Facades\Redis;

class RedisStore
{
    protected $prefix = 'transcopy';

    public function find($torrentId)
    {
        return new TorrentEntry(Redis::hgetall("{$this->prefix}:{$torrentId}"));
    }

    public function all()
    {
        $members = Redis::smembers("{$this->prefix}:all");
        $list = [];
        foreach ($members as $torrentId) {
            $list[$torrentId] = new TorrentEntry(Redis::hgetall("{$this->prefix}:{$torrentId}"));
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
}
