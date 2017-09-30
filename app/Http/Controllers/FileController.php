<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Filesystem;
use App\FileEntry;

class FileController extends Controller
{
    public function index()
    {
        $files = FileEntry::orderByDesc('timestamp')->get();
        return view('files.index', compact('files'));
    }

    public function update()
    {
        (new Filesystem)->refresh();
        return redirect()->route('file.index');
    }
}
