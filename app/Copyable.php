<?php

namespace App;

interface Copyable
{
    public function isFile();

    public function isDirectory();

    public function getPath();

    public function getFullPath();

    public function getBasename();

    public function source();

    public function exists();
}
