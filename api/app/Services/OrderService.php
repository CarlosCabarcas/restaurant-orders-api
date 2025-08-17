<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Redis;
use App\Models\Order;
use Mockery\Matcher\Any;

class OrderService
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function createOrder(array $data)
    {
        $total = 0;
        $itemTotal = 0;
        foreach ($data['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $total += $itemTotal;
        }

        // Add total to the order data
        $data['total'] = $total;

        $order = $this->orderRepository->create($data);

        // store the order in Redis cache
        $this->saveOrderToCache($order);

        $orders = $this->getAllOrders();

        return $orders;
    }

    public function getAllOrders()
    {
        // search first in Redis cache
        $cachedOrders = Redis::get('orders:all');
        if ($cachedOrders) {
            return json_decode($cachedOrders, true);
        }

        // if not found in cache, fetch from database
        $orders = $this->orderRepository->all();

        if ($orders->isNotEmpty()) {
            // store the orders in Redis cache
            Redis::set('orders', $orders->toJson());
        }

        return $orders;
    }

    public function getOrderById(int $id)
    {
        // search first in Redis cache
        $cachedOrder = Redis::get('order:' . $id);
        if ($cachedOrder) {
            return json_decode($cachedOrder, true);
        }

        // if not found in cache, fetch from database
        $order = $this->orderRepository->find($id);

        if ($order) {
            // store the order in Redis cache
            Redis::set('order:' . $id, $order->toJson());
        }

        return $order;
    }

    public function updateOrder(int $id, array $data): bool
    {
        $updated = $this->orderRepository->update($id, $data);

        if ($updated) {
            // update the order in Redis cache
            Redis::set('order:' . $id, json_encode($this->getOrderById($id)));
        }

        return $updated;
    }

    public function deleteOrder(int $id): bool
    {
        $deleted = $this->orderRepository->delete($id);

        if ($deleted) {
            // remove the order from Redis cache
            Redis::del('order:' . $id);
        }

        return $deleted;
    }

    public function saveOrderToCache(Order $order): void
    {
        Redis::set("order:{$order->id}", $order->toJson());

        $statuses = ['initiated', 'sent', 'delivered'];
        foreach ($statuses as $status) {
            Redis::srem("orders:status:{$status}", $order->id);
        }

        Redis::sadd("orders:status:{$order->status}", $order->id);
    }

    public function advanceOrder(int $id)
    {
        $order = $this->getOrderById($id);
        $statusToUnpdate = null;
        if (!$order) {
            return null;
        }

        if ($order['status'] === 'initiated') {
            $this->updateOrder($id, ['status' => 'sent']);
            $this->updateOrderStatusInCache($id, 'initiated', 'sent');
            $statusToUnpdate = 'sent';
        }elseif ($order['status'] === 'sent') {
            $this->deleteOrder($id);
            Redis::del("order:{$id}");
            Redis::lrem("orders:status:sent", 0, $id);
            $statusToUnpdate = 'delivered';
        }

        return "Order {$id} advanced to status: {$statusToUnpdate}";
    }

    public function updateOrderStatusInCache(int $id, string $oldStatus, $newStatus): void
    {
        $orderKey = "order:{$id}";

        $order = Redis::get($orderKey);
        if (!$order) {
            return;
        }

        $orderData = json_decode($order, true);
        $orderData['status'] = $newStatus;

        Redis::set($orderKey, json_encode($orderData));

        // Update the order status in the set
        $oldListKey = "orders:status:{$oldStatus}";
        Redis::lrem($oldListKey, 0, $order);

        $newListKey = "orders:status:{$newStatus}";
        Redis::rpush($newListKey, $order);
    }
}
