<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ReplySampleCategory as ReplySampleCategoryDB;

class ReplySample extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reply_sample_category_id',
        'content',
        'sort',
    ];

    public function category(){
        return $this->belongsTo(ReplySampleCategoryDB::class);
    }
}
