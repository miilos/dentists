<?php

namespace Milos\Dentists\Model;

use DateTime;
use Milos\Dentists\Core\Db;
use Milos\Dentists\Core\Exception\APIException;
use PDO;

class AppointmentModel
{
    // inserts all the appointment related data and returns the appointment code
    public function createAppointment(array $data): string
    {
        $this->validateAppointment($data['scheduled_at'], $data['duration'], $data['dentist_id']);

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

    public function getAppointmentById(int $appointmentId): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT * FROM appointment WHERE id = :appointmentId";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':appointmentId', $appointmentId);
        $stmt->execute();
        $appointment = $stmt->fetch();

        if (!$appointment) {
            return [];
        }

        return $appointment;
    }

    private function validateAppointment(string $scheduledAt, int $duration, int $dentistId): void
    {
        // check if the appointment is at most a month ahead
        $scheduledAtDate = new DateTime($scheduledAt);
        $now = new DateTime();
        $monthAhead = (new DateTime())->modify('+1 month');

        if (!($scheduledAtDate >= $now && $scheduledAtDate <= $monthAhead)) {
            throw new APIException('You can schedule an appointment at most a month ahead!', 400);
        }

        // check if the appointment overlaps with other appointments
        $overlappingAppointments = $this->getOverlappingAppointments($scheduledAt, $duration, $dentistId);
        if ($overlappingAppointments) {
            throw new APIException('There is already an appointment made at this time!', 400);
        }
    }

    private function getOverlappingAppointments(string $startTime, int $duration, int $dentistId): array
    {
        $endTime = date('Y-m-d H:i:s', strtotime($startTime . " + $duration minutes"));

        $dbh = Db::getConnection();
        $query = "SELECT * FROM appointment WHERE scheduled_at < :endTime AND DATE_ADD(scheduled_at, INTERVAL duration MINUTE) > :startTime AND dentist_id = :dentistId";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':startTime', $startTime);
        $stmt->bindValue(':endTime', $endTime);
        $stmt->bindValue(':dentistId', $dentistId);
        $stmt->execute();
        $overlappingAppointments = $stmt->fetchAll();

        if (!$overlappingAppointments) {
            return [];
        }

