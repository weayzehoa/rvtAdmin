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

class OrderShippingSFHandwriteSheet implements FromCollection,WithStrictNullComparison,WithStyles,WithTitle,WithHeadings,ShouldAutoSize,WithColumnWidths
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
        //偏鄉陣列設定
        $outlyingIsland = ['澎湖','金門','馬祖','綠島','蘭嶼','小琉球'];
        $city = ['新北市','桃園市','新竹縣','苗栗縣','臺中巿','台中市','彰化縣','南投縣','嘉義縣','雲林縣','臺南市','台南市','高雄市','屏東縣','宜蘭縣','花蓮縣','臺東縣','台東縣'];
        $area = [
            '新北市' => ['萬里區','金山區','平溪區','雙溪區','貢寮區','坪林區','烏來區','三芝區','石門區','石碇區'],
            '桃園市' => ['復興區'],
            '新竹縣' => ['關西鎮','芎林鄉','寶山鄉','五峰鄉','橫山鄉','尖石鄉','北埔鄉','峨嵋鄉'],
            '苗栗縣' => ['南庄鄉','獅潭鄉','大湖鄉','泰安鄉','卓蘭鎮'],
            '臺中巿' => ['東勢區','和平區','新社區'],
            '台中巿' => ['東勢區','和平區','新社區'],
            '彰化縣' => ['溪州鄉','竹塘鄉','二林鄉','大城鄉'],
            '南投縣' => ['中寮鄉','國姓鄉','仁愛鄉','名間鄉','集集鎮','水里鄉','魚池鄉','信義鄉','竹山鎮','鹿谷鄉'],
            '嘉義縣' => ['番路鄉','梅山鄉','阿里山鄉','大埔鄉','東石鄉','義竹鄉','布袋鎮'],
            '雲林縣' => ['褒忠鄉','東勢鄉','台西鄉','麥寮鄉','二崙鄉','北港鎮','水林鄉','口湖鄉','四湖鄉','元長鄉'],
            '臺南市' => ['左鎮區','玉并區','楠西區','南化區','龍崎區','七股區','將軍區','北門區','白河區','東山區','大內區'],
            '台南市' => ['左鎮區','玉并區','楠西區','南化區','龍崎區','七股區','將軍區','北門區','白河區','東山區','大內區'],
            '高雄市' => ['田寮區','六龜區','內門區','那瑪夏區','茂林區','桃源區','甲仙區','杉林區'],
            '屏東縣' => ['恆春鄉','枋寮鄉','三地門鄉','來義鄉','東港鄉','獅子鄉','牡丹鄉','林邊鄉','高樹鄉','枋山鄉','里港鄉','滿州鄉','車城鄉','鹽埔鄉','九如鄉','萬巒鄉','新園鄉','新埤鄉','春日鄉','霧臺鄉','霧台鄉','佳冬鄉','竹田鄉','瑪家鄉','泰武鄉','南州鄉','崁頂鄉','瑪家鄉','麟洛鄉'],
            '宜蘭縣' => ['冬山鄉','三星鄉','大同鄉','南澳鄉'],
            '花蓮縣' => ['光復鄉','玉里鎮','新城鄉','壽豐鄉','鳳林鎮','豐濱鄉','瑞穗鄉','富里鄉','萬榮鄉','卓溪鄉','秀林鄉'],
            '台東縣' => ['成功鎮','關山鎮','卑南鄉','大武鄉','太麻里鄉','東河鄉','長濱鄉','鹿野鄉','池上鄉','延平鄉','海端鄉','達仁鄉','金峰鄉'],
            '臺東縣' => ['成功鎮','關山鎮','卑南鄉','大武鄉','太麻里鄉','東河鄉','長濱鄉','鹿野鄉','池上鄉','延平鄉','海端鄉','達仁鄉','金峰鄉'],
        ];
        $data = [];
        $orders = $this->getOrderData($this->param);
        if(!empty($orders)){
            $c = 3;
            $i = 1;
            $setWordColor = [];
            foreach ($orders as $order) {
                $shippings = $order->shippings;
                $items = $order->items;
                $expressWay = null;
                $expressNo = null;
                $set = 0;
                if(!empty($shippings)){
                    foreach($shippings as $shipping){
                        $expressWay = join(',',$shipping->express_way);
                        $expressNo = join(',',$shipping->express_no);
                    }
                }
                $memo  = $order->user_memo;
                !empty($order->admin_memo) ? $memo  .= '/'.$order->admin_memo : '';
                //偏鄉地區地址欄位字體顏色改為紅色
                for($x=0;$x<count($outlyingIsland);$x++){
                    if(strstr($order->receiver_address, $outlyingIsland[$x])){
                        $set = 1;
                        break;
                    }else{
                        for($y=0; $y<count($city);$y++){
                            if (strstr($order->receiver_address, $city[$y])) {
                                foreach($area as $key => $value){
                                    if($key == $city[$y]){
                                        for($v=0;$v<count($value);$v++){
                                            if(strstr($order->receiver_address,$value[$v])){
                                                $set = 1;
                                                break 4;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                foreach ($items as $item) {
                    $price = null;
                    $totalPrice = null;
                    if($order->shipping_method == '4'){  //只有寄送海外訂單需顯示
                        $price = $item->price;
                        if(strstr($order->receiver_address, '日本')){
                            $totalPrice = $order->amount + $order->shipping_fee + round($order->pracel_tax) - $order->discount - $order->spend_point;
                        }else{
                            $totalPrice = $order->amount + $order->shipping_fee + round($order->pracel_tax);
                        }
                    }
                    $data[] = [
                        $order->order_number,
                        $expressWay,
                        $expressNo,
                        $order->receiver_name,
                        $order->tel,
                        $order->receiver_address,
                        $item->sku,
                        $item->gtin13,
                        $item->vendor_name,
                        $item->product_name,
                        $item->quantity,
                        $price,
                        $totalPrice,
                        $memo,
                        '',
                        '',
                    ];
                    //訂單底色
                    if($i%2 != 0){
                        $setBGColor[] = $c;
                    }
                    $set == 1 ? $setWordColor[] = $c : '';
                    $c++;
                }
                $i++;
            }
        }
        $this->setBGColor = $setBGColor;
        $this->setWordColor = $setWordColor;
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $setBGColor = $this->setBGColor;
        $setWordColor = $this->setWordColor;
        if(!empty($setBGColor)){
            for ($i=0; $i < count($setBGColor) ; $i++) {
                $sheet->getStyle('A'.($setBGColor[$i]).':Z'.($setBGColor[$i]))->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('D3D3D3');
            }
        }
        if(!empty($setWordColor)){
            for ($i=0; $i < count($setWordColor) ; $i++) {
                $sheet->getStyle('F'.$setWordColor[$i])->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
            }
        }

        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('A')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('H')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('H')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $borderStyle = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:P1')->getBorders()->applyFromArray($borderStyle);//底線

        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return '寄送台灣-順豐';
    }
    public function columnWidths(): array
    {
        $highestColumn = 'Z';
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
                '單號不可重覆', //A1
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '單價金額', //L1
                '',
                '',
                '是否次品Y或N', //O1
            ],
            [
                '訂單號碼',
                '物流公司',
                '運單號',
                '收件人',
                '電話',
                '地址',
                '商品編號',
                '條碼',
                '廠商',
                '商品名稱',
                '商品出庫數量',
                '價格',
                '訂單總金額',
                '訂單備註',
                '庫存狀態',
                '商品效期',
            ],
        ];
    }
}

