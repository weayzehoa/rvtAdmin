<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateLog extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'logs';
    protected $fillable = [
        'admin_id',
        'db_name',
        'type',
        'sku',
        'digiwin_no',
        'old_data',
        'data',
    ];
}
