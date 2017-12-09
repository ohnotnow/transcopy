<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\TorrentEntry;

class TorrentEntryResource extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'torrent_id' => $this->torrent_id,
            'name' => $this->webFriendlyName(),
            'eta' => $this->formattedEta(),
            'size' => $this->formattedSize(),
            'copied' => $this->wasAlreadyCopied(),
            'incomplete' => $this->isStillDownloading(),
        ];
    }
}
