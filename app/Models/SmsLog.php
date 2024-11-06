<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User as UserDB;

class SmsLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sms_id',
        'user_id',
        'vendor',
        'send_response',
        'get_response',
        'status',
        'message',
        'msg_id',
        'aws_id',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class);
    }
}
