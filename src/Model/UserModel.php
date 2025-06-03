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
        return $stmt->fetchColumn() ?: [];
    }

    public function createUser(array $data): bool
    {
        $dbh = Db::getConnection();
        $query = 'INSERT INTO user (first_name, last_name, email, phone, password) VALUES (:first_name, :last_name, :email, :phone, :password)';
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':first_name', $data['first_name']);
        $stmt->bindValue(':last_name', $data['last_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':phone', $data['phone']);
        $stmt->bindValue(':password', $this->hashPassword($data['password']));
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}