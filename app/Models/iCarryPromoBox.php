<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryPromoBox extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'promo_box';
    public $timestamps = FALSE;
    protected $fillable = [
        'title',
        'text_teaser',
        'text_complete',
        'title_en',
        'text_teaser_en',
        'text_complete_en',
        'title_jp',
        'text_teaser_jp',
        'text_complete_jp',
        'title_kr',
        'text_teaser_kr',
        'text_complete_kr',
        'title_th',
        'text_teaser_th',
        'text_complete_th',
        'img_url',
        'is_on',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
    ];
}
















