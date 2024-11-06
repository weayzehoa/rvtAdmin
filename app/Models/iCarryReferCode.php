<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\iCarryUser as UserDB;

class iCarryReferCode extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'refer_code_event';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    protected $fillable = [
        'code',
        'icarry_point',
        'icarry_point_type',
        'total_register',
        'start_time',
        'end_time',
        'memo',
        'status',
    ];

    public function users(){
        return $this->hasMany(UserDB::class,'refer_code','code');
    }
}
