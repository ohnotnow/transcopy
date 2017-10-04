<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use App\FileEntry;

class CopyFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function handle()
    {
        if ($this->file->isDirectory()) {
            return $this->copyDirectory($this->file);
        }
        return $this->copyFile($this->file->getFullPath(), $this->file->getBasename());
    }

    protected function copyDirectory($directory)
    {
        return collect($directory->source()->listContents($directory->getPath(), true))->filter(function ($entry) {
            return $entry['type'] === 'file';
        })->each(function ($entry) {
            $fullPath = $this->file->source()->getDriver()->getAdapter()->applyPathPrefix($entry['path']);
            $this->copyFile($fullPath, $entry['path']);
        });
    }

    protected function copyFile($sourceName, $destName)
    {
        //dd($sourceName, $destName);
        Storage::disk('destination')->put($destName, fopen($sourceName, 'r+'));
    }
}
