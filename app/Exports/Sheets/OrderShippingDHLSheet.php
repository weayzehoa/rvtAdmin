<?php

namespace App\Exports\Sheets;

use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\CompanySetting as CompanySettingDB;
use App\Models\Country as CountryDB;
use App\Models\UserAddress as UserAddressDB;
use App\Models\ProductLang as ProductLangDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use DB;
use App\Traits\OrderExportFunctionTrait;

class OrderShippingDHLSheet implements FromCollection,WithStrictNullComparison,WithStyles,WithTitle,ShouldAutoSize,WithHeadings
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
            $i = 0;
            $setWordColor = [];

            foreach ($orders as $order) {
                $items = $order->items;
                $totalGrossWeight = 0;
                $totalPrice = 0;
                foreach ($items as $item) {
                    if($item->category_id == 3 || $item->vendor_id == 235 || $item->vendor_id == 228 || $item->vendor_id == 190){
                        $price = number_format($item->price * 0.033 * 0.3,2);
                        $totalPrice += $item->quantity * $price;
                    }else{
                        $price = number_format($item->price * 0.033 * 0.5,2);
                        $totalPrice += $item->quantity * $price;
                    }
                    $totalGrossWeight += $item->gross_weight;
                }
                $data1[$i] = [
                    $order->order_number,
                    '',
                    '1',
                    '',
                    '',
                    '',
                    ceil($totalGrossWeight/1000),
                    $totalPrice,
                    'USD',
                    $order->order_number,
                ];
                $tmp = [];
                $c = 0;
                foreach($items as $item){
                    if($item->category_id == 3 || $item->vendor_id == 235 || $item->vendor_id == 228 || $item->vendor_id == 190){
                        $price = $item->price * 0.033 * 0.3;
                    }else{
                        $price = $item->price * 0.033 * 0.5;
                    }
                    $item->product_eng_name = ProductLangDB::where([['product_id',$item->product_id],['lang','en']])->select('name')->first();
                    if(!empty($item->product_eng_name)){
                        $item->product_name = $item->product_eng_name->name;
                    }else{
                        $setWordColor[$i] = $c + 1;
                    }
                    $tmp2 = [$price,$item->product_name,$item->quantity];
                    $tmp = array_merge($tmp,$tmp2);
                    $c++;
                    if($c == 10){
                        break;
                    };
                }
                //補空白欄位
                $x = 10 - count($items);
                if($x >= 1){
                    for ($j=1; $j <= $x ; $j++) {
                        $tmp3 = ['','',''];
                        $tmp = array_merge($tmp,$tmp3);
                    }
                }
                $data2[$i] = $tmp;
                $data3[$i] = [
                    $order->receiver_name,
                    $order->receiver_tel,
                    '',
                    $order->receiver_address,
                    '',
                    '',
                    strtoupper($order->receiver_country),
                    '',
                    '',
                    $order->zip_code,
                    '',
                    'N'
                ];
                $data[$i] = array_merge(array_merge($data1[$i],$data2[$i]),$data3[$i]);
                $i++;
            }
            $this->setWordColor = $setWordColor;
            $this->count = $count = count($data);
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('#');
        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return 'DHL';
    }

    public function headings(): array
    {
        $head1 = [
            '商業發票號碼*',
            '寄件人員',
            '包裹數量*',
            '長',
            '寬',
            '高',
            '包裹重量*',
            '海關總值*',
            '幣別*',
            '提單貨件參考資料',
        ];
        for($i=1; $i<=10; $i++){
            if($i==1){
                $head2[] = '報關商品單價'.$i.'*';
                $head2[] = '報關商品說明'.$i.'*';
                $head2[] = '報關商品數量'.$i.'*';
            }else{
                $head2[] = '報關商品單價'.$i;
                $head2[] = '報關商品說明'.$i;
                $head2[] = '報關商品數量'.$i;
            }
        }
        $head3 = [
            '收件聯絡人*',
            '收件人聯絡電話*',
            '收件人公司名稱',
            '收件人地址(一)*',
            '收件人地址(二)',
            '收件人地址(三)',
            '收件人國家代碼*',
            '收件人州/省',
            '收件人城市*',
            '收件人郵政編碼*',
            '單位',
            'DTP'
        ];
        return $head = array_merge(array_merge($head1,$head2),$head3);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 15,
            'D' => 16,
            'E' => 45,
            'F' => 10,
            'G' => 15,
            'H' => 20,
            'I' => 15,
            'J' => 50,
            'K' => 15,
            'L' => 15,
            'M' => 15,
            'N' => 50,
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
            'AD' => 15,
            'AE' => 10,
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
            $receiver_tel_tmp = substr($receiver_tel,0,'-'.$str_tmp);
            if($receiver_tel_tmp=='886'){//就是香港
                $receiver_tel = substr($receiver_tel,3);//擷取完畢
            }
        $receiver_tel  = str_replace(' ','',$receiver_tel);//去除有空格的(有些+XXX 的問題)
        return $receiver_tel;
    }
}
