<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Route;
use Milos\Dentists\Core\Request;

class TestController
{
    #[Route(path: '/api/test', method: 'get')]
    public function test(Request $req): string
    {
        return 'radi jeejjj';
    }
}