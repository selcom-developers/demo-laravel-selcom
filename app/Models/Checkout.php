<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'email',
        'buyer_name',
        'buyer_phone',
        'amount',
        'currency',
        'number_of_items',
        'status'
    ];
    

}
