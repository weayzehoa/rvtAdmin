<?php

namespace App\Exports;

use App\Models\Order as OrderDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DigiwinLogisticsExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings, WithStyles
{
    protected $param;

    public function __construct(array $param)
    {
        $this->param = $param;
    }

    public function collection()
    {
        for($i=1; $i<count($this->param); $i++){
            $orderNumber = $this->param[$i][1];
            $shippingNumber = null;
            $shippingMemo = null;
            $expressWay = null;
            if(!empty($orderNumber)){
                $order = OrderDB::where('order_number',$orderNumber)
                    ->orWhere('partner_order_number',$orderNumber)
                    ->select(['shipping_number','shipping_memo'])->first();
                if(!empty($order)){
                    $shippingNumber = $order->shipping_number;
                    $shippingMemo = $order->shipping_memo;
                    if(!empty($shippingMemo)){
                        $shippingData = json_decode($shippingMemo,true);
                        foreach ($shippingData as $sm) {
                            if($sm['express_no'] == $shippingNumber){
                                $expressWay = $sm['express_way'];
                            }
                        }
                    }
                }
            }
            $data[] = [
                $this->param[$i][0],
                $orderNumber,
                $expressWay,
                $shippingNumber,
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
