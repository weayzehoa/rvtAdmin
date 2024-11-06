<?php

namespace App\Exports;

use App\Models\ProductModel as ProductModelDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DigiwinEc2NoExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings, WithStyles
{
    protected $param;

    public function __construct(array $param)
    {
        $this->param = $param;
    }

    public function collection()
    {
        for($i=1; $i<count($this->param); $i++){
            $sku = $this->param[$i][0];
            $digiwinNo = $this->param[$i][1];
            if(!empty($sku)){
                $order = ProductModelDB::where('sku',$sku)->orWhereRaw(" id = RIGHT('{$sku}',6)")
                    ->select('digiwin_no')->first();
                !empty($order) ? $digiwinNo = $order->digiwin_no : $digiwinNo = null;
            }elseif(!empty($digiwinNo)){
                $order = ProductModelDB::where('digiwin_no',$digiwinNo)->select('sku')->first();
                !empty($order) ? $sku = $order->sku : $sku = null;
            }
            $data[] = [
                $sku,
                $digiwinNo,
            ];
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('B')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    }

    public function headings(): array
    {
        return $this->param[0];
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-鼎新資料處理_物流單號匯入匯出',
            'description'    => 'iCarry後台管理-鼎新資料處理_物流單號匯入匯出',
            'subject'        => 'iCarry後台管理-鼎新資料處理_物流單號匯入匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
