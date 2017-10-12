<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Copyable;
use App\SharedCopyableLogic;

class TorrentEntry extends Model implements Copyable
{
    use SharedCopyableLogic;

    protected $guarded = [];

    protected $diskName = 'torrents';

    public function isStillDownloading()
    {
        return $this->eta >= 0;
    }

    public function formattedEta()
    {
        if ($this->isStillDownloading()) {
            return intval($this->eta / 60) . 'min';
        }
        return 'Done';
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
}
