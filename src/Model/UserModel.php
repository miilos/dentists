<?php

namespace Milos\Dentists\Model;

use Milos\Dentists\Core\Db;
use Milos\Dentists\Core\Exception\APIException;

class UserModel
{
    public function getEmails(): array
    {
        $dbh = Db::getConnection();
        $query = 'SELECT email FROM user';
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    public function createUser(array $data): bool
    {
        $dbh = Db::getConnection();
        $query = 'INSERT INTO user (first_name, last_name, email, phone, password, activation_token, activation_token_expires_at) 
                    VALUES (:first_name, :last_name, :email, :phone, :password, :token, NOW() + INTERVAL 1 HOUR)';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':first_name', $data['first_name']);
        $stmt->bindValue(':last_name', $data['last_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':phone', $data['phone']);
        $stmt->bindValue(':password', $this->hashPassword($data['password']));
        $stmt->bindValue(':token', $data['activation_token']);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function activateUser(string $activationToken): bool
    {
        $dbh = Db::getConnection();
        $query = 'UPDATE user SET is_active = 1, activation_token = NULL WHERE activation_token = :activation_token AND activation_token_expires_at > NOW()';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':activation_token', $activationToken);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getUserByEmail(string $email): array
    {
        $dbh = Db::getConnection();
        $query = 'SELECT id, first_name, last_name, email, phone, password, is_active, is_banned, role FROM user WHERE email = :email LIMIT 1 ';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            // if the user trying to log in is a dentist, get their data from the dentists table
            $getFromDentists = "SELECT id, first_name, last_name, email, photo, role, password FROM dentist WHERE email = :email LIMIT 1";
            $stmt = $dbh->prepare($getFromDentists);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            $dentist = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$dentist) {
                return [];
            }

            return $dentist;
        }

        return $user;
    }

    public function setPasswordResetToken(string $resetToken, int $id): bool
    {
        $dbh = Db::getConnection();
        $query = "UPDATE user SET password_reset_token = :reset_token, password_reset_token_expires_at = NOW() + INTERVAL 5 MINUTE WHERE id = :id LIMIT 1";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':reset_token', $resetToken);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getUserByResetToken(string $resetToken): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT * FROM user WHERE password_reset_token = :reset_token AND password_reset_token_expires_at > NOW() LIMIT 1";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':reset_token', $resetToken);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return [];
        }

        return $user;
    }

    public function resetPassword(array $user, string $password): bool
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $dbh = Db::getConnection();
        $query = "UPDATE user SET password = :password, password_reset_token = NULL, password_reset_token_expires_at = NULL WHERE id = :id LIMIT 1";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':password', $hash);
        $stmt->bindValue(':id', $user['id']);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function editProfile(array $data, int $userId): bool
    {
        $dbh = Db::getConnection();
        $query = "UPDATE user SET first_name = :fist_name, last_name = :last_name, phone = :phone WHERE id = :id LIMIT 1";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':fist_name', $data['first_name']);
        $stmt->bindValue(':last_name', $data['last_name']);
        $stmt->bindValue(':phone', $data['phone']);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}