<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Exception\APIException;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Core\Response\JSONResponse;
use Milos\Dentists\Core\Route;
use Milos\Dentists\Model\UserModel;
use Milos\Dentists\Service\Validator;

class AuthController extends BaseController
{
    #[Route(path: '/api/signup', method: 'post')]
    public function signup(Request $req): JSONResponse
    {
        $data = $req->getPostBody();
        $model = new UserModel();

        $errors = Validator::validateUser($data);
        if ($errors) {
            throw new APIException('Validation error', 400, $errors);
        }

        $status = $model->createUser($data);

        if (!$status) {
            throw new APIException('Something went wrong while creating your account', 500);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Signup successful! An activation email has been sent to your account.'
        ]);
    }
}