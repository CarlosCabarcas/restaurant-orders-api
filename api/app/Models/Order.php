<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class Order extends Model
{
    protected $fillable = [
        'client_name',
        'status',
        'total',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
