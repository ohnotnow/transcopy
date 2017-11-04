<?php

namespace App;

use Illuminate\Support\Facades\Storage;

trait SharedCopyableLogic
{
    public function source()
    {
        return Storage::disk($this->diskName);
    }

    public function applyPathPrefix(string $filename) : string
    {
        return $this->source()->getDriver()->getAdapter()->applyPathPrefix($filename);
    }

    public function exists()
    {
        return $this->source()->exists($this->getBasename());
    }

    public function findFiles()
    {
        return collect($this->source()->listContents($this->getPath(), true))->filter(function ($entry) {
            return $entry['type'] === 'file';
        })->map(function ($entry) {
            $entry['fullpath'] = $this->applyPathPrefix($entry['path']);
            return $entry;
        });
    }

    public function markCopied()
    {
        $this->update(['was_copied' => true]);
    }
}