<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TmpMachineList extends Model
{
    use HasFactory;
    protected $fillable = [
        'id', //資料移轉後須將此行移除
        'vendor_id',
        'vendor_account_id',
        'name',
        'contact_person',
        'tel',
        'fax',
        'email',
        'city',
        'zip_code',
        'address',
        'airport_shipping',
        'hotel_shipping',
        'yourself_shipping',
        'overseas_shipping',
        'taiwan_shipping',
        'airport_box',
        'hotel_box',
        'yourself_box',
        'overseas_box',
        'taiwan_box',
        'airport_base',
        'hotel_base',
        'yourself_base',
        'overseas_base',
        'taiwan_base',
        'card_paying',
        'alipay_paying',
        'card_draw',
        'alipay_draw',
        'free_shipping',
        'bank',
        'is_on',
        'can_return',
        'can_cancel',
    ];
}
