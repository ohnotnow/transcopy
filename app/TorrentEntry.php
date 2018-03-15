<?php

namespace App;

use App\Jobs\CopyFile;
use App\RedisStore;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TorrentEntry
{
    protected $attribs = [
        'id' => null,
        'name' => null,
        'size' => null,
        'percent' => null,
        'path' => null,
        'eta' => null,
        'was_copied' => false,
        'is_copying' => false,
        'copy_failed' => false,
        'should_copy' => false,
    ];

    protected $booleans = [
        'was_copied',
        'is_copying',
        'copy_failed',
        'should_copy',
    ];

    protected $diskName = 'torrents';

    public function __construct($attribs = [])
    {
        $this->attribs = array_merge($this->attribs, $attribs);
        $this->store = app(RedisStore::class);
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->attribs)) {
            if (in_array($key, $this->booleans)) {
                return !! $this->attribs[$key];
            }
            return $this->attribs[$key];
        }

        return null;
    }

    public function save()
    {
        return $this->update();
    }

    public function update($attribs = [])
    {
        $this->attribs = array_merge($this->attribs, $attribs);
        $this->store->update($this->attribs);
        return $this;
    }

    public static function find($torrentId)
    {
        return $this->store->find($torrentId);
    }

    public function toArray()
    {
        return $this->attribs;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function queueCopy()
    {
        CopyFile::dispatch($this->id);
    }

    public function markCopying()
    {
        $this->update([
            'is_copying' => true,
            'was_copied' => false,
            'copy_failed' => false,
            'should_copy' => false
        ]);
    }

    public function markCopied()
    {
        $this->update([
            'is_copying' => false,
            'was_copied' => true,
            'copy_failed' => false,
            'should_copy' => false
        ]);
    }

    public function markShouldCopy()
    {
        $this->update([
            'should_copy' => true
        ]);
    }

    public function markFailed()
    {
        $this->update([
            'is_copying' => false,
            'was_copied' => false,
            'copy_failed' => true
        ]);
    }

    public function clearFlags()
    {
        $this->update([
            'is_copying' => false,
            'was_copied' => false,
            'copy_failed' => false,
            'should_copy' => false,
        ]);
    }

    public function wasAlreadyCopied()
    {
        return $this->was_copied;
    }

    public function isCopying()
    {
        return $this->is_copying;
    }

    public function copyFailed()
    {
        return $this->copy_failed;
    }

    public function isStillDownloading()
    {
        return $this->percent < 100;
    }

    public function isComplete()
    {
        return ! $this->isStillDownloading();
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

    /**
     * Return the filename fully prefixed by the storage 'disk' filesystem path
     *
     * @param string $filename
     * @return string
     */
    public function applyPathPrefix(string $filename) : string
    {
        return $this->source()->getDriver()->getAdapter()->applyPathPrefix($filename);
    }

    public function exists()
    {
        return $this->source()->exists($this->getBasename());
    }

    /**
     * Returns an collection of files inside 'this' directory.
     *
     * @return array
     */
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

    /**
     * Splits filenames with no whitespace so they don't mess with page layout.
     * Does some extra faff if the filename has an extension at the end so we
     * don't end up with 'some file name mpg' and return more like
     * 'some file name.mpg'
     *
     * @return string
     */
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

    public function formattedEta()
    {
        if ($this->isComplete()) {
            return 'Done';
        }

        if ($this->eta < 0) {
            return 'Unknown';
        }

        if ($this->eta < 60) {
            return "{$this->eta}sec";
        }

        $mins = intval($this->eta / 60);
        if ($mins < 60) {
            return $mins . 'min';
        }

        $hours = intval($mins / 60);
        $mins = $mins % 60;
        return "{$hours}hr {$mins}mins";
    }
}
