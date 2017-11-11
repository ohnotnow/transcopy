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
        if ($this->isStillDownloading()) {
            return intval($this->eta / 60) . 'min';
        }
        return 'Done';
    }

    public function isStillDownloading()
    {
        return $this->eta >= 0;
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
