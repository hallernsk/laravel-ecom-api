<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
protected $fillable = ['name', 'description', 'price'];

public function cartItems()
{
    return $this->hasMany(cartItem::class);
}

public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}
}
