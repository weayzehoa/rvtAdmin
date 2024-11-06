<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryCategory as CategoryDB;

class iCarrySubCategory extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'sub_category';
    protected $fillable = [
        'id',
        'name',
        'intro',
        'category_id',
        'sort_id',
        'is_on',
    ];
    public function mainCate(){
        return $this->beLongsTo(CategoryDB::class,'category_id','id');
    }
}
