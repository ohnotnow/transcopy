<?php

namespace App\Jobs;

use App\Torrent;
use App\RedisStore;
use Illuminate\Bus\Queueable;
use App\Contracts\TorrentContract;
use Illuminate\Queue\SerializesModels;
use App\Exceptions\CopyFailedException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CopyFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $torrentId;

    public $torrent;

    public function __construct($torrentId)
    {
        $this->torrentId = $torrentId;
    }

    public function handle()
    {
        $this->torrent = $this->getTorrent();
        if ($this->torrent->isStillDownloading()) {
            $this->torrent->markShouldCopy();
            $this->tryAgainIn(1);
            return;
        }

        $start = now();

        $this->torrent->markCopying();

        $maxTries = config('transcopy.max_tries', 1);
        $tryCount = 0;

        while ($tryCount < $maxTries) {
            try {
                $this->copyTorrent();
                break;
            } catch (\Exception $e) {
                $tryCount = $tryCount + 1;
                $this->torrent->markRetry($tryCount);
                \Log::error($e->getMessage());
            }
        }

        if ($tryCount >= $maxTries) {
            $this->torrent->markFailed();
            throw new CopyFailedException($this->torrent->name);
        }

        $this->torrent->markCopied();
        $end = now();
        $speed = floor(($this->torrent->size / 1048576) / ($end->diffInSeconds($start)));
        \Log::info('Copied file in ' . $end->diffInMinutes($start) . 'minutes (' . $speed . 'mb/s');
    }

    protected function copyTorrent()
    {
        if ($this->torrent->isDirectory()) {

            return $this->copyDirectory($this->torrent);
        }

        return $this->copyFile($this->torrent->getFullPath(), $this->torrent->getBasename());
    }

    protected function copyDirectory($directory)
    {
        $directory->findFiles()->each(function ($entry) {
            $this->copyFile($entry['fullpath'], $entry['path']);
        });
    }

    protected function copyFile($sourceName, $destName)
    {
        if (!file_exists($sourceName)) {
            throw new \InvalidArgumentException('No such file ' . $sourceName);
        }

        Storage::disk('destination')->put($destName, fopen($sourceName, 'r+'));
    }

    /**
     * Put a new copy of this job onto the queue with a delay
     *
     * @return void
     */
    protected function tryAgainIn($delayMinutes = 5)
    {
        app(TorrentContract::class)->update($this->torrentId);

        $this->dispatch($this->torrentId)
             ->delay(now()->addMinutes($delayMinutes));
    }

    public function getTorrent()
    {
        return app(RedisStore::class)->find($this->torrentId);
    }
}
