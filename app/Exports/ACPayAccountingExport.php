<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;

use App\Exports\Sheets\EmptySheet;
use App\Exports\Sheets\ACPayMachineTableAccountingExport;
use App\Exports\Sheets\ACPayMachineDetailAccountingExport;
use App\Exports\Sheets\ACPayRecordDetailAccountingExport;

class ACPayAccountingExport implements WithMultipleSheets, WithProperties
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
        // dd($param);
        if(!empty($param['cate'])){
            if ($param['cate'] == 'machine') {
                if($param['type'] == 'detail'){ //匯出明細
                    $sheets = [ new ACPayMachineDetailAccountingExport($this->param) ];
                }
                if($param['type'] == 'table' || $param['type'] == 'condition'){ //匯出帳務總表&by條件
                    $sheets = [ new ACPayMachineTableAccountingExport($this->param) ];
                }
            }
            if ($param['cate'] == 'record') {
                $sheets = [ new ACPayMachineDetailAccountingExport($this->param) ];
            }
        }else{
            $sheets = [new EmptySheet()];
        }

        return $sheets;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-ACPay帳務管理匯出',
            'description'    => 'iCarry後台管理-ACPay帳務管理匯出',
            'subject'        => 'iCarry後台管理-ACPay帳務管理匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
