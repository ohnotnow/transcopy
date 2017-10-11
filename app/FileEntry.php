<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Copyable;
use App\SharedCopyableLogic;

class FileEntry extends Model implements Copyable
{
    use SharedCopyableLogic;

    protected $guarded = [];

    protected $diskName = 'files';

    public function isFile()
    {
        return $this->type === 'file';
    }

    public function isDirectory()
    {
        return !$this->isFile();
    }

    public function getFullPath()
    {
        return $this->source()->getDriver()->getAdapter()->applyPathPrefix($this->path);
    }

    public function getBasename()
    {
        return $this->path;
    }

    public function getPath()
    {
        return $this->path;
    }

    // public function source()
    // {
    //     return Storage::disk('files');
    // }

    // public function exists()
    // {
    //     return $this->source()->exists($this->getBasename());
    // }

    // public function applyPathPrefix(string $filename) : string
    // {
    //     return $this->source()->getDriver()->getAdapter()->applyPathPrefix($filename);
    // }

    // public function findFiles()
    // {
    //     return collect($this->source()->listContents($this->getPath(), true))->filter(function ($entry) {
    //         return $entry['type'] === 'file';
    //     })->map(function ($entry) {
    //         $entry['fullpath'] = $this->applyPathPrefix($entry['path']);
    //         return $entry;
    //     });
    // }
}
