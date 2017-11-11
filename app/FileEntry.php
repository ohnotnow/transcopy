<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Copyable;
use App\SharedCopyableLogic;
use App\FormattingHelpers;

class FileEntry extends Model implements Copyable
{
    use SharedCopyableLogic;
    use FormattingHelpers;

    protected $guarded = [];

    protected $casts = [
        'was_copied' => 'boolean',
    ];

    protected $diskName = 'files';

    public function isFile()
    {
        return $this->type === 'file';
    }

    public function isDirectory()
    {
        return !$this->isFile();
    }

    public function wasAlreadyCopied()
    {
        return $this->was_copied;
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
}
