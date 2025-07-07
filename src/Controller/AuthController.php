<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Exception\APIException;
use Milos\Dentists\Core\Middleware\AuthMiddleware;
use Milos\Dentists\Core\Middleware\Middleware;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Core\Response\JSONResponse;
use Milos\Dentists\Core\Route;
use Milos\Dentists\Model\UserModel;
use Milos\Dentists\Service\Mailer;
use Milos\Dentists\Service\SessionManager;
use Milos\Dentists\Service\TokenGenerator;
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

        $token = TokenGenerator::generate();
        $data['activation_token'] = $token;

        $status = $model->createUser($data);
        if (!$status) {
            throw new APIException('Something went wrong while creating your account', 500);
        }

        $mailer = new Mailer();
        $mailer->send(
            $data['email'],
            $data['first_name'],
            'Welcome to dentists!',
            "
                <h4>Thank you for registering with us!</h4>
        
                <p>
                    To activate your account, please visit
                    <a href=\"https://localhost:8000/api/account/activate/{$token}\">this link</a>
                </p>
        
                <p>Sincerely, <br>the dentists team</p>
            ",
            'Your signup was successful! Please go to http://localhost:8000/api/accounts/activate to activate your account'
        );

        return $this->json([
            'status' => 'success',
            'message' => 'Signup successful! An activation email has been sent to your account.'
        ]);
    }

    #[Route(path: '/api/account/activate/{token}', method: 'get')]
    public function activateAccount(Request $req): JSONResponse
    {
        $activationToken = $req->params['token'];

        $model = new UserModel();
        $status = $model->activateUser($activationToken);

        if (!$status) {
            throw new APIException('Invalid activation token!', 400);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Account activated!'
        ]);
    }

    #[Route(path: '/api/login', method: 'post')]
    public function login(Request $req): JSONResponse
    {
        $data = $req->getPostBody();

        if (!$data['email'] || !$data['password']) {
            throw new APIException('Missing email or password!', 400);
        }

        $model = new UserModel();
        $user = $model->getUserByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            throw new APIException('Incorrect email or password!', 400);
        }

        if ($user['is_banned']) {
            throw new APIException('You are banned!', 400);
        }

        unset($user['password']);
        SessionManager::set('user', $user);

        return $this->json([
            'status' => 'success',
            'message' => 'Login successful!'
        ]);
    }

    #[Route(path: '/api/me', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    public function getUser(Request $req): JSONResponse
    {
        $user = $req->user;

        if (!$user) {
            throw new APIException('User not found!', 400);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'user' => $user
            ]
        ]);
    }
}