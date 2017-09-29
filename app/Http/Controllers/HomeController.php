<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Filesystem;
use App\FileEntry;

class HomeController extends Controller
{
    public function index()
    {
        $files = (new Filesystem)->index();
        return view('home', compact('files'));
    }
}
