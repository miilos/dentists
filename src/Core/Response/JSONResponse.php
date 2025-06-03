<?php

namespace Milos\Dentists\Core\Response;

class JSONResponse extends Response
{
    private mixed $data;

    public function __construct(mixed $data, int $statusCode)
    {
        parent::__construct($statusCode);
        $this->data = $data;
    }

    public function send(): string
    {
        http_response_code($this->getStatusCode());
        header('Content-Type: application/json');
        return json_encode($this->data);
    }
}