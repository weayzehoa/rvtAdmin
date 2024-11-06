<?php

namespace App\Exports;

use App\Models\Order as OrderDB;
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

class InvoicesExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings,WithColumnWidths,WithStyles,WithTitle
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
        $orders = OrderDB::with('user','shippingMethod')->whereIn('id',$param['id'])->orderBy('created_at','desc')->get();
        $isInvoiceText = [ 0 => '未開立', 1 => '已開立', 2 => '已作廢'];
        $statusText = [ -1 => '訂單取消', 0 => '尚未付款', 1 => '已付款待出貨', 2 => '訂單集貨中', 3 => '訂單出貨中', 4 => '訂單已完成'];
        $invoiceTypeText = [ 2 => '二聯式', 3 => '三聯式'];
        foreach($orders as $order){
            //invoice_title與invoice_number相反時(抬頭與統編)
            if (is_numeric($order->invoice_title) && !is_numeric($order->invoice_number)) {
                $tmp = $order->invoice_title;
                $order->invoice_title=$order->invoice_number;
                $order->invoice_number=$tmp;
            }
            $order->pay_total = $order->amount + $order->shipping_fee + $order->parcel_tax - $order->discount - $order->spend_point;
            $data[] = [
                $order->is_invoice_no,
                $isInvoiceText[$order->is_invoice],
                $order->order_number,
                $statusText[$order->status],
                $order->user_id,
                $order->user->name,
                $order->created_at,
                $order->shippingMethod->name,
                $order->pay_method,
                $order->pay_total,
                $invoiceTypeText[$order->invoice_type],
                $order->buyer_name,
                $order->invoice_number,
                $order->invoice_title,
                $order->invoice_address,
            ];
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('N:O')->getAlignment()->setWrapText(true);

        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles

    }

    public function title(): string
    {
        return 'iCarry 我來寄 發票資料';
    }

    public function headings(): array
    {
        return [
            '發票號碼',
            '開立狀態',
            '訂單編號',
            '訂單狀態',
            '購買者id',
            '購買人',
            '建單日期',
            '物流',
            '金流',
            '消費者付款金額',
            '收據種類',
            '發票收受人',
            '收據統一編號',
            '收據抬頭',
            '收據寄送地址',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 10,
            'C' => 18,
            'D' => 18,
            'E' => 10,
            'F' => 20,
            'G' => 20,
            'H' => 12,
            'I' => 15,
            'J' => 20,
            'K' => 10,
            'L' => 20,
            'M' => 15,
            'N' => 30,
            'O' => 30,
        ];
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-發票資料匯出',
            'description'    => 'iCarry後台管理-發票資料匯出',
            'subject'        => 'iCarry後台管理-發票資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
