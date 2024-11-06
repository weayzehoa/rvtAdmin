<?php

namespace App\Exports\Sheets;

use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\ShopcomOrder as ShopcomOrderDB;
use App\Models\Product as ProductDB;
use App\Models\OrderLog as OrderLogDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use App\Traits\ProductAvailableDate;
use App\Traits\OrderExportFunctionTrait;

class OrderDigiWinSheet implements FromCollection,WithStrictNullComparison, WithHeadings,WithStyles,WithTitle,ShouldAutoSize
{
    use ProductAvailableDate,OrderExportFunctionTrait;
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
        $faraway = ['萬里區','金山區','平溪區','雙溪區','貢寮區','坪林區','烏來區','三芝區','石門區','石碇區','復興鄉','關西鎮','芎林鄉','寶山鄉','五峰鄉','橫山鄉','尖石鄉','北埔鄉','峨嵋鄉','南庄鄉','獅潭鄉','大湖鄉','泰安鄉','卓蘭鎮','東勢區','和平區','新社區','溪州鄉','竹塘鄉','二林鄉','大城鄉','中寮鄉','國姓鄉','仁愛鄉','名間鄉','集集鎮','水里鄉','魚池鄉','信義鄉','竹山鎮','鹿谷鄉','番路鄉','梅山鄉','阿里山鄉','大埔鄉','東石鄉','義竹鄉','布袋鎮','褒忠鄉','東勢鄉','台西鄉','麥寮鄉','二崙鄉','北港鎮','水林鄉','口湖鄉','四湖鄉','元長鄉','左鎮區','玉井區','楠西區','南化區','龍崎區','七股區','將軍區','北門區','白河區','東山區','大內區','田寮區','六龜區','內門區','那瑪夏區','茂林區','桃源區','甲仙區','杉林區','恆春鄉','枋寮鄉','三地門鄉','來義鄉','東港鄉','獅子鄉','牡丹鄉','林邊鄉','高樹鄉','枋山鄉','里港鄉','滿州鄉','車城鄉','鹽埔鄉','九如鄉','萬巒鄉','新園鄉','新埤鄉','春日鄉','霧台鄉','佳冬鄉','竹田鄉','泰武鄉','南州鄉','崁頂鄉','瑪家鄉','麟洛鄉','南澳鄉','冬山鄉','大同鄉','三星鄉','光復鄉','玉里鎮','新城鄉','壽豐鄉','鳳林鎮','豐濱鄉','瑞穗鄉','富里鄉','秀林鄉','萬榮鄉','卓溪鄉','成功鎮','關山鎮','卑南鄉','大武鄉','太麻里鄉','東河鄉','長濱鄉','鹿野鄉','池上鄉','延平鄉','海端鄉','達仁鄉','金峰鄉'];
        $data = [];
        $orderIds = $this->getOrderData($this->param);
        if (!empty($orderIds)) {
            $items = OrderItemDB::join('orders', 'orders.id', 'order_items.order_id')
                ->join('product_models', 'product_models.id', 'order_items.product_model_id');
            $items = $items->whereIn('order_items.order_id', $orderIds);
            $items = $items->select([
                DB::raw("DATE_FORMAT(orders.pay_time,'%Y-%m-%d') as pay_time"),
                DB::raw("DATE_FORMAT(orders.pay_time,'%Y%m%d') as payTime"),
                'orders.ship_to',
                DB::raw("(IF(orders.pay_method='蝦皮', IF(orders.user_memo LIKE '%蝦皮訂單：(台灣)%',(SELECT CONCAT(customer_no,'@_@',customer_name,'@_@',set_deposit_ratio) FROM digiwin_payments WHERE customer_name='台灣蝦皮'),(SELECT CONCAT(customer_no,'@_@',customer_name,'@_@',set_deposit_ratio) FROM digiwin_payments WHERE customer_name='新加坡蝦皮')) ,(SELECT CONCAT(customer_no,'@_@',customer_name,'@_@',set_deposit_ratio) FROM digiwin_payments WHERE customer_name=orders.pay_method))) as pay"),
                'is_shopcom' => ShopcomOrderDB::whereColumn('orders.id', 'shopcom_orders.order_id')->select(DB::raw('COUNT(id) as count'))->limit(1),
                'orders.promotion_code',
                'orders.create_type',
                'orders.shipping_method',
                'orders.status',
                'orders.book_shipping_date',
                'orders.user_memo',
                'orders.receiver_key_time',
                'orders.receiver_keyword',
                'orders.receiver_address',
                'orders.order_number',
                'orders.partner_order_number',
                'orders.receiver_name',
                'orders.receiver_tel',
                'orders.discount',
                'orders.spend_point',
                'orders.shipping_fee',
                'orders.parcel_tax',
                'orders.shipping_memo',
                'product_models.digiwin_no',
                'product_models.origin_digiwin_no',
                DB::raw("(SELECT name FROM vendors WHERE id IN(SELECT vendor_id FROM products WHERE id IN(SELECT product_id from product_models where digiwin_no IN((SELECT origin_digiwin_no FROM product_models WHERE product_models.id=order_items.product_model_id))))) as origin_vendor_name"),
                DB::raw("(SELECT name FROM products WHERE id IN(SELECT product_id from product_models where digiwin_no IN((SELECT origin_digiwin_no from product_models WHERE product_models.id=order_items.product_model_id)))) as origin_product_name"),
                'order_items.product_name',
                'order_items.vendor_name',
                'order_items.quantity',
                'order_items.price',
                'order_items.gross_weight',
                'order_items.order_id',
            ]);
            $items = $items->orderBy('orders.id', 'desc')->get();
            // dd($items->toArray());
            if (count($items) > 0) {
                $nowOrderNumber = null;
                $i = 0;
                foreach ($items as $item) {
                    if ($item->shipping_method == 1) {
                        $item->max_days = ProductDB::whereIn('id', OrderItemDB::where('order_id', $item->order_id)->select('product_id')->get()->pluck('product_id')->all())->max('airport_days');
                    } else {
                        $item->max_days = ProductDB::whereIn('id', OrderItemDB::where('order_id', $item->order_id)->select('product_id')->get()->pluck('product_id')->all())->max('hotel_days');
                    }
                    $colA = $colT = $colH = $colI = null;
                    if (!empty($item->pay_time)) {
                        if (empty($item->book_shipping_date)) {
                            $colT = $this->ProductAvailableDate($item->max_days, $item->pay_time, 'shipping');
                        } else {
                            //$item->book_shipping_date存在時,去除 -,/ 符號
                            $colT=str_replace(array('-','/'), array('',''), $item->book_shipping_date);
                            if (empty($colT) || $colT=='00000000') {
                                $colT = $this->ProductAvailableDate($item->max_days, $item->pay_time, 'shipping');
                            }
                        }
                    }
                    !empty($colT) ? $colT=str_replace(array('-','/'), array('',''), $colT) : '';
                    !empty($item->payTime) ? $colA = $item->payTime : '';
                    if (!empty($item->pay)) {
                        $pay = explode('@_@', $item->pay);
                        if (is_array($pay)) {
                            $colB = $pay[0];
                            $colC = $pay[1];
                            $colV = $pay[2];
                        } else {
                            $colB = $colC = $colV = null;
                        }
                    }
                    if ($item->create_type == 'alipay') {
                        $colD = '004';
                        $colE = '小程序';
                    } elseif ($item->promotion_code == 'buyandship') {
                        $colD = '003';
                        $colE = 'buyandship';
                    } elseif (!empty($item->is_shopcom)) {
                        $colD = '002';
                        $colE = '美安';
                    } else {
                        $colD = '001';
                        $colE = 'iCarry';
                    }
                    intval($pay[0])>9 ? $colD = $colE = null : '';
                    $colL = str_replace('-', '', substr($item->receiver_key_time, 0, 10));
                    if ($item->shipping_method == 1) {
                        $colF = $item->receiver_address;
                        $colH = $item->receiver_keyword;
                        $colI = substr($item->receiver_key_time, -8);
                        $colM = '01';
                        $colN = '台灣宅配通';
                    } elseif ($item->shipping_method == 2) {
                        $colF = $item->receiver_keyword.'-'.$item->receiver_address;
                        $colM = '02';
                        $colN = '順豐-台灣';
                        foreach ($faraway as $value) {
                            if (strstr($item->receiver_address, $value)) {
                                $colM = '01';
                                $colN = '台灣宅配通';
                                break;
                            }
                        }
                    } elseif ($item->shipping_method == 4) {
                        $colF = $item->receiver_address;
                        if ($item->ship_to == '香港' || ($item->ship_to == '' && strstr($item->receiver_address, '香港'))) {
                            $colM = '02';
                            $colN = '順豐-香港';
                        } elseif ($item->ship_to == '澳門' || ($item->ship_to == '' && strstr($item->receiver_address, '澳門'))) {
                            $colM = '02';
                            $colN = '順豐-澳門';
                        } elseif ($item->ship_to == '台灣' || ($item->ship_to == '' && strstr($item->receiver_address, '台灣'))) {
                            $colM = '02';
                            $colN = '順豐-台灣';
                        } elseif ($item->ship_to == '美國' || $item->ship_to == '加拿大' || $item->ship_to == '澳洲' || $item->ship_to == '紐西蘭' || $item->ship_to == '南韓' || $item->ship_to == '韓國' || ($item->ship_to == '' && (strstr($item->receiver_address, '美國') || strstr($item->eceiver_address, '加拿大') || strstr($item->receiver_address, '澳洲') || strstr($item->receiver_address, '紐西蘭') || strstr($item->receiver_address, '南韓') || strstr($item->receiver_address, '韓國')))) {
                            $colM = '03';
                            $colN = 'DHL';
                        } elseif ($item->ship_to == '日本' || $item->ship_to == '新加坡' || $item->ship_to == '馬來西亞' || $item->ship_to == '法國' || $item->ship_to == '越南' || ($item->ship_to == '' && (strstr($item->ship_to, '日本') || strstr($item->ship_to, '新加坡') || strstr($item->ship_to, '馬來西亞') || strstr($item->ship_to, '法國') || strstr($item->ship_to, '越南')))) {
                            $colM = '04';
                            if (empty($item->ship_to)) {
                                if (strstr($item->receiver_address, '日本')) {
                                    $item->ship_to = '日本';
                                } elseif (strstr($item->receiver_address, '新加坡')) {
                                    $item->ship_to = '新加坡';
                                } elseif (strstr($item->receiver_address, '馬來西亞')) {
                                    $item->ship_to = '馬來西亞';
                                } elseif (strstr($item->receiver_address, '法國')) {
                                    $item->ship_to = '法國';
                                } elseif (strstr($item->receiver_address, '越南')) {
                                    $item->ship_to = '越南';
                                }
                            }
                            $colN = 'LINEX-'.$item->ship_to;
                        } elseif ($item->ship_to == '泰國' || $item->ship_to == '泰國-曼谷' || ($item->ship_to == '' && (strstr($item->receiver_address, '泰國') || strstr($item->receiver_address, '泰國-曼谷')))) {
                            $colM = '05';
                            $colN = '好馬吉';
                        }
                        if ($item->create_type == 'shopee_sg' && strstr($item->user_memo, '新加坡')) {
                            $colM = '04';
                            $colN = 'LINEX-新加坡';
                        }
                        if ($item->create_type == 'momo' || $item->create_type == 'Momo') {
                            $colM = '08';
                            $colN = 'MOMO-宅配通';
                        }
                        if ($item->ship_to == '中國' || strstr($item->receiver_address, '中國')) {
                            $colM = '02';
                            $colN = '順豐-中國';
                        }
                    } elseif ($item->shipping_method == 5) {
                        $colF = str_replace('台灣台灣', '台灣', $item->ship_to.$item->receiver_address);
                        $colM = '02';
                        $colN = '順豐-台灣';
                        if ($item->create_type=='shopee' && strstr($item->user_memo, '台灣') && strstr($item->receiver_address, '7-')) {
                            $colM = '09';
                            $colN = '7-11 大智通';
                        } elseif ($item->create_type=='松果' && strstr($item->receiver_address, '台灣 7-11')) {
                            $colM = '09';
                            $colN = '7-11 大智通';
                        } elseif ($item->create_type=='shopee' && strstr($item->user_memo, '台灣') && strstr($item->receiver_address, '全家')) {
                            $colM = '10';
                            $colN = '全家 日翊';
                        } elseif ($item->create_type=='松果' && strstr($item->receiver_address, '全家')) {
                            $colM = '10';
                            $colN = '全家 日翊';
                        } elseif ($item->create_type=='shopee' && strstr($item->user_memo, '台灣') && strstr($item->receiver_address, '萊爾')) {
                            $colM = '11';
                            $colN = '萊爾富';
                        }
                        if ($item->create_type=='momo' || $item->create_type=='Momo') {
                            $colM = '08';
                            $colN = 'MOMO-宅配通';
                        }
                    } elseif ($item->shipping_method == 6) {
                        $colF = $item->receiver_address;
                        $colM = '02';
                        $colN = '順豐-台灣';
                        foreach ($faraway as $value) {
                            if (strstr($item->receiver_address, $value)) {
                                $colM = '01';
                                $colN = '台灣宅配通';
                                break;
                            }
                        }
                        if ($item->create_type=='shopee' && $item->ship_to=='台灣' && strstr($item->user_memo, '台灣') && strstr($item->receiver_address, '7-')) {
                            $colM = '09';
                            $colN = '7-11 大智通';
                        } elseif ($item->create_type=='松果' && strstr($item->receiver_address, '台灣 7-11')) {
                            $colM = '09';
                            $colN = '7-11 大智通';
                        } elseif ($item->create_type=='shopee' && $item->ship_to=='台灣' && strstr($item->user_memo, '台灣') && strstr($item->receiver_address, '全家')) {
                            $colM = '10';
                            $colN = '全家 日翊';
                        } elseif ($item->create_type=='松果' && strstr($item->receiver_address, '全家')) {
                            $colM = '10';
                            $colN = '全家 日翊';
                        } elseif ($item->create_type=='shopee' && $item->ship_to=='台灣' && strstr($item->user_memo, '台灣') && strstr($item->receiver_address, '萊爾')) {
                            $colM = '11';
                            $colN = '萊爾富';
                        }
                        if ($item->create_type=='momo' && $item->ship_to=='台灣') {
                            $colM = '08';
                            $colN = 'MOMO-宅配通';
                        } elseif ($item->create_type=='Momo' && $item->ship_to=='台灣') {
                            $colM = '08';
                            $colN = 'MOMO-宅配通';
                        }
                    }
                    $colG = $item->user_memo;
                    !empty($item->partner_order_number) ? $colJ = $item->partner_order_number : $colJ = $item->order_number;
                    empty($item->receiver_tel) || $item->receiver_tel=='null' ? $colK = '' : $colK = $item->receiver_name;
                    $colO = $item->receiver_tel;
                    //追加條件 閃購商品對應轉換貨號功能
                    if (!empty($item->origin_digiwin_no)) {
                        $item->digiwin_no=$item->origin_digiwin_no;
                        $item->vendor_name=$item->origin_vendor_name;
                        $item->product_name=$item->origin_product_name;
                    }
                    $colP = $item->digiwin_no;
                    $colQ = "{$item->vendor_name} {$item->product_name}";
                    $colR = $item->quantity;
                    $colS = $item->price;
                    if ($item->status!=-1) {
                        if (!empty($colT)) {
                            $bookShippingDate = substr($colT, 0, 4).'-'.substr($colT, 4, 2).'-'.substr($colT, -2);
                            $order = OrderDB::find($item->order_id);
                            if (empty($order->book_shipping_date)) {
                                $order->update(['book_shipping_date' => $bookShippingDate]);
                                $log = OrderLogDB::create([
                                    'order_id' => $item->order_id,
                                    'admin_id' => auth()->user()->id,
                                    'column_name' => 'book_shipping_date',
                                    'log' => $bookShippingDate,
                                ]);
                            }
                        }
                    }
                    $colU = $item->gross_weight * $item->quantity;
                    strstr($item->shipping_memo, '廠商發貨') ? $colW = 'W02' : $colW = 'W01';
                    $data[$i] = [$colA,$colB,$colC,$colD,$colE,$colF,$colG,$colH,$colI,$colJ,$colK,$colL,$colM,$colN,$colO,$colP,$colQ,$colR,$colS,$colT,$colU,$colV,$colW];
                    if ($nowOrderNumber != $item->order_number) {
                        if ($item->discount != 0) {//
                            $i++;
                            $data[$i] = [$colA,$colB,$colC,$colD,$colE,$colF,$colG,$colH,$colI,$colJ,$colK,$colL,$colM,$colN,$colO,'999000','活動折抵',1,$item->discount * -1, $colT, '', $colV, 'W07'];
                        }
                        if ($item->spend_point > 0) {//購物金
                            $i++;
                            $data[$i] = [$colA,$colB,$colC,$colD,$colE,$colF,$colG,$colH,$colI,$colJ,$colK,$colL,$colM,$colN,$colO,'999001','購物金',1,$item->spend_point * -1, $colT, '', $colV, 'W07'];
                        }
                        if ($item->shipping_fee > 0) {//運費
                            $i++;
                            $data[$i] = [$colA,$colB,$colC,$colD,$colE,$colF,$colG,$colH,$colI,$colJ,$colK,$colL,$colM,$colN,$colO,'901001','運費',1,$item->shipping_fee * -1, $colT, '', $colV, 'W07'];
                        }
                        if ($item->parcel_tax) {//行郵稅
                            $i++;
                            $data[$i] = [$colA,$colB,$colC,$colD,$colE,$colF,$colG,$colH,$colI,$colJ,$colK,$colL,$colM,$colN,$colO,'901002','行郵稅',1,$item->parcel_tax * -1, $colT, '', $colV, 'W07'];
                        }
                        $nowOrderNumber = $item->order_number;
                    }
                    $i++;
                }
            }
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('O')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('P')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('J')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('O')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('P')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return '訂單資料-鼎新匯出';
    }

    public function headings(): array
    {
        return [
            '訂單日期',
            '客戶代號',
            '客戶代號簡稱',
            '部門代號',
            '部門代號簡稱',
            '送貨地址（飯店名稱-飯店地址）',
            '備註',
            'TEL 班機',
            'FAX 機場提貨時間',
            '網路訂單編號',
            '收件人',
            '指定日期（提貨日）',
            '貨運別',
            '貨運名稱',
            '行動電話（收件人）',
            '品號',
            '品名',
            '數量',
            '單價',
            '預交日',
            '毛重（kg）依訂單',
            '定金比例',
            '倉別'
        ];
    }
}
