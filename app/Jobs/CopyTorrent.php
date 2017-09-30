<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\TorrentEntry;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CopyTorrent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $torrent;

    public function __construct(TorrentEntry $torrent)
    {
        $this->torrent = $torrent;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->torrent->isDirectory()) {
            return $this->copyDirectory();
        }
        return $this->copyFile();
    }

    public function copyDirectory()
    {
        return File::copyDirectory($this->getFullSourcePath(), $this->getFullDestinationPath());
    }

    public function copyFile()
    {
        return File::copy($this->getFullSourcePath(), $this->getFullDestinationPath());
    }

    public function getFullSourcePath()
    {
        return $this->torrent->path;
    }

    public function getFullDestinationPath()
    {
        return Storage::disk('destination')->getDriver()->getAdapter()->applyPathPrefix($this->torrent->basename());
    }
}
