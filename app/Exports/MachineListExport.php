<?php

namespace App\Exports;

use DB;
use App\Models\MachineList as MachineListDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class MachineListExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison,WithColumnWidths,WithStyles,WithTitle
{
    protected $param;

    public function __construct(array $param)
    {
        $this->param = $param;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $param = $this->param;
        $machines = MachineListDB::with('vendor','account');
        !empty($param['vendor_id']) ? $machines = $machines->where('vendor_id',$param['vendor_id']) : '';
        $machines = $machines->orderBy('is_on','desc')->orderBy('id','asc')->get();
        $data[0] = ['商家資料','','','','帳號','物流方式','','','','','是否開啟店家免運','金流方式','','收款行','狀態','機台編號'];
        $data[1] = ['商家名稱','店名','公司名稱','統編','帳號','機場提貨','旅店提貨',
                    '寄送台灣','寄送海外','自行提貨','是否開啟店家免運','信用卡','支付寶','收款行','狀態','機台編號',
                    ];
        foreach($machines as $machine){
            $data[] = [
                $machine->vendor->name,
                $machine->name,
                $machine->vendor->company,
                $machine->vendor->vat_number,
                $machine->account->account,
                $machine->airport_shipping == 1 ? 'Y' : 'N',
                $machine->hotel_shipping == 1 ? 'Y' : 'N',
                $machine->taiwan_shipping == 1 ? 'Y' : 'N',
                $machine->overseas_shipping == 1 ? 'Y' : 'N',
                $machine->yourself_shipping == 1 ? 'Y' : 'N',
                $machine->free_shipping == 1 ? '是' : '否',
                $machine->card_paying == 1 ? 'Y' : 'N',
                $machine->alipay_paying == 1 ? 'Y' : 'N',
                $machine->bank,
                $machine->is_on == 1 ? '啟用' : '停用',
                "C".str_pad($machine->id,5,'0',STR_PAD_LEFT),
            ];
        }
        return collect($data);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:D1'); //合併第一行A-D
        $sheet->mergeCells('E1:E2');
        $sheet->mergeCells('F1:J1');
        $sheet->mergeCells('K1:K2');
        $sheet->mergeCells('L1:M1');
        $sheet->mergeCells('N1:N2');
        $sheet->mergeCells('O1:O2');
        $sheet->mergeCells('P1:P2');
        $sheet->getStyle('A')->getAlignment()->setWrapText(true);
        $sheet->getStyle('B')->getAlignment()->setWrapText(true);
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#'); //D欄格式數字改字串
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('M')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }
    public function title(): string
    {
        return 'ACPay機台資料';
    }
    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 25,
            'C' => 25,
            'D' => 15,
            'E' => 20,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 10,
            'J' => 10,
            'K' => 20,
            'L' => 10,
            'M' => 10,
            'N' => 10,
            'O' => 10,
            'P' => 15,
        ];
    }
    public function properties(): array
    {
        !empty($this->param['vendor_id']) ? $vendor = '商家' : $vendor = '';
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry'.$vendor.'後台管理-ACPay機台資料匯出',
            'description'    => 'iCarry'.$vendor.'後台管理-ACPay機台資料匯出',
            'subject'        => 'iCarry'.$vendor.'後台管理-ACPay機台資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
