<?php

namespace App\Exports;

use App\Models\Order as OrderDB;
use App\Models\OrderShipping as OrderShippingDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderShippingsExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings,WithColumnWidths,WithStyles,WithTitle
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
        $orders = OrderDB::with('shippings')->where('shipping_memo','!=','');

        if(!empty($param['express_way'])){
            $expressWay = $param['express_way'];
            $orders = $orders->where('shipping_memo','like',"%$expressWay%");
        }

        if(!empty($param['created_at'])){
            $createAt = $param['created_at'];
            $orders = $orders->where('created_at','>=',$createAt);
        }

        if(!empty($param['created_at_end'])){
            $createAtEnd = $param['created_at_end'];
            $orders = $orders->where('created_at','<=',$createAtEnd);
        }

        $orders = $orders->orderBy('created_at','desc')->get();

        foreach($orders as $order){
            //iCarry出貨
            if(count($order->shippings) > 0){
                foreach($order->shippings as $shipping){
                    if(!empty($param['express_way']) && $shipping->express_way == $param['express_way']){
                        $shipping->express_way == '廠商發貨' ? $shippingFromVendor = 'V' : $shippingFromVendor = '';
                        $data[] = [
                            $order->order_number,
                            $shipping->created_at,
                            $shipping->express_way,
                            $shipping->express_no,
                            $shippingFromVendor,
                        ];
                    }else{
                        $shipping->express_way == '廠商發貨' ? $shippingFromVendor = 'V' : $shippingFromVendor = '';
                        $data[] = [
                            $order->order_number,
                            $shipping->created_at,
                            $shipping->express_way,
                            $shipping->express_no,
                            $shippingFromVendor,
                        ];
                    }
                }
            }
        }
        empty($data) ? $data = [] : '';
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT)->setWrapText(true);

        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles

    }

    public function title(): string
    {
        return 'iCarry 我來寄 物流單查詢資料';
    }

    public function headings(): array
    {
        return [
            '訂單編號',
            '填寫時間',
            '物流廠商',
            '物流單號',
            '店家出貨',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 25,
            'D' => 30,
            'E' => 15,
        ];
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-物流單資料匯出',
            'description'    => 'iCarry後台管理-物流單資料匯出',
            'subject'        => 'iCarry後台管理-物流單資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
