<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Exception\APIException;
use Milos\Dentists\Core\Middleware\AuthMiddleware;
use Milos\Dentists\Core\Middleware\Middleware;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Core\Response\JSONResponse;
use Milos\Dentists\Core\Route;
use Milos\Dentists\Model\DentistModel;

class DentistController extends BaseController
{
    #[Route(path: '/api/dentists', method: 'get')]
    public function getAllDentists(Request $req): JsonResponse
    {
        $model = new DentistModel();
        $dentists = $model->getAllDentists();

        return $this->json([
            'status' => 'success',
            'results' => count($dentists),
            'data' => [
                'dentists' => $dentists
            ]
        ]);
    }

    #[Route(path: '/api/dentists/{id}', method: 'get')]
    public function getDentist(Request $req): JsonResponse
    {
        $id = $req->params['id'];

        $model = new DentistModel();
        $dentist = $model->getDentistById($id);

        if (!$dentist) {
            throw new APIException('No dentist found with that id!', 404);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'dentist' => $dentist
            ]
        ]);
    }

    #[Route(path: '/api/dentists/{id}', method: 'post')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['admin'])]
    public function editDentist(Request $req): JsonResponse
    {
        $id = $req->params['id'];
        $data = $req->getPostBody();

        $model = new DentistModel();
        $dentist = $model->getDentistById($id);

        if (!$dentist) {
            throw new APIException('No dentist found with that id!', 400);
        }

        $updateData = [
            'first_name' => $data['first_name'] ?? $dentist['first_name'],
            'last_name' => $data['last_name'] ?? $dentist['last_name'],
            'photo' => $data['photo'] ?? $dentist['photo'],
        ];

        $status = $model->editDentist($id, $updateData);

        if (!$status) {
            throw new APIException('Something went wrong with updating the dentist!', 500);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Dentist updated successfully!'
        ]);
    }

    #[Route(path: '/api/dentists/{id}', method: 'delete')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['admin'])]
    public function deleteDentist(Request $req): JSONResponse
    {
        $id = $req->params['id'];

        $model = new DentistModel();
        $status = $model->deleteDentist($id);

        if (!$status) {
            throw new APIException('Something went wrong with deleting the dentist!', 500);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Dentist deleted successfully!'
        ], 204);
    }
}