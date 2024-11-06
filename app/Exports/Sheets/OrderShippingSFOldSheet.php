<?php

namespace App\Exports\Sheets;

use App\Models\UserAddress as UserAddressDB;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\CompanySetting as CompanySettingDB;
use App\Models\Country as CountryDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use App\Traits\OrderExportFunctionTrait;

class OrderShippingSFOldSheet implements FromCollection,WithStrictNullComparison,WithStyles,WithTitle,WithHeadings,ShouldAutoSize,WithColumnWidths
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
        if(!empty($orders)){
            $c = 3;
            foreach ($orders as $order) {
                $items = $order->items;
                $i = 1;
                $colI='';
                if($order->ship_to == '澳門' || $order->ship_to == '香港'){
                    $colI = 1;
                }elseif($order->ship_to == '台灣'){
                    $colI = 104;
                }elseif($order->ship_to == '中國'){
                    $colI = 59;
                }
                $totalAmount = $order->amount + $order->shipping_fee + round($order->parcel_tax) - $order->spend_point - $order->discount;
                $currency = '';
                if(strstr($order->receiver_address,'中國')){
                    $memo = '大陸身分證：'.$order->receiver_id_card.'/'.$order->user_memo;
                }else if(strstr($order->receiver_address,'香港') || strstr($order->receiver_address, 'HONG KONG')){
                    $memo = '附加費轉寄付/'.$order->user_memo;
                }else if($order->receiver_name == '蝦皮台灣特選店'){
                    $memoLengh =  strpos($order->user_memo,'。');
                    $memo = substr($order->user_memo,0,$memoLengh);
                }else{
                    $memo = $order->user_memo;
                }
                foreach ($items as $item) {
                    //臺灣或機場不顯示金額 其他顯示
                    if($order->shipping_method == 1 || $order->shipping_method == 2 || $order->shipping_method == 3 || $order->shipping_method == 5 || $order->ship_to == '台灣'){
                        $currency = 'TWD';
                        $price = number_format($item->price);
                    }else{
                        if(strstr($order->receiver_address, '中國') == false){
                            $currency = 'USD';
                            $totalAmount = number_format(($order->amount + $order->shipping_fee + round($order->parcel_tax) - $order->spend_point - $order->discount) * 0.03, 4 );
                            $price = number_format($item->price * 0.03,4);
                        }else{
                            $currency = 'RMB';
                            $totalAmount = number_format(($order->amount + $order->shipping_fee + round($order->parcel_tax) - $order->spend_point - $order->discount) * 0.2, 4 );
                            $price = number_format($item->price * 0.2,4);
                        }
                    }
                    if($currency == 'RMB'){
                        $setBGColor[] =  $c;
                    }
                    empty($item->gtin13) ? $item->gtin13 = $item->sku : '';
                    $data[] = [
                        '886DCA',
                        'W8860691130',
                        '8860691130',
                        $i,
                        '',
                        '',
                        $order->order_number, //G
                        '',
                        $colI,
                        $order->receiver_name,
                        '',
                        '',
                        '',
                        $order->receiver_address,
                        '',
                        '',
                        '',
                        '',
                        $item->gtin13,
                        $item->vendor_name.' - '.$item->product_name,
                        $item->quantity,
                        $price,
                        $totalAmount,
                        $order->receiver_name,
                        $order->receiver_tel,
                        $order->receiver_tel,
                        $memo,
                        '',
                        '',
                        $order->shipping_number,
                        '',
                        '',
                        $currency,
                        '',
                    ];
                    $i++;
                    $c++;
                }
            }
        }
        $this->setBGColor = $setBGColor;
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $setBGColor = $this->setBGColor;
        if(!empty($setBGColor)){
            for ($i=0; $i < count($setBGColor) ; $i++) {
                $sheet->getStyle('A'.($setBGColor[$i]).':AZ'.($setBGColor[$i]))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('D3D3D3');
            }
        }
        $sheet->getStyle('B2')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $sheet->getStyle('G2')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $sheet->getStyle('J2')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $sheet->getStyle('N2')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $sheet->getStyle('G')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('S')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('Z')->getNumberFormat()->setFormatCode('#');
        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return '順豐出貨單(OLD)';
    }
    public function columnWidths(): array
    {
        $highestColumn = 'AZ';
        $highestColumn++;
        for ($column = 'A'; $column !== $highestColumn; $column++) {
            $width[$column] = 20;
        }
        return $width;
    }
    public function headings(): array
    {
        return [
            [
                '填寫說明',
                '根據客戶的月結帳戶填寫',
                '根據客戶的月結帳戶填寫',
                '空格欄位不可刪除及填寫內容',
                '標體為紅色:內容須簡體字',
                '',
                '自行編排單號不可重覆',
                '標體為紅色:內容須簡體字',
                '1.貨件走項：跨境商品-全球順。島內商品-島內件-批(80CM)※島內件依實際材積計算。2. 標標體為紅色:內容須簡體字。3. 承運商ID為"自提出庫"，填入"自提"。',
                '根據客戶公司名稱填寫，沒有收件公司填寫收件人姓名。',
                '',
                '',
                '',
                '收件人完整地址',
                '島內使用：填入大寫"N" or "Y"',
                '島內使用：P欄位填入Y時，Q欄位才需填入金額',
                '',
                '',
                '商品代碼(貨號)(國際條碼)',
                '',
                '',
                '',
                '單價金額，此欄位填入數據將會呈現購物清單上。',
                '系統已設定自動計算',
                '收件人名',
                '根據市內電話為選擇性填寫，行動電話號碼為必填。',
                '',
                '',
                '',
                '',
                '',
                'Y或N',
                'Y或N',
                '',
                '新加坡需填寫',
            ],
            [
                '仓库ID',
                '货主ID',
                '月结账号',
                '行号',
                '订单类型ID',
                '客户支付SF运费方式ID',
                '订单号码',
                '承运商ID',
                '承运商服务ID',
                '收件公司',
                '省',
                '市',
                '区/县',
                '地址',
                '是否货到付款',
                '代收货款金额',
                '是否保价',
                '声明价值',
                '条码',
                '预留字段1/商品名称',
                '商品出库数量',
                '价格',
                '订单总金额',
                '收件人',
                '固定电话',
                '手机',
                '订单备注',
                '是否自取件',
                '库存状态',
                '运单号',
                '批次号',
                '是否组合商品',
                '币种简码',
                '收件方邮编',
            ],
        ];
    }
}

