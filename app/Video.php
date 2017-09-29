<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Filesystem;
use SSH;

class Video extends Model
{
    public function copy()
    {
        return SSH::put($this->path, config('transcopy.destination'));
    }
}
