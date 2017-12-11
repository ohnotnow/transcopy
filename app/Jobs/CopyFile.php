<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Copyable;

class CopyFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $file;

    public function __construct(Copyable $file)
    {
        $this->file = $file;
    }

    public function handle()
    {
        $this->file->markCopying();
        if ($this->file->isDirectory()) {
            $this->copyDirectory($this->file);
        } else {
            $this->copyFile($this->file->getFullPath(), $this->file->getBasename());
        }
        
        $this->file->markCopied();
    }

    protected function copyDirectory($directory)
    {
        return $directory->findFiles()->each(function ($entry) {
            $this->copyFile($entry['fullpath'], $entry['path']);
        });
    }

    protected function copyFile($sourceName, $destName)
    {
        Storage::disk('destination')->put($destName, fopen($sourceName, 'r+'));
        Log::info('Copied ' . $destName);
    }
}
