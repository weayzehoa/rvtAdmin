<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateAdminPwdUpdateLog extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'admin_pwd_update_logs';
    protected $fillable = [
        'admin_id',
        'ip',
        'password',
        'editor_id',
    ];
}
