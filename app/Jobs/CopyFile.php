<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\FileEntry;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CopyFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $file;

    public function __construct(FileEntry $file)
    {
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->file->isDirectory()) {
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
        return Storage::disk('source')->getDriver()->getAdapter()->applyPathPrefix($this->file->path);
    }

    public function getFullDestinationPath()
    {
        return Storage::disk('destination')->getDriver()->getAdapter()->applyPathPrefix($this->file->path);
    }
}
