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
    #[Middleware([AuthMiddleware::class, 'authenticate'])]
    #[Middleware([AuthMiddleware::class, 'authorize'], args: ['user'])]
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
}