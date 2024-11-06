<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MposRecord as MposRecordDB;

class MposRefundLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mpos_record_id',
        'order_number',
        'amount',
        'response',
    ];

    public function record(){
        return $this->belongsTo(MposRecordDB::class);
    }
}
