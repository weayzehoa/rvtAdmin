<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigiwinPayment extends Model
{
    use HasFactory;
    protected $primaryKey = 'customer_no';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = [
        'customer_no',
        'customer_name',
        'set_deposit_ratio',
        'user_name',
    ];
}
