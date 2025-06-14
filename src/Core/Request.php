<?php

namespace Milos\Dentists\Core;

use Milos\Dentists\Model\UserModel;

class Request
{
    public array $params = [];
    public array $user = [];

    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    public function getPath(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getPostBody(): array
    {
        return json_decode(file_get_contents('php://input'), true);
    }
}