<?php

namespace App\Exports\Sheets;

use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\OrderExportFunctionTrait;

class OrderDetailItemSheet implements FromCollection,WithStrictNullComparison, WithHeadings,WithStyles,WithTitle
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
                $order->is_call || $order->is_call != 0 ? $order->is_call = substr($order->is_call, 0, 4)."/".substr($order->is_call, 4, 2)."/".substr($order->is_call, 6, 2) : $order->is_call = '';
                $order->is_print || $order->is_print != 0 ? $order->is_print = substr($order->is_print, 0, 4)."/".substr($order->is_print, 4, 2)."/".substr($order->is_print, 6, 2) : $order->is_print = '';
                if ($order->items) {
                    foreach ($order->items as $orderItem) {
                        $orderItem->is_call || $orderItem->is_call != 0 ? $orderItem->is_call = substr($orderItem->is_call, 0, 4)."/".substr($orderItem->is_call, 4, 2)."/".substr($orderItem->is_call, 6, 2) : $orderItem->is_call = '';
                        $orderItem->is_print || $orderItem->is_print != 0 ? $orderItem->is_print = substr($orderItem->is_print, 0, 4)."/".substr($orderItem->is_print, 4, 2)."/".substr($orderItem->is_print, 6, 2) : $orderItem->is_print = '';
                        if (!empty($this->param['vendor_id'])) { //商家後台
                            if ($orderItem->vendor_id == $this->param['vendor_id']) {
                                $data[] = [
                                    $order->order_number,
                                    $order->user['name'],
                                    $order->created_at,
                                    $orderItem->product_name,
                                    $orderItem->unit_name,
                                    $orderItem->quantity,
                                    $orderItem->price,
                                    $orderItem->quantity * $orderItem->price,
                                    $order->shippingMethod['name'],
                                ];
                            }
                        } else {
                            $data[] = [
                                $order->order_number,
                                $order->user['name'],
                                $this->country($order->receiver_address, $order->shipping_method, $order->receiver_tel),
                                $order->created_at,
                                $orderItem->vendor_name,
                                $orderItem->product_name,
                                $order->shipping_fee,
                                $orderItem->unit_name,
                                $orderItem->quantity,
                                $orderItem->price,
                                $orderItem->quantity * $orderItem->price,
                                $order->shippingMethod['name'],
                                $order->receiver_key_time,
                                $order->user_memo,
                                $orderItem->is_call,
                                $order->is_print,
                            ];
                        }
                    }
                }
            }
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A')->getNumberFormat()->setFormatCode('#');
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);
        $sheet->getColumnDimension('K')->setAutoSize(true);
        $sheet->getColumnDimension('L')->setAutoSize(true);
        $sheet->getColumnDimension('M')->setAutoSize(true);
        $sheet->getColumnDimension('N')->setAutoSize(true);
        $sheet->getColumnDimension('P')->setWidth(30);
        $sheet->getStyle('P')->getAlignment()->setWrapText(true);
        $sheet->getColumnDimension('Q')->setAutoSize(true);
        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return '商品資料';
    }

    public function headings(): array
    {
        if(!empty($this->param['vendor_id'])){ //商家後台
            $headings = [
                '訂單編號',
                '訂購者',
                '訂單日期',
                '商品名稱',
                '單位',
                '數量',
                '單價',
                '總價',
                '物流方式',
            ];
        }else{
            $headings = [
                '訂單編號',
                '訂購者',
                '國別',
                '訂單日期',
                '商家名稱',
                '商品名稱',
                '運費',
                '單位',
                '數量',
                '單價',
                '總價',
                '收件人姓名',
                '收件人電話',
                '物流方式',
                '提貨時間',
                '訂單備註',
                '已叫貨',
                '已列印',
            ];
        }
        return $headings;
    }
}
