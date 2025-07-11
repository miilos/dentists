<?php

namespace Milos\Dentists\Controller;

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
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['user'])]
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
}