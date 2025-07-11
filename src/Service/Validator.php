<?php

namespace Milos\Dentists\Service;

use Milos\Dentists\Model\UserModel;

class Validator
{
    public static function validateUser(array $data): array
    {
        $errors = [];

        if (!$data['first_name']) {
            $errors['first_name'][] = 'First name is required';
        }

        if (!$data['last_name']) {
            $errors['last_name'][] = 'Last name is required';
        }

        if (!$data['email']) {
            $errors['email'][] = 'Email is required';
        }

        $model = new UserModel();
        if (in_array($data['email'], $model->getEmails())) {
            $errors['email'][] = 'This email is already taken';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Not a valid email format';
        }

        if (!$data['phone']) {
            $errors['phone'][] = 'Phone number is required';
        }

        if (strlen($data['password']) < 8) {
            $errors['password'][] = 'Your password must be at least 8 characters long';
        }

        if (!$data['password']) {
            $errors['password'][] = 'You have to set a password';
        }

        if ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'][] = 'This field has to match password';
        }

        return $errors;
    }

    public static function validateUserEditData(array $data): array
    {
        $errors = [];

        if (!$data['first_name']) {
            $errors['first_name'][] = 'First name is required';
        }

        if (!$data['last_name']) {
            $errors['last_name'][] = 'Last name is required';
        }

        if (!$data['phone']) {
            $errors['phone'][] = 'Phone number is required';
        }

        return $errors;
    }
}