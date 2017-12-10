<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\Copyable;
use App\SharedCopyableLogic;
use App\FormattingHelpers;

class TorrentEntry extends Model implements Copyable
{
    use SharedCopyableLogic;
    use FormattingHelpers;

    protected $guarded = [];

    protected $casts = [
        'was_copied' => 'boolean',
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
        return $this->percent < 1.0;
    }

    public function isComplete()
    {
        return ! $this->isStillDownloading();
    }

    public function wasAlreadyCopied()
    {
        return $this->was_copied;
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
