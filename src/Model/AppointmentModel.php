<?php

namespace Milos\Dentists\Model;

use Milos\Dentists\Core\Db;
use Milos\Dentists\Core\Exception\APIException;

class AppointmentModel
{
    // inserts all the appointment related data and returns the appointment code
    public function createAppointment(array $data): string
    {
        // create the main appointment record in the db
        $dbh = Db::getConnection();
        $query = "INSERT INTO appointment (user_id, dentist_id, price, duration) VALUES (:user_id, :dentist_id, :price, :duration)";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':dentist_id', $data['dentist_id']);
        $stmt->bindValue(':price', $data['total']);
        $stmt->bindValue(':duration', $data['duration']);
        $stmt->execute();
        $status = $stmt->rowCount();

        if ($status == 0) {
            throw new APIException('Something went wrong with booking your appointment!');
        }

        $appId = $dbh->lastInsertId();

        // create the appointment_service link records
        $query = "INSERT INTO appointment_service (appointment_id, service_id) VALUES (:appointment_id, :service_id)";
        $stmt = $dbh->prepare($query);

        $stmt->bindValue(':appointment_id', $appId);
        foreach ($data['services'] as $serviceId) {
            $stmt->bindValue(':service_id', $serviceId);
            $stmt->execute();
            $serviceWriteStatus = $stmt->rowCount();

            if ($serviceWriteStatus == 0) {
                throw new APIException('Something went wrong with booking your appointment!');
            }
        }

        // create appointment code and appointment code record in db
        $appointmentCode = bin2hex(random_bytes(8));

        $insertCodeQuery = "INSERT INTO appointment_codes (user_id, appointment_id, code) VALUES (:user_id, :appointment_id, :code)";
        $stmt = $dbh->prepare($insertCodeQuery);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':appointment_id', $appId);
        $stmt->bindValue(':code', $appointmentCode);
        $stmt->execute();
        $codeInsertStatus = $stmt->rowCount();

        if ($codeInsertStatus == 0) {
            throw new APIException('Something went wrong with booking your appointment!');
        }

        return $appointmentCode;
    }
}