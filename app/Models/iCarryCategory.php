<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryCategoryLang as CategoryLangDB;

class iCarryCategory extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'category';
    public $timestamps = FALSE;
    protected $fillable = [
        'id',
        'name',
        'name_en',
        'name_jp',
        'name_kr',
        'name_th',
        'intro',
        'logo',
        'cover',
        'sort_id',
        'is_on',
    ];
    public function langs(){
        return $this->hasMany(CategoryLangDB::class,'category_id','id');
    }
}
