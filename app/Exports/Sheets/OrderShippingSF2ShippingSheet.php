<?php

namespace App\Exports\Sheets;

use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\CompanySetting as CompanySettingDB;
use App\Models\SystemSetting as SystemSettingDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use App\Traits\OrderExportFunctionTrait;

class OrderShippingSF2ShippingSheet implements FromCollection,WithStrictNullComparison,WithStyles,WithTitle,WithColumnWidths
{
    use OrderExportFunctionTrait;
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
        $data = [];
        $order = OrderDB::with('items')->find($this->param['order_id']);
        if(!empty($order)){
            $systemSetting = SystemSettingDB::find(1);
            $usdRate = $systemSetting->exchange_rate_USD;
            $usd = round(($order->amount - $order->discount ) / $usdRate,2);
            $tmp = '';
            $nation = $this->checkNation2($order->receiver_address);
            if($nation == '澳門' || $nation == '澳门' || $nation == 'HONG KONG' || $nation == 'MACAU' || $nation == '香港' ){
                $tmp = '額外charge轉寄付';
            }
            $items = '';
            if(!empty($order->items)){
                foreach($order->items as $item){
                    $productModelName = '';
                    $productModelName = str_replace("單一規格","","-".$item->product_name);
                    $price = round($item->price / $usdRate,2);
                    $items .= '　'.$item->vendor_name.$productModelName.'*'.$item->quantity.' $'.$price.'，';
                }
                $items = ltrim(rtrim($items,'，'),'　');
            }

            $data[0] = ['',''];
            $data[1] = [$order->created_at,''];
            for($i=2;$i<=10;$i++){
                $data[$i] = ['','']; //空行
            }
            $data[11] = ['　　　'.$order->receiver_name,''];
            $data[12] = ['　　　'.$order->receiver_id_card,''];
            $data[13] = ['　　　'.$order->receiver_tel,''];
            $data[14] = ['　　　'.$order->receiver_address,''];
            $data[15] = $data[16] = ['',''];
            $data[17] = ['　　　'.$order->ship_to,''];
            $data[18] = $data[19] = ['',''];
            $data[20] = [$usd."\r\nus$",''];
            $data[21] = [$items,'']; //$items 資料
            $data[22] = ['訂單編號：'.$order->order_number,$tmp];
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A')->getAlignment()->setWrapText(true); //自動換行
        $sheet->getStyle('B')->getAlignment()->setWrapText(true); //自動換行
        for($i=1;$i<=23;$i++){ //全部字型12
            $sheet->getStyle('A'.$i)->getFont()->setSize(12);
            $sheet->getStyle('B'.$i)->getFont()->setSize(12);
        }
        $sheet->getStyle('A1:B1')->getFont()->setSize(21);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A12:B12')->getFont()->setSize(10);
        $sheet->getStyle('A13')->getFont()->setSize(8);
        $sheet->getStyle('A13')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->mergeCells('A15:A16'); //合併第一行A15-16
        $sheet->getStyle('A15')->getFont()->setSize(10);
        $sheet->getStyle('A15')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $sheet->getStyle('A15')->getFont()->setSize(8);
        $sheet->getStyle('A20')->getFont()->setSize(20);
        $sheet->getStyle('A21')->getFont()->setSize(8);
        $sheet->getStyle('A21')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        $sheet->getStyle('A21')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A22')->getFont()->setSize(8);
        $sheet->getRowDimension(22)->setRowHeight(60); //第22行高度60
        $sheet->getStyle('A22')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
    }

    public function title(): string
    {
        return $this->param['title'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 40,
        ];
    }
}
