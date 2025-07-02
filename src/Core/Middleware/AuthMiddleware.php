<?php

namespace Milos\Dentists\Core\Middleware;

use Milos\Dentists\Core\Exception\APIException;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Service\SessionManager;

class AuthMiddleware
{
    public function authenticate(Request $req, array $args): void
    {
        $user = SessionManager::get('user');

        if (!$user) {
            throw new APIException('You need to be logged in to access this route!', 403);
        }

        $req->user = $user;
    }

    public function authorize(Request $req, array $args): void
    {
        if (!in_array($req->user['role'], $args)) {
            throw new APIException('You do not have permission to access this route!', 403);
        }
    }
}