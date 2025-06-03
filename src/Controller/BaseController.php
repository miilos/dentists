<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Response\JSONResponse;

abstract class BaseController
{
    protected function json(array $data, int $statusCode = 200): JSONResponse
    {
        return new JSONResponse($data, $statusCode);
    }
}