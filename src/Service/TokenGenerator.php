<?php

namespace Milos\Dentists\Service;

class TokenGenerator
{
    public static function generate(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}