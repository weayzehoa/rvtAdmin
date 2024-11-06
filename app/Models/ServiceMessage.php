<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User as UserDB;
use App\Models\Admin as AdminDB;


class ServiceMessage extends Model
{
    use HasFactory;

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
        return $this->belongsTo(AdminDB::class);
    }
}
