<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarrySearchTitle extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'search_title';
    public $timestamps = FALSE;
    protected $fillable = [
        'title',
        'is_on',
        'start_time',
        'end_time',
        'sort_id',
    ];
}
