        return $overlappingAppointments;
    }
    public function getAllMedicalRecordsForUser(int $userId): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT a.id, a.scheduled_at, a.price, a.duration, a.note,
                     d.first_name AS dentist_first_name, d.last_name AS dentist_last_name
              FROM appointment a
              INNER JOIN dentist d ON a.dentist_id = d.id
              WHERE a.user_id = :userId
              ORDER BY a.scheduled_at DESC";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUniquePatientsForDentist(int $dentistId): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT DISTINCT u.id, u.first_name, u.last_name
              FROM appointment a
              INNER JOIN user u ON a.user_id = u.id
              WHERE a.dentist_id = :dentistId";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':dentistId', $dentistId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getActiveAppointmentsForUser(int $userId): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT a.id AS appointment_id,a.user_id, a.dentist_id, a.scheduled_at, a.price, a.duration, ac.code, d.id AS dentist_id, d.first_name, d.last_name, d.email, d.photo
    FROM appointment a
    INNER JOIN appointment_codes ac ON a.id = ac.appointment_id
    INNER JOIN dentist d ON a.dentist_id = d.id
    WHERE a.user_id = :userId AND a.scheduled_at > NOW()";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activeAppointments = [];
        foreach ($res as $appointment) {
            $servicesQuery = "SELECT * FROM appointment_service app_sr INNER JOIN service s ON app_sr.service_id = s.id WHERE app_sr.appointment_id = :appointmentId";
            $servicesStmt = $dbh->prepare($servicesQuery);
            $servicesStmt->bindValue(':appointmentId', $appointment['appointment_id']);
            $servicesStmt->execute();
            $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

            $activeAppointments[] = [
                'id' => $appointment['appointment_id'],
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
                ],
                'services' => $services
            ];

        }

        return $activeAppointments;
    }

    public function getAllAppointmentsForUser(int $userId): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT a.id, a.user_id, a.dentist_id, a.scheduled_at, a.price, a.duration, ac.code, d.first_name, d.last_name, d.email, d.photo
                    FROM appointment a INNER JOIN appointment_codes ac
                    ON a.id = ac.appointment_id
                    INNER JOIN dentist d
                    ON a.dentist_id = d.id
                    WHERE a.user_id = :userId";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $appointments = [];
        foreach ($res as $appointment) {
            $servicesQuery = "SELECT * FROM appointment_service app_sr INNER JOIN service s ON app_sr.service_id = s.id WHERE app_sr.appointment_id = :appointmentId";
            $servicesStmt = $dbh->prepare($servicesQuery);
            $servicesStmt->bindValue(':appointmentId', $appointment['id']);
            $servicesStmt->execute();
            $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

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
                ],
                'services' => $services
            ];
        }

        return $appointments;
    }

    public function getAllAppointmentsForDentist(int $id): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT a.id AS appointment_id, a.dentist_id, a.scheduled_at, a.price, a.duration, a.note, 
                     u.id AS user_id, u.first_name, u.last_name
              FROM appointment a
              INNER JOIN user u ON a.user_id = u.id
              WHERE a.dentist_id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $appointments = [];
        foreach ($data as $appointment) {
            $appointments[] = [
                'id' => $appointment['appointment_id'],
                'dentist_id' => $appointment['dentist_id'],
                'scheduled_at' => $appointment['scheduled_at'],
                'price' => $appointment['price'],
                'duration' => $appointment['duration'],
                'note' => $appointment['note'],
                'user' => [
                    'id' => $appointment['user_id'],
                    'first_name' => $appointment['first_name'],
                    'last_name' => $appointment['last_name']
                ]
            ];
        }

        return $appointments ?: [];
    }

    public function getAppointmentByCode(int $userId, string $appointmentCode): array
    {
        $dbh = Db::getConnection();
        $query = "SELECT ac.code, a.id, a.scheduled_at FROM appointment_codes ac INNER JOIN appointment a ON ac.appointment_id = a.id WHERE ac.code = :code AND ac.user_id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':code', $appointmentCode);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$appointment) {
            return [];
        }

        return $appointment;
    }

    public function cancelAppointment(int $appointmentId): bool
    {
        $dbh = Db::getConnection();
        $deleteCodeQuery = "DELETE FROM appointment_codes WHERE appointment_id = :id";
        $stmt = $dbh->prepare($deleteCodeQuery);
        $stmt->bindValue(':id', $appointmentId);
        $stmt->execute();

        $deleteServicesQuery = "DELETE FROM appointment_service WHERE appointment_id = :id";
        $stmt = $dbh->prepare($deleteServicesQuery);
        $stmt->bindValue(':id', $appointmentId);
        $stmt->execute();

        $deleteAppointmentQuery = "DELETE FROM appointment WHERE id = :id";
        $stmt = $dbh->prepare($deleteAppointmentQuery);
        $stmt->bindValue(':id', $appointmentId);
        $stmt->execute();

        $status = $stmt->rowCount();
        return $status > 0;
    }

    public function editAppointmentTime(string $newTime, int $appointmentId): bool
    {
        $dbh = Db::getConnection();
        $query = "UPDATE appointment SET scheduled_at = :newTime WHERE id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':newTime', $newTime);
        $stmt->bindValue(':id', $appointmentId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function editAppointmentServices(array $data, int $appointmentId): bool
    {
        $dbh = Db::getConnection();
        $deleteExistingServicesQuery = "DELETE FROM appointment_service WHERE appointment_id = :id";
        $stmt = $dbh->prepare($deleteExistingServicesQuery);
        $stmt->bindValue(':id', $appointmentId);
        $stmt->execute();

        $duration = 0;
        $price = 0;
        foreach ($data as $serviceId) {
            $addNewServicesQuery = "INSERT INTO appointment_service (appointment_id, service_id) VALUES (:appointmentId, :serviceId)";
            $stmt = $dbh->prepare($addNewServicesQuery);
            $stmt->bindValue(':appointmentId', $appointmentId);
            $stmt->bindValue(':serviceId', $serviceId);
            $stmt->execute();

            $selectPriceAndDurationQuery = "SELECT price, duration FROM service WHERE id = :id LIMIT 1";
            $stmt = $dbh->prepare($selectPriceAndDurationQuery);
            $stmt->bindValue(':id', $serviceId);
            $stmt->execute();
            $serviceData = $stmt->fetch(\PDO::FETCH_ASSOC);
            $price += (int) $serviceData['price'];
            $duration += (int) $serviceData['duration'];
        }

        $updateServiceQuery = "UPDATE appointment SET price = :price, duration = :duration WHERE id = :id";
        $stmt = $dbh->prepare($updateServiceQuery);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':duration', $duration);
        $stmt->bindValue(':id', $appointmentId);
        $stmt->execute();

        return true;
    }

    public function addNoteToAppointment(int $appointmentId, string $note): bool
    {
        $dbh = Db::getConnection();
        $query = "UPDATE appointment SET note = :note WHERE id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':note', $note);
        $stmt->bindValue(':id', $appointmentId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function userMissedAppointment(int $userId): bool
    {
        $dbh = Db::getConnection();
        $query = "UPDATE user SET num_missed_appointments = num_missed_appointments + 1 WHERE id = :id";
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();
        $status = $stmt->rowCount() > 0;

        $banQuery = "UPDATE user SET is_banned = 1 WHERE id = :id AND num_missed_appointments >= 3";
        $stmt = $dbh->prepare($banQuery);
        $stmt->bindValue(':id', $userId);
        $stmt->execute();

        return $status;
    }
    public function getAllAppointmentsForPatientsOfDentist(int $dentistId): array
    {
        $dbh = Db::getConnection();

        $queryPatients = "SELECT DISTINCT user_id FROM appointment WHERE dentist_id = :dentistId";
        $stmtPatients = $dbh->prepare($queryPatients);
        $stmtPatients->bindValue(':dentistId', $dentistId);
        $stmtPatients->execute();
        $patientIds = $stmtPatients->fetchAll(PDO::FETCH_COLUMN);

        if (empty($patientIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($patientIds), '?'));
        $queryAppointments = "
        SELECT 
            a.*, 
            d.first_name AS dentist_first_name, d.last_name AS dentist_last_name,
            u.first_name AS user_first_name, u.last_name AS user_last_name
        FROM appointment a
        INNER JOIN dentist d ON a.dentist_id = d.id
        INNER JOIN user u ON a.user_id = u.id
        WHERE a.user_id IN ($placeholders)
        ORDER BY u.last_name, a.scheduled_at
    ";
        $stmtAppointments = $dbh->prepare($queryAppointments);
        foreach ($patientIds as $k => $patientId) {
            $stmtAppointments->bindValue($k+1, $patientId);
        }
        $stmtAppointments->execute();
        $appointments = $stmtAppointments->fetchAll(PDO::FETCH_ASSOC);

        foreach ($appointments as &$app) {
            $servicesQuery = "SELECT s.name FROM appointment_service aps INNER JOIN service s ON aps.service_id = s.id WHERE aps.appointment_id = :appointmentId";
            $servicesStmt = $dbh->prepare($servicesQuery);
            $servicesStmt->bindValue(':appointmentId', $app['id']);
            $servicesStmt->execute();
            $services = $servicesStmt->fetchAll(PDO::FETCH_COLUMN);
            $app['services'] = $services;
        }

        return $appointments;
    }

}