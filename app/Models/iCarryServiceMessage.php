<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryUser as UserDB;
use App\Models\GateAdmin as AdminDB;


class iCarryServiceMessage extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'service_message';
        //變更 Laravel 預設 created_at 與 updated_at 欄位
        const CREATED_AT = 'create_time';
        const UPDATED_AT = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_id',
        'to_id',
        'message',
        'is_read',
        'admin_id',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class,'from_id','id');
    }

    public function admin(){
        return $this->belongsTo(AdminDB::class,'admin_id','id');
    }
}
