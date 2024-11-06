<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryOldCuration extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'curation';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    protected $fillable = [
        'main_title',
        'subtitle',
        'more_caption',
        'more_caption_url',
        'main_title_en',
        'subtitle_en',
        'more_caption_en',
        'main_title_jp',
        'subtitle_jp',
        'more_caption_jp',
        'main_title_kr',
        'subtitle_kr',
        'more_caption_kr',
        'main_title_th',
        'subtitle_th',
        'more_caption_th',
        'is_select',
        'select_data',
        'layout',
        'text_layout',
        'photo_data',
        'start_time',
        'end_time',
        'sort',
        'is_on',
    ];

}
