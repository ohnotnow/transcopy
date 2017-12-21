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

    public $file;

    public function __construct(TorrentEntry $file)
    {
        $this->file = $file;
    }

    public function handle()
    {
        try {
            $this->file->markCopying();

            if ($this->file->isDirectory()) {
                $this->copyDirectory($this->file);
            } else {
                $this->copyFile($this->file->getFullPath(), $this->file->getBasename());
            }

            $this->file->markCopied();
        } catch (\Exception $e) {
            $this->file->markFailed();
            throw $e;
        }
    }

    protected function copyDirectory($directory)
    {
        return $directory->findFiles()->each(function ($entry) {
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
