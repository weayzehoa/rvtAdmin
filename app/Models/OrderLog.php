<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order as OrderDB;
use App\Models\Admin as AdminDB;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'column_name',
        'admin_id',
        'log',
    ];

    public function order(){
        return $this->belongsTo(OrderDB::class);
    }

    public function admin(){
        return $this->belongsTo(AdminDB::class);
    }
}
