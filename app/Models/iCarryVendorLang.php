<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryVendorLang extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'vendor_langs';

    protected $fillable = [
        'vendor_id',
        'lang',
        'name',
        'summary',
        'description',
        'curation',
    ];
}
