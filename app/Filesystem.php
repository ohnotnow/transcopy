<?php

namespace App;

use DB;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Adapter\Local;
use Illuminate\Support\Facades\Storage;
use App\FileEntry;

class Filesystem
{
    protected $source;

    public function __construct($source = null)
    {
        if (!$source) {
            $source = Storage::disk('files');
        }
        $this->source = $source;
    }

    public function index()
    {
        return DB::transaction(function () {
            return collect($this->source->listContents())->map(function ($entry) {
                return FileEntry::updateOrCreate(['path' => $entry['path']], $entry);
            });
        });
    }

    public function refresh()
    {
        FileEntry::truncate();
        return $this->index();
    }
}
