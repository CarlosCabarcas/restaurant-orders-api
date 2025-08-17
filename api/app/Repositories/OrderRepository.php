<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function create(array $data): Order
    {
        DB::beginTransaction();
        try {
            $order = Order::create([
                'client_name' => $data['client_name'],
                'total' => $data['total'],
            ]);

            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            DB::commit();
            $order->refresh()->load('items');

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Order::with('items')->get();
    }

    public function find(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function findByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return Order::where('status', $status)->with('items')->get();
    }

    public function update(int $id, array $data): bool
    {
        $order = $this->find($id);
        if ($order) {
            return $order->update($data);
        }
        return false;
    }

    public function delete(int $id): bool
    {
        return Order::destroy($id) > 0;
    }
}
