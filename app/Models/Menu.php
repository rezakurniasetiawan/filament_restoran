<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'price',
        'id_category',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category');
    }
}
