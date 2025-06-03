<?php

namespace Milos\Dentists\Core\Exception;

class APIException extends \Exception
{
    public int $statusCode;
    public mixed $extraInfo;

    public function __construct(string $message, int $statusCode, mixed $extraInfo = null)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->extraInfo = $extraInfo;
    }
}