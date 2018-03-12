<?php

namespace App\Jobs;

use App\Torrent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use App\TorrentEntry;

class CopyFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $torrent;

    public function __construct(TorrentEntry $torrent)
    {
        $this->torrent = $torrent;
    }

    public function handle()
    {
        if ($this->torrent->isStillDownloading()) {
            $this->torrent->markShouldCopy();
            $this->tryAgainIn(5);
            return;
        }

        $this->torrent->markCopying();

        try {
            $this->copyTorrent();
        } catch (\Exception $e) {
            $this->torrent->markFailed();
            throw $e;
        }

        $this->torrent->markCopied();
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
        app(Torrent::class)->update($this->torrent->torrent_id);
        $this->dispatch(TorrentEntry::find($this->torrent->id))->delay(now()->addMinutes($delayMinutes));
    }
}
