<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class TorrentEntry extends Model
{
    protected $guarded = [];

    public function formattedEta()
    {
        if ($this->eta >= 0) {
            return $this->eta / 60;
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
            return number_format($size / 1073741824, 0) . 'GB';
        }
        if ($size >= 1048576) {
            return number_format($size / 1048576, 0). 'MB';
        }
        if ($size >= 1024) {
            return number_format($size / 1024, 0) . 'KB';
        }
        return $bytes . 'bytes';
    }

    public function isFile()
    {
        return File::isFile($this->path);
    }

    public function isDirectory()
    {
        return ! $this->isFile();
    }

    public function basename()
    {
        return File::basename($this->path);
    }
}
