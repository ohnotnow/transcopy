<?php

namespace App\Contracts;

interface TorrentContract
{
    public function index();

    public function update($id);
}
