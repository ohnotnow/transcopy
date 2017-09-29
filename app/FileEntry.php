<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileEntry extends Model
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
}
