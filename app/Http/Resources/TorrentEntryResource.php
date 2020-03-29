<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TorrentEntryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->webFriendlyName(),
            'eta' => $this->formattedEta(),
            'size' => $this->formattedSize(),
            'copied' => $this->wasAlreadyCopied(),
            'percent' => number_format($this->percent, 2),
            'incomplete' => $this->isStillDownloading(),
            'copying' => $this->isCopying(),
            'copy_failed' => $this->copy_failed,
            'should_copy' => $this->should_copy,
            'copy_start' => $this->copy_started,
            'copy_end' => $this->copy_ended,
            'is_selected' => false,
        ];
    }
}
