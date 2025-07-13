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
            'Signup successful!',
            "
                <h4>Thank you for registering with us!</h4>
        
                <p>
                    To activate your account, please visit
                    <a href=\"https://localhost:8080/dentists/api/account/activate/{$token}\">this link</a>
                </p>
            ",
            'Your signup was successful! Please go to http://localhost:8080/dentists/api/accounts/activate to activate your account'
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
            'message' => 'Login successful!',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    #[Route(path: '/api/logout', method: 'get')]
    public function logout(Request $req): JSONResponse
    {
        SessionManager::remove('user');

        return $this->json([
            'status' => 'success',
            'message' => 'Logged out!'
        ], 204);
    }

    #[Route(path: '/api/users', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['dentist', 'admin'])]
    public function getAllUsers(Request $req): JSONResponse
    {
        $model = new UserModel();
        $users = $model->getAllUsers();

        if (!$users) {
            throw new APIException('No users found!', 404);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'users' => $users
            ]
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

    #[Route(path: '/api/forgotPassword', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    public function forgotPassword(Request $req): JSONResponse
    {
        $resetToken = TokenGenerator::generate(16);

        $model = new UserModel();
        $status = $model->setPasswordResetToken($resetToken, $req->user['id']);

        if (!$status) {
            throw new APIException('Something went wrong while getting you your password reset token!', 500);
        }

        $mailer = new Mailer();
        $mailer->send(
            $req->user['email'],
            $req->user['first_name'],
            'Your password reset token (expires in 5 minutes)',
            'Your password reset token',
            "
                <p>Your password reset token is: </p>
                
                <br>
                <h1>{$resetToken}</h1>
                <br>
                
                <p>This token is only valid for 5 minutes.</p>
            ",
            'Password reset token sent to your email! It\'s only valid for 5 minutes.'
        );

        return $this->json([
            'status' => 'success',
            'message' => 'Your password reset token has been sent to your email.',
            'data' => [
                'resetToken' => $resetToken
            ]
        ]);
    }

    #[Route(path: '/api/resetPassword', method: 'post')]
    public function resetPassword(Request $req): JSONResponse
    {
        $data = $req->getPostBody();

        if (!$data['reset_token'] || !$data['password']) {
            throw new APIException('Email or password reset token not specified!', 400);
        }

        if (strlen($data['password']) < 8) {
            throw new APIException('Password must be at least 8 characters!', 400);
        }

        $model = new UserModel();
        $user = $model->getUserByResetToken($data['reset_token']);

        if (!$user) {
            throw new APIException('Invalid or expired reset token!', 400);
        }

        $status = $model->resetPassword($user, $data['password']);
        if (!$status) {
            throw new APIException('Something went wrong while resetting your password!', 500);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Your password has been reset!',
        ]);
    }

    #[Route(path: '/api/editProfile', method: 'post')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    public function editProfile(Request $req): JSONResponse
    {
        $data = $req->getPostBody();
        $user = $req->user;

        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone']
        ];

        $errors = Validator::validateUserEditData($updateData);
        if ($errors) {
            throw new APIException('Validation error', 400, $errors);
        }

        $model = new UserModel();
        $status = $model->editProfile($updateData, $user['id']);

        if (!$status) {
            throw new APIException('Something went wrong while updating your profile!', 500);
        }

        $updatedUser = $model->getUserByEmail($user['email']);
        SessionManager::set('user', $updatedUser);

        return $this->json([
            'status' => 'success',
            'message' => 'Your profile has successfully been updated!',
        ]);
    }

    #[Route(path: '/api/ban/{userId}', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['admin'])]
    public function banUser(Request $req): JSONResponse
    {
        $userId = $req->params['userId'];

        $model = new UserModel();
        $status = $model->banUser($userId);

        if (!$status) {
            throw new APIException('Something went wrong while banning user!', 500);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'User banned successfully!',
        ]);
    }

    #[Route(path: '/api/unban/{userId}', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['admin'])]
    public function unbanUser(Request $req): JSONResponse
    {
        $userId = $req->params['userId'];

        $model = new UserModel();
        $status = $model->unbanUser($userId);

        if (!$status) {
            throw new APIException('Something went wrong while unbanning user!', 500);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'User unbanned successfully!',
        ]);
    }
}