<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;

use App\Exports\Sheets\EmptySheet;
use App\Exports\Sheets\OrderDetailSheet;
use App\Exports\Sheets\OrderDetailItemSheet;

class VendorOrderExport implements WithMultipleSheets, WithProperties
{
    protected $param;

    public function __construct(array $param)
    {
        $this->param = $param;
    }
    /**
     * @return array
     */
    public function sheets(): array
    {
        $param = $this->param;
        $sheets = [];
        if(!empty($param['cate'])){
            if ($param['cate'] == 'excel'){
                $sheets = [
                    new OrderDetailSheet($this->param),
                    new OrderDetailItemSheet($this->param),
                ];
            }
        }else{
            $sheets = [new EmptySheet()];
        }
        return $sheets;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry商家後台管理員',
            'lastModifiedBy' => 'iCarry商家後台管理員',
            'title'          => 'iCarry商家後台管理-訂單資料匯出',
            'description'    => 'iCarry商家後台管理-訂單資料匯出',
            'subject'        => 'iCarry商家後台管理-訂單資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry商家後台管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
