<?php

namespace App\Jobs;

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
}
