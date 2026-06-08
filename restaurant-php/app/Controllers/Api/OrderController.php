<?php

namespace App\Controllers\Api;

use App\Models\OrderModel;
use CodeIgniter\RESTful\ResourceController;

class OrderController extends ResourceController
{
    protected $format = 'json';

    public function index(): \CodeIgniter\HTTP\ResponseInterface
    {
        $model  = new OrderModel();
        $orders = $model->getOrdersWithDetails();

        return $this->respond([
            'status' => 'success',
            'count'  => count($orders),
            'data'   => $orders,
        ]);
    }

    public function show($id = null): \CodeIgniter\HTTP\ResponseInterface
    {
        $id = (int) $id;

        if ($id <= 0) {
            return $this->failValidationErrors('Order ID must be a positive integer.');
        }

        $model = new OrderModel();
        $order = $model->getOrderWithDetails($id);

        if (!$order) {
            return $this->failNotFound("Order #{$id} not found.");
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $order,
        ]);
    }
}