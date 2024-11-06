<?php

namespace App\Exports\Sheets;

use App\Models\UserAddress as UserAddressDB;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\CompanySetting as CompanySettingDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ProductPackage as ProductPackageDB;
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

class OrderShippingWarehousingPreDeliverySheet implements FromCollection,WithStrictNullComparison,WithStyles,WithTitle,WithHeadings,ShouldAutoSize
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
        $orderIds = $this->getOrderData($this->param);
        if(!empty($orderIds)){
            $items = OrderItemDB::join('orders','orders.id','order_items.order_id')
                ->join('product_models','product_models.id','order_items.product_model_id')
                ->join('products','products.id','product_models.product_id')
                ->whereIn('order_id',$orderIds)
                ->select([
                    'product_models.sku',
                    'product_models.gtin13',
                    'order_items.product_id',
                    'order_items.product_model_id',
                    'order_items.vendor_name',
                    'order_items.product_name',
                    'products.serving_size',
                    'products.model_type',
                    'orders.book_shipping_date',
                    DB::raw("(IF(orders.shipping_memo LIKE '%廠商發貨%','V','')) as vendor_send"),
                    // DB::raw("GROUP_CONCAT(orders.book_shipping_date) as shippingDate"), //驗證測試用
                    DB::raw("SUM((CASE WHEN order_items.is_call is null THEN order_items.quantity ELSE 0 END)) as quantity"),
                ])->groupBy('sku','book_shipping_date','vendor_send')
                ->orderBy('product_models.sku','asc')
                ->orderBy('vendor_send','asc')
                ->orderBy('orders.book_shipping_date','asc')
                ->withTrashed()->get();
            $c = 1;
            foreach ($items as $item) {
                if($item->quantity > 0){
                    if($item->model_type == 3){ //組合商品需要另外抓item資料
                        $packageItems = ProductPackageDB::join('product_package_lists','product_package_lists.product_package_id','product_packages.id')
                            ->where([['product_packages.product_id',$item->product_id],['product_packages.product_model_id',$item->product_model_id]])->get();
                        foreach($packageItems as $packItem){
                            $quantity = $item->quantity * $packItem->quantity;
                            $pack = ProductModelDB::join('products','products.id','product_models.product_id')
                                ->join('vendors','vendors.id','products.vendor_id')
                                ->select([
                                    'product_models.sku',
                                    'product_models.gtin13',
                                    'vendors.name as vendor_name',
                                    'products.name as product_name',
                                    'products.serving_size',
                                ])->find($packItem->product_model_id);
                            $data[] = [
                                $item->vendor_send,
                                $c,
                                $pack->sku,
                                $pack->gtin13 ?? $pack->sku,
                                $pack->vendor_name,
                                $pack->product_name,
                                $pack->serving_size,
                                $quantity,
                                '',
                                '預計日期: '.$item->book_shipping_date,
                            ];
                            $c++;
                        }
                    }else{
                        $data[] = [
                            $item->vendor_send,
                            $c,
                            $item->sku,
                            $item->gtin13 ?? $item->sku,
                            $item->vendor_name,
                            $item->product_name,
                            $item->serving_size,
                            $item->quantity,
                            '',
                            '預計日期: '.$item->book_shipping_date,
                        ];
                        $c++;
                    }
                }
            }
        }
        $this->count = $c;
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $count = $this->count + 2;
        $sheet->mergeCells('A1:I1'); //合併第一行A-I
        $sheet->getStyle('A1:I1')->getFont()->setSize(20)->setBold(true); //第一行字型大小
        $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        for($i=1; $i<$count; $i++){
            $sheet->getStyle("A$i:J$i")->getBorders()->getAllBorders() //框線
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return '商品入庫明細表';
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
                '【直流電通】 商品入庫管理表',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '日期: '.date('Y/m/d'),
            ],
            [
                '廠商發貨',
                '序號',
                '商品條碼(貨號)',
                '參考號(國際條碼)',
                '廠商',
                '商品名稱',
                '商品規格',
                '預收數量',
                '有效日期',
                '備註(預定出貨日）',
            ]
        ];
    }
}

