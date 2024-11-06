<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order as OrderDB;

class Spgateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'amount',
        'pay_status',
        'PaymentType',
        'memo',
        'post_json',
        'get_json',
        'result_json',
    ];

    public function order(){
        return $this->belongsTo(OrderDB::class,'order_number','order_number');
    }

}
