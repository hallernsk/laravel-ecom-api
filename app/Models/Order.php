<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'payment_method_id', 'status', 'payment_link', 'total_amount'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }

}
