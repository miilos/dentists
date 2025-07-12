<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Exception\APIException;
use Milos\Dentists\Core\Middleware\AuthMiddleware;
use Milos\Dentists\Core\Middleware\Middleware;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Core\Response\JSONResponse;
use Milos\Dentists\Core\Route;
use Milos\Dentists\Model\AppointmentModel;
use Milos\Dentists\Service\Mailer;

class AppointmentController extends BaseController
{
    #[Route(path: '/api/appointments', method: 'post')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['user'])]
    public function createAppointment(Request $req): JsonResponse
    {
        $data = $req->getPostBody();

        $data['user_id'] = $req->user['id'];

        $model = new AppointmentModel();
        $appointmentCode = $model->createAppointment($data);

        $mailer = new Mailer();
        $mailer->send(
            $req->user['email'],
            $req->user['first_name'],
            "Appointment booked!",
            "Your appointment was successfully booked!",
            "
                <p>
                    Your appointment on {$data['scheduled_at']} was successfully booked. Your appointment code is:
                    <br>
                    <h1>{$appointmentCode}</h1>
                </p>
        
                <p>Sincerely, <br>the dentists team</p>
            ",
            "
                    Your appointment on {$data['scheduled_at']} was successfully booked. Your appointment code is:
                    {$appointmentCode}
        
                    Sincerely, the dentists team
            "
        );

        return $this->json([
            'status' => 'success',
            'message' => 'Appointment booked successfully!',
            'data' => [
                'appointmentCode' => $appointmentCode
            ]
        ]);
    }

    #[Route(path: '/api/appointments/active', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['user'])]
    public function getActiveAppointmentsForUser(Request $req): JsonResponse
    {
        $userId = $req->user['id'];

        $model = new AppointmentModel();
        $activeAppointments = $model->getActiveAppointmentsForUser($userId);

        return $this->json([
            'status' => 'success',
            'data' => [
                'appointments' => $activeAppointments
            ]
        ]);
    }

    #[Route(path: '/api/appointments', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['user', 'dentist'])]
    public function getAllAppointmentsForUser(Request $req): JsonResponse
    {
        $userId = $req->user['id'];

        $model = new AppointmentModel();
        $appointments = $model->getAllAppointmentsForUser($userId);

        return $this->json([
            'status' => 'success',
            'data' => [
                'appointments' => $appointments
            ]
        ]);
    }

    #[Route(path: '/api/appointments/dentist/{id}', method: 'get')]
    public function getAllAppointmentsForDentist(Request $req): JsonResponse
    {
        $id = $req->params['id'];

        $model = new AppointmentModel();
        $appointments = $model->getAllAppointmentsForDentist($id);

        return $this->json([
            'status' => 'success',
            'data' => [
                'appointments' => $appointments
            ]
        ]);
    }

    #[Route(path: '/api/appointments/{code}/cancel', method: 'delete')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['user'])]
    public function cancelAppointment(Request $req): JsonResponse
    {
        $code = $req->params['code'];

        $model = new AppointmentModel();
        $appointment = $model->getAppointmentByCode($req->user['id'], $code);

        if (!$appointment) {
            throw new APIException("No appointment found for code '{$code}'!", 400);
        }

        $now = new \DateTimeImmutable();
        $fourHoursFromNow = $now->add(new \DateInterval('PT4H'));
        $appointmentScheduledAt = new \DateTime($appointment['scheduled_at']);

        if ($fourHoursFromNow > $appointmentScheduledAt) {
            throw new APIException('You can\'t cancel your appointment less than 4 hours before it starts!', 400);
        }

        $status = $model->cancelAppointment($appointment['id']);
        if (!$status) {
            throw new APIException("Something went wrong with cancelling your appointment!", 400);
        }

        return $this->json([
           'status' => 'success',
           'message' => 'Appointment cancelled successfully!'
        ]);
    }

    #[Route(path: '/api/appointments/{id}/editTime', method: 'post')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['dentist'])]
    public function editAppointmentTime(Request $req): JsonResponse
    {
        $newDate = $req->getPostBody()['newDate'];
        $appointmentId = $req->params['id'];
        $dentist = $req->user;

        $model = new AppointmentModel();
        $appointment = $model->getAppointmentById($appointmentId);

        if (!$appointment) {
            throw new APIException('No appointment with that id found!', 400);
        }

        if ($appointment['dentist_id'] !== $dentist['id']) {
            throw new APIException('You can\'t edit this appointment!', 403);
        }

        $status = $model->editAppointmentTime($newDate, $appointment['id']);
        if (!$status) {
            throw new APIException("Something went wrong with editing your appointment time!", 400);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Appointment time edited successfully!'
        ]);
    }

    #[Route(path: '/api/appointments/{id}/services', method: 'post')]
    public function editAppointmentServices(Request $req): JsonResponse
    {
        $data = $req->getPostBody();
        $id = $req->params['id'];

        $model = new AppointmentModel();
        $model->editAppointmentServices($data['service_ids'], $id);

        return $this->json([
           'status' => 'success',
           'message' => 'Appointment services edited successfully!'
        ]);
    }

    #[Route(path: '/api/appointments/{id}/delete', method: 'delete')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['dentist'])]
    public function deleteAppointment(Request $req): JsonResponse
    {
        $appointmentId = $req->params['id'];
        $dentist = $req->user;

        $model = new AppointmentModel();
        $appointment = $model->getAppointmentById($appointmentId);

        if (!$appointment) {
            throw new APIException("No appointment found with that id!", 400);
        }

        if ($appointment['dentist_id'] !== $dentist['id']) {
            throw new APIException('You can\'t delete this appointment!', 403);
        }

        $status = $model->cancelAppointment($appointment['id']);
        if (!$status) {
            throw new APIException("Something went wrong with cancelling your appointment!", 400);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Appointment cancelled successfully!'
        ]);
    }

    #[Route(path: '/api/appointments/{id}/note', method: 'post')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['dentist'])]
    public function addNoteToAppointment(Request $req): JsonResponse
    {
        $id = $req->params['id'];
        $data = $req->getPostBody()['note'];

        $model = new AppointmentModel();
        $status = $model->addNoteToAppointment($id, $data);

        if (!$status) {
            throw new APIException("Something went wrong with creating your note!", 400);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Note added successfully!'
        ]);
    }

    #[Route(path: '/api/missedAppointment/{userId}', method: 'get')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['dentist'])]
    public function userMissedAppointment(Request $req): JsonResponse
    {
        $userId = $req->params['userId'];

        $model = new AppointmentModel();
        $status = $model->userMissedAppointment($userId);

        if (!$status) {
            throw new APIException("Something went wrong with marking the user for missing an appointment!", 400);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'User marked for missing an appointment successfully!'
        ]);
    }
}