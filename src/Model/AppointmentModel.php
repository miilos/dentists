<?php

namespace Milos\Dentists\Model;

use Milos\Dentists\Core\Db;
use Milos\Dentists\Core\Exception\APIException;

class AppointmentModel
{
    // inserts all the appointment related data and returns the appointment code
    public function createAppointment(array $data): string
    {
        $this->validateAppointment($data['scheduled_at'], $data['duration']);

        // create the main appointment record in the db
        $dbh = Db::getConnection();
        $query = "INSERT INTO appointment (user_id, dentist_id, scheduled_at, price, duration) VALUES (:user_id, :dentist_id, :scheduled_at, :price, :duration)";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':dentist_id', $data['dentist_id']);
        $stmt->bindValue(':scheduled_at', $data['scheduled_at']);
        $stmt->bindValue(':price', $data['total']);
        $stmt->bindValue(':duration', $data['duration']);
        $stmt->execute();
        $status = $stmt->rowCount();

        if ($status == 0) {
            throw new APIException('Something went wrong with booking your appointment!', 400);
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
                throw new APIException('Something went wrong with booking your appointment!', 400);
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
            throw new APIException('Something went wrong with booking your appointment!', 400);
        }

        return $appointmentCode;
    }

    private function validateAppointment(string $scheduledAt, int $duration): void
    {
        // check if the appointment is at most a month ahead
        $scheduledAtDate = new \DateTime($scheduledAt);
        $now = new \DateTime();
        $monthAhead = (new \DateTime())->modify('+1 month');

        if (!($scheduledAtDate >= $now && $scheduledAtDate <= $monthAhead)) {
            throw new APIException('You can schedule an appointment at most a month ahead!', 400);
        }

        // check if the appointment overlaps with other appointments
        $overlappingAppointments = $this->getOverlappingAppointments($scheduledAt, $duration);
        if ($overlappingAppointments) {
            throw new APIException('There is already an appointment made at this time!', 400);
        }
    }

    private function getOverlappingAppointments(string $startTime, int $duration): array
    {
        $endTime = date('Y-m-d H:i:s', strtotime($startTime . " + $duration minutes"));

        $dbh = Db::getConnection();
        $query = "SELECT * FROM appointment WHERE scheduled_at < :endTime AND DATE_ADD(scheduled_at, INTERVAL duration MINUTE) > :startTime";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':startTime', $startTime);
        $stmt->bindValue(':endTime', $endTime);
        $stmt->execute();
        $overlappingAppointments = $stmt->fetchAll();

        if (!$overlappingAppointments) {
            return [];
        }

        return $overlappingAppointments;
    }

    public function getActiveAppointmentsForUser(int $userId): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT a.id, a.user_id, a.dentist_id, a.scheduled_at, a.price, a.duration, ac.code, d.id, d.first_name, d.last_name, d.email, d.photo
                    FROM appointment a INNER JOIN appointment_codes ac
                    ON a.id = ac.appointment_id
                    INNER JOIN dentist d
                    ON a.dentist_id = d.id
                    WHERE a.user_id = :userId AND a.scheduled_at > NOW()";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $activeAppointments = [];
        foreach ($res as $appointment) {
            $activeAppointments[] = [
                'id' => $appointment['id'],
                'user_id' => $appointment['user_id'],
                'scheduled_at' => $appointment['scheduled_at'],
                'price' => $appointment['price'],
                'duration' => $appointment['duration'],
                'code' => $appointment['code'],
                'dentist' => [
                    'id' => $appointment['dentist_id'],
                    'first_name' => $appointment['first_name'],
                    'last_name' => $appointment['last_name'],
                    'email' => $appointment['email'],
                    'photo' => $appointment['photo']
                ]
            ];
        }

        return $activeAppointments;
    }

    public function getAllAppointmentsForUser(int $userId): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT a.id, a.user_id, a.dentist_id, a.scheduled_at, a.price, a.duration, ac.code, d.id, d.first_name, d.last_name, d.email, d.photo
                    FROM appointment a INNER JOIN appointment_codes ac
                    ON a.id = ac.appointment_id
                    INNER JOIN dentist d
                    ON a.dentist_id = d.id
                    WHERE a.user_id = :userId";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $appointments = [];
        foreach ($res as $appointment) {
            $appointments[] = [
                'id' => $appointment['id'],
                'user_id' => $appointment['user_id'],
                'scheduled_at' => $appointment['scheduled_at'],
                'price' => $appointment['price'],
                'duration' => $appointment['duration'],
                'code' => $appointment['code'],
                'dentist' => [
                    'id' => $appointment['dentist_id'],
                    'first_name' => $appointment['first_name'],
                    'last_name' => $appointment['last_name'],
                    'email' => $appointment['email'],
                    'photo' => $appointment['photo']
                ]
            ];
        }

        return $appointments;
    }
}