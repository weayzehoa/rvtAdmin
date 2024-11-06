<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReplySample as ReplySampleDB;

class ReplySampleCategory extends Model
{
    use HasFactory;

    public $timestamps = FALSE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sort',
    ];

    public function sapmles(){
        return $this->hasMany(ReplySampleDB::class);
    }
}
