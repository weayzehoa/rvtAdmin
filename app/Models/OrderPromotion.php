<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order as OrderDB;

class OrderPromotion extends Model
{
    use HasFactory;

    public $timestamps = FALSE;

    protected $fillable = [
        'order_id',
        'promotion_ids',
    ];

    public function order(){
        return $this->belongsTo(OrderDB::class);
    }
}
