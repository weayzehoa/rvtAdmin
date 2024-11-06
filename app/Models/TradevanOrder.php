<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order as OrderDB;

class TradevanOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'RID',
        'Click_ID',
    ];

    public function order(){
        return $this->belongsTo(OrderDB::class);
    }
}
