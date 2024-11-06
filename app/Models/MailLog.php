<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin as AdminDB;

class MailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'email_supplier',
        'type',
        'to',
        'subject',
        'status',
    ];

    public function admin(){
        return $this->belongsTo(AdminDB::class);
    }
}
