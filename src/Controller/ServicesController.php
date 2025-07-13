<?php

namespace Milos\Dentists\Controller;

use Milos\Dentists\Core\Exception\APIException;
use Milos\Dentists\Core\Middleware\AuthMiddleware;
use Milos\Dentists\Core\Middleware\Middleware;
use Milos\Dentists\Core\Request;
use Milos\Dentists\Core\Response\JSONResponse;
use Milos\Dentists\Core\Route;
use Milos\Dentists\Model\ServicesModel;

class ServicesController extends BaseController
{
    #[Route(path: '/api/services', method: 'get')]
    public function getAllServices(Request $req): JSONResponse
    {
        $model = new ServicesModel();
        $services = $model->getAllServices();

        if (!$services) {
            throw new APIException('No services found!', 404);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'services' => $services
            ]
        ]);
    }

    #[Route(path: '/api/dentists/{id}/services', method: 'get')]
    public function getServicesForDentist(Request $req): JSONResponse
    {
        $id = $req->params['id'];

        $model = new ServicesModel();
        $services = $model->getServicesForDentist($id);

        if (!$services) {
            throw new APIException('No services found!', 404);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'services' => $services
            ]
        ]);
    }

    #[Route(path: '/api/services/{id}', method: 'post')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['admin'])]
    public function editService(Request $req): JSONResponse
    {
        $data = $req->getPostBody();
        $id = $req->params['id'];

        $model = new ServicesModel();
        $service = $model->getServiceById($id);

        if (!$service) {
            throw new APIException('No service found with that id!', 400);
        }

        $updateData = [
            'name' => $data['name'] ?? $service['name'],
            'duration' => $data['duration'] ?? $service['duration'],
            'price' => $data['price'] ?? $service['price'],
        ];
        $status = $model->editService($id, $updateData);

        if (!$status) {
            throw new APIException('Something went wrong while updating the service!', 400);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Service updated successfully!'
        ]);
    }

    #[Route('/api/services/{id}', method: 'delete')]
    #[Middleware(function: [AuthMiddleware::class, 'authenticate'])]
    #[Middleware(function: [AuthMiddleware::class, 'authorize'], args: ['admin'])]
    public function deleteService(Request $req): JSONResponse
    {
        $id = $req->params['id'];

        $model = new ServicesModel();
        $service = $model->getServiceById($id);

        if (!$service) {
            throw new APIException('No service found with that id!', 400);
        }

        $model->deleteService($id);

        return $this->json([
            'status' => 'success',
            'message' => 'Service deleted successfully!'
        ], 204);
    }
}