<?php

namespace Milos\Dentists\Core;

/**
 *  attribute that represents an API route
 *  attributes on all controller functions are read using the Reflection API
 *  in Router.php, and they are registered in the router's $routes array
 */

#[\Attribute(\Attribute::TARGET_METHOD|\Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $path,
        public string $method,
    ) {}
}