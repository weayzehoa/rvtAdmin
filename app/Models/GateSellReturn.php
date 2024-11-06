<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GateSellReturn extends Model
{
    use HasFactory;
    protected $connection = 'erpGate';
    protected $table = 'sell_returns';
}
