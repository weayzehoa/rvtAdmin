<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\iCarryUser as UserDB;

class iCarryUserPoint extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'user_point';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'from_user_id',
        'point_type',
        'points',
        'balance',
        'dead_time',
        'is_dead',
        'real_dead_time',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class,'user_id','id');
    }

}
