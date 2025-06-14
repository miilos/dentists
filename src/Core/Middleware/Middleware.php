<?php

namespace Milos\Dentists\Core\Middleware;

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Middleware
{
    public function __construct(
        public array $function,
        public array $args = []
    ) {}
}