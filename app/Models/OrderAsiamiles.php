<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order as OrderDB;

class OrderAsiamiles extends Model
{
    use HasFactory;

    public $timestamps = FALSE;

    protected $fillable = [
        'order_id',
        'asiamiles_account',
        'asiamiles_name',
        'asiamiles_last_name',
    ];

    public function order(){
        return $this->belongsTo(OrderDB::class);
    }
}
