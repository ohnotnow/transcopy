<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Filesystem;

class FileController extends Controller
{
    public function update()
    {
        (new Filesystem)->refresh();
        return redirect()->route('home');
    }
}
