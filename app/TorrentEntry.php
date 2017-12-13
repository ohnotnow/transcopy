<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TorrentEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'was_copied' => 'boolean',
        'is_copying' => 'boolean',
    ];

    protected $diskName = 'torrents';

    public function formattedEta()
    {
        if ($this->isComplete()) {
            return 'Done';
        }

        $mins = intval($this->eta / 60);
        if ($mins < 60) {
            return $mins . 'min';
        }

        $hours = intval($mins / 60);
        $mins = $mins % 60;
        return "{$hours}hr {$mins}mins";
    }

    public function isStillDownloading()
    {
        return $this->percent < 100;
    }

    public function isComplete()
    {
        return ! $this->isStillDownloading();
    }

    public function wasAlreadyCopied()
    {
        return $this->was_copied;
    }

    public function isCopying()
    {
        return $this->is_copying;
    }

    public function isFile()
    {
        return File::isFile($this->path);
    }

    public function isDirectory()
    {
        return ! $this->isFile();
    }

    public function getBasename()
    {
        return File::basename($this->path);
    }

    public function getFullPath()
    {
        return $this->path;
    }

    public function getPath()
    {
        return $this->getBasename();
    }

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
        if (!$this->source()->exists($this->getPath())) {
            throw new \InvalidArgumentException('Directory does not exist : ' . $this->getPath());
        }
        return collect($this->source()->listContents($this->getPath(), true))
            ->filter(function ($entry) {
                return $entry['type'] === 'file';
            })->map(function ($entry) {
                $entry['fullpath'] = $this->applyPathPrefix($entry['path']);
                return $entry;
            });
    }

    public function markCopied()
    {
        $this->update(['was_copied' => true, 'is_copying' => false]);
    }

    public function markCopying()
    {
        $this->update(['is_copying' => true, 'was_copied' => false]);
    }

    public function formattedSize()
    {
        $size = $this->size;
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 1) . 'GB';
        }
        if ($size >= 1048576) {
            return number_format($size / 1048576, 0) . 'MB';
        }
        if ($size >= 1024) {
            return number_format($size / 1024, 0) . 'KB';
        }
        return $size . 'bytes';
    }

    public function webFriendlyName()
    {
        $parts = preg_split('/[\._]+/', $this->getBasename());
        $extension = pathinfo($this->getBasename(), PATHINFO_EXTENSION);
        if ($extension) {
            $parts = array_slice($parts, 0, -1);
            $extension = '.' . $extension;
        }
        return implode(' ', $parts) . $extension;
    }
}
