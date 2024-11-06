<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryUser as UserDB;

class GateSmsLog extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'sms_logs';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sms_id',
        'admin_id',
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
