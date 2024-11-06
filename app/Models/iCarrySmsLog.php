<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryUser as UserDB;

class iCarrySmsLog extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'sms_log';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sms_id',
        'user_id',
        'mobile',
        'vendor',
        'send_response',
        'get_response',
        'status',
        'message',
        'msg_id',
        'aws_id',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class,'user_id','id');
    }
}
