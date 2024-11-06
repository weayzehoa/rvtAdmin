<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryAddressDisableKeyword extends Model
{
    use HasFactory;

    protected $connection = 'icarry';
    protected $table = 'address_disabled_keywords';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'keywords',
        'reason',
    ];
}
