<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateExportCenter extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'export_centers';
    protected $fillable = [
        'export_no',
        'admin_id',
        'cate',
        'name',
        'condition',
        'filename',
        'start_time',
        'end_time',
    ];
}
