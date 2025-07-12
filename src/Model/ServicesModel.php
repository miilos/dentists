<?php

namespace Milos\Dentists\Model;

use Milos\Dentists\Core\Db;

class ServicesModel
{
    public function getAllServices(): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT * FROM service";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        $services = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$services) {
            return [];
        }

        return $services;
    }

    public function getServiceById($id): array
    {
        $dbh = Db::getConnection();
        $stmt = $dbh->prepare("SELECT * FROM service WHERE id = :id LIMIT 1");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $service = $stmt->fetch();

        if (!$service) {
            return [];
        }

        return $service;
    }

    public function editService(int $serviceId, array $data): bool
    {
        $dbh = Db::getConnection();
        $query = "UPDATE service SET name = :name, duration = :duration, price = :price WHERE id = :service_id LIMIT 1";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':duration', $data['duration']);
        $stmt->bindValue(':price', $data['price']);
        $stmt->bindValue(':service_id', $serviceId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}