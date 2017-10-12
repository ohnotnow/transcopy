<?php

namespace App;

trait FormattingHelpers
{
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
}