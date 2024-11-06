<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile',
        'message',
        'sms_vendor',
        'user_id',
        'is_send',
        'order_id',
    ];
}
