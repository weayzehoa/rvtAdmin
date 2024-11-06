<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MposRecord as MposRecordDB;

class MposFill extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'receiver_province',
        'receiver_city',
        'receiver_area',
        'receiver_zip_code',
        'receiver_address',
        'receiver_name',
        'receiver_email',
        'receiver_keyword',
        'receiver_key_time',
        'room_number',
        'user_memo',
        'admin_memo',
    ];
    public function record(){
        return $this->belongsTo(MposRecordDB::class,'order_number','order_number');
    }
}
