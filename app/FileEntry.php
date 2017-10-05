<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Copyable;

class FileEntry extends Model implements Copyable
{
    protected $guarded = [];

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

    public function source()
    {
        return Storage::disk('files');
    }

    public function exists()
    {
        return $this->source()->exists($this->getBasename());
    }
}
