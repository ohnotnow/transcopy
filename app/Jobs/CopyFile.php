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
        /**
         * if the torrent is still downloading, then assume we 'pre-queued' the
         * copy to take place (ie, pressed the copy button while it was still
         * downloading in the UI) - so we put the job back on the queue and
         * try again in 5 minutes.
         */
        if ($this->torrent->isStillDownloading()) {
            $this->requeue();
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
    protected function requeue($delaySeconds = 5 * 60)
    {
        app(Torrent::class)->update($this->torrent->torrent_id);
        $this->dispatch(TorrentEntry::find($this->torrent->id))->delay(now()->addSeconds($delaySeconds));
    }
}
