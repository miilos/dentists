<?php

namespace Milos\Dentists\Model;

use Milos\Dentists\Core\Db;

class DentistModel
{
    public function getAllDentists(): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT * FROM dentist ORDER BY first_name, last_name";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        $dentists = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $res = [];
        foreach ($dentists as $dentist) {
            $services = [];

            $query = "SELECT s.id, s.name, s.duration, s.price FROM service s INNER JOIN dentist_service ds ON s.id = ds.service_id WHERE dentist_id = :dentist_id";
            $stmt = $dbh->prepare($query);
            $stmt->bindValue(':dentist_id', $dentist['id']);
            $stmt->execute();

            $services = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $dentist['services'] = $services;

            $res[] = $dentist;
        }

        return $res;
    }

    public function getDentistById(int $id): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT * FROM dentist WHERE id = :id LIMIT 1";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $dentist = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$dentist) {
            return [];
        }

        $servicesQuery = "SELECT s.id, s.name, s.duration, s.price FROM service s INNER JOIN dentist_service ds ON s.id = ds.service_id WHERE dentist_id = :dentist_id";
        $servicesStmt = $dbh->prepare($servicesQuery);
        $servicesStmt->bindValue(':dentist_id', $id);
        $servicesStmt->execute();
        $services = $servicesStmt->fetchAll(\PDO::FETCH_ASSOC);

        $dentist['services'] = $services;
        return $dentist;
    }
}