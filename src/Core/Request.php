<?php

namespace Milos\Dentists\Core;

class Request
{
    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getPath(): string
    {
        return $_SERVER['REQUEST_URI'];
    }
}