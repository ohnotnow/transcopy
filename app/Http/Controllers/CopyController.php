<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\CopyFile;
use App\FileEntry;
use Carbon\Carbon;

class CopyController extends Controller
{
    public function store(Request $request)
    {
        if (!$request->exists('copies')) {
            return redirect()->route('home');
        }

        foreach ($request->copies as $fileId) {
            CopyFile::dispatch(FileEntry::findOrFail($fileId))->delay(Carbon::now()->addMinutes(1));
        }

        return redirect()->route('home');
    }
}
