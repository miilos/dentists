<?php

namespace Milos\Dentists\Core;

class Db
{
    private static ?Db $instance = null;
    private string $host;
    private string $db;
    private string $username;
    private string $password;
    private \PDO $dbh;

    private function __construct()
    {
        $this->host = $_ENV['DB_HOST'];
        $this->db = $_ENV['DB_NAME'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASS'];

        $this->connect();
    }

    private function connect(): void
    {
        $this->dbh = new \PDO("mysql:host=$this->host;dbname=$this->db", $this->username, $this->password);
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public static function getConnection(): \PDO
    {
        if (self::$instance === null) {
            self::$instance = new Db();
        }

        return self::$instance->dbh;
    }
}