<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Copyable;

class TorrentEntry extends Model implements Copyable
{
    protected $guarded = [];

    public function isStillDownloading()
    {
        return $this->eta >= 0;
    }

    public function formattedEta()
    {
        if ($this->eta >= 0) {
            return intval($this->eta / 60) . 'min';
        }
        return 'Done';
    }

    public function formattedPercentDone()
    {
        return $this->percent;
    }

    public function formattedSize()
    {
        $size = $this->size;
        if ($size >= 1073741824) {
            return number_format($size / 1073741824, 1) . 'GB';
        }
        if ($size >= 1048576) {
            return number_format($size / 1048576, 0). 'MB';
        }
        if ($size >= 1024) {
            return number_format($size / 1024, 0) . 'KB';
        }
        return $size . 'bytes';
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
        return Storage::disk('torrents');
    }

    public function exists()
    {
        return $this->source()->exists($this->getBasename());
    }
}
