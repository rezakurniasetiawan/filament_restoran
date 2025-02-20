<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'id_customer',
        'total_order',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'id_customer');
    }

    public function menu_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'id_menu');
    }
}
