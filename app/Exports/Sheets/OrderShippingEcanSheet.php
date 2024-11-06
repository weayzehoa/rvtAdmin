<?php

namespace App\Exports\Sheets;

use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\CompanySetting as CompanySettingDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use App\Traits\OrderExportFunctionTrait;

class OrderShippingEcanSheet implements FromCollection,WithStrictNullComparison,WithStyles,WithTitle,WithColumnWidths
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
        $orders = $this->getOrderData($this->param);
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $order->shipping_method == 2 ? $receiverAddress = $order->receiver_address.'/'.$order->receiver_keyword : $receiverAddress = $order->receiver_address;
                //寄件者備註新增提貨時間及訂單號碼
                if($order->book_shipping_date && $order->shipping_method != 4){
                    if($order->user_memo){
                        $order->user_memo = '提貨時間：'.$order->book_shipping_date.'，'.$order->user_memo;
                    }else{
                        $order->user_memo = '提貨時間：'.$order->book_shipping_date;
                    }
                }
                $order->user_memo ? $order->user_memo = $order->order_number.'/'.$order->user_memo : $order->user_memo = $order->order_number;
                $data[] = [
                    $order->order_number,
                    '4645270101',
                    'iCarry-我來寄',
                    '0906486688',
                    '台北市中山區南京東路三段103號11樓',
                    '',//f
                    $order->receiver_name,
                    '',//h
                    $this->phoneChange($order->receiver_tel),
                    $receiverAddress,
                    '糕餅零食',
                    '1',
                    '', //m
                    "班機號碼：{$order->receiver_keyword} 提貨時間：".substr($order->receiver_key_time,0,19),
                    '寄付月結',
                    '標準快遞',
                    '',//q
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',//z
                    '33900',
                    date('Y-m-d', strtotime('+1 day', time())), //派送日期加1天
                    '09:00-12:00',
                ];
            }
            $this->count = $count = count($data);
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('L')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('AA')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('L')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('AA')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return '宅配通物流(機場)';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 15,
            'D' => 16,
            'E' => 45,
            'F' => 15,
            'G' => 15,
            'H' => 10,
            'I' => 15,
            'J' => 40,
            'K' => 15,
            'L' => 10,
            'M' => 10,
            'N' => 40,
            'O' => 10,
            'P' => 10,
            'Q' => 10,
            'R' => 10,
            'S' => 10,
            'T' => 10,
            'U' => 10,
            'V' => 10,
            'W' => 10,
            'X' => 10,
            'Y' => 10,
            'Z' => 10,
            'AA' => 10,
            'AB' => 10,
            'AC' => 15,
        ];
    }
    public function phoneChange($phone)
    {
        //好巴這邊為何不用 str_replace 來直接取代掉886 因為你不知道是頭吃886
        //還是手機號碼吃886為了保險就只檢查表頭3碼再取表頭三碼以外的  這樣做法比較好
        //這邊由於格式超級不固定... 要去掉+ 886但有時候 886後面還有空白也要去掉
        $receiver_tel  = str_replace('+','',$phone);//先去掉+
        $receiver_tel_lengh = strlen($receiver_tel);//先取得長度(擷取使用)用這是保險
        $receiver_tel_tmp = '';
        $str_tmp = $receiver_tel_lengh-3;//台灣是三碼
            $receiver_tel_tmp = substr($receiver_tel,0,"-{$str_tmp}");
            if($receiver_tel_tmp=='886'){//就是香港
                $receiver_tel = substr($receiver_tel,3);//擷取完畢
            }
        $receiver_tel  = str_replace(' ','',$receiver_tel);//去除有空格的(有些+XXX 的問題)
        return $receiver_tel;
    }
}