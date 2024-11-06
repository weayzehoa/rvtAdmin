<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateAdminLoginLog extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'admin_login_logs';
    protected $fillable = [
        'admin_id',
        'result',
        'ip',
        'account',
        'site',
    ];
}
