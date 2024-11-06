<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\iCarryCategory as CategoryDB;

class iCarryCategoryLang extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $connection = 'icarry';
    protected $table = 'category_langs';
    protected $fillable = [
        'category_id',
        'lang',
        'name',
        'intro',
    ];

    public function category(){
        return $this->belongsTo(CategoryDB::class,'category_id','id');
    }

}
