<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(): JsonResponse
    {
        $orders = $this->orderService->getAllOrders();
        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $order = $this->orderService->createOrder($data);

        return response()->json($order, 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json($order);
    }

    public function advance(int $id)
    {
        $response = $this->orderService->advanceOrder($id);
        if (!$response) {
            return response()->json(['message' => 'Order not found'], 404);
        }
        return response()->json($response);
    }

}
