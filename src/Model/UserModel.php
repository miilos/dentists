<?php

namespace Milos\Dentists\Model;

use Milos\Dentists\Core\Db;

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
}