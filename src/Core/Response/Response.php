<?php

namespace Milos\Dentists\Core\Response;

abstract class Response
{
    public function __construct(
        private int $statusCode,
    ) {}

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public abstract function send();
}