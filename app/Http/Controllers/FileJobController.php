<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CopyFile;
use App\FileEntry;
use Carbon\Carbon;

class FileJobController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->exists('copies')) {
            return redirect()->back();
        }

        $fileList = [];
        foreach ($request->copies as $fileId) {
            $file = FileEntry::findOrFail($fileId);
            $fileList[] = $file->path;
            CopyFile::dispatch($file);
        }

        return redirect()->route('file.index')->with(['fileList' => implode(', ', $fileList)]);
    }
}
