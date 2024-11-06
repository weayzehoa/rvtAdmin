<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MposLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mpos_record_id',
        'column_name',
        'admin_id',
        'log',
    ];
}
