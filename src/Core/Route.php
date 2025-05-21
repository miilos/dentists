<?php

namespace Milos\Dentists\Core;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $path,
        public string $method,
    ) {}
}