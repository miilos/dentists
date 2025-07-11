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

            $specialization = null;
            $specializationQuery = "SELECT s.name FROM dentist d INNER JOIN dentist_specialization ds ON d.id = ds.dentist_id INNER JOIN specialization s ON ds.specialization_id = s.id WHERE d.id = :dentist_id";
            $stmt = $dbh->prepare($specializationQuery);
            $stmt->bindValue(':dentist_id', $dentist['id']);
            $stmt->execute();

            $specialization = $stmt->fetch(\PDO::FETCH_ASSOC);
            $dentist['specialization'] = $specialization['name'];

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

        $specialization = null;
        $specializationQuery = "SELECT s.name FROM dentist d INNER JOIN dentist_specialization ds ON d.id = ds.dentist_id INNER JOIN specialization s ON ds.specialization_id = s.id WHERE d.id = :dentist_id";
        $stmt = $dbh->prepare($specializationQuery);
        $stmt->bindValue(':dentist_id', $dentist['id']);
        $stmt->execute();

        $specialization = $stmt->fetch(\PDO::FETCH_ASSOC);
        $dentist['specialization'] = $specialization['name'];

        return $dentist;
    }

    public function editDentist(int $dentistId, array $data): bool
    {
        $dbh = Db::getConnection();
        $query = "UPDATE dentist SET first_name = :first_name, last_name = :last_name, photo = :photo WHERE id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':first_name', $data['first_name']);
        $stmt->bindValue(':last_name', $data['last_name']);
        $stmt->bindValue(':photo', $data['photo']);
        $stmt->bindValue(':id', $dentistId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function deleteDentist(int $dentistId): bool
    {
        $dbh = Db::getConnection();
        $query = "DELETE FROM dentist WHERE id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':id', $dentistId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}