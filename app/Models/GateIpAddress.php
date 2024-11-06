<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateIpAddress extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'ip_addresses';
    protected $fillable = [
        'admin_id',
        'memo',
        'ip',
        'disable',
        'is_on',
    ];
}
