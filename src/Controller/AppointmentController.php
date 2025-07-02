<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Middleware\AuthMiddleware;
use Milos\Dentists\Core\Middleware\Middleware;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Core\Response\JSONResponse;
use Milos\Dentists\Core\Route;
use Milos\Dentists\Model\AppointmentModel;

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

        return $this->json([
            'status' => 'success',
            'message' => 'Appointment booked successfully!',
            'data' => [
                'appointmentCode' => $appointmentCode
            ]
        ]);
    }
}