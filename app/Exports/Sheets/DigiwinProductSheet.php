<?php

namespace App\Exports\Sheets;

use App\Models\iCarryCountry as CountryDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use App\Traits\ProductFunctionTrait;

class DigiwinProductSheet implements FromCollection,ShouldAutoSize,WithStrictNullComparison, WithHeadings, WithTitle, WithStyles
{
    use ProductFunctionTrait;
    protected $param;

    public function __construct(array $param)
    {
        $this->param = $param;
    }

    public function collection()
    {
        $products = $this->getProductExportData($this->param);
        // dd($products);
        $i=0;
        $setColor = null;
        $data = null;
        foreach($products as $product){
            if(!empty($product->service_fee)){
                $product->service_fee = str_replace(":}",":0}",$product->service_fee); //補0, 正常遷移時應該已經補了
                $serviceFee = json_decode($product->service_fee,true);
                if(is_array($serviceFee)){
                    foreach($serviceFee as $value){
                        if($value['name']=="iCarry"){
                            $serviceFeePercent = $value['percent'];
                            break;
                        }
                    }
                }
            }
            $buyPrice = 0;
            $flag=false;
            if(empty($product->vendor_price) || $product->vendor_price == 0){
                $buyPrice = ($product->price - $product->TMS_price) * ((100-$serviceFeePercent)/100) + $product->TMS_price;
                $buyPrice = round($buyPrice);
                $flag=true;
            }else{
                $buyPrice = $product->vendor_price;
                $flag=false;
            }
            $buyPrice = round($buyPrice/1,2);
            if(empty($buyPrice) || $flag == true){
                $setColor[] = $i;
            }
            $data[$i] = [
                $product->digiwin_no,
                $product->product_name,
                $product->serving_size,
                $product->unit_name,
                $product->gtin13,
                $product->storage_life,
                'A'.str_pad($product->vendor_id,5,'0',STR_PAD_LEFT),
                round($buyPrice/1.05,2),
            ];
            $i++;
        }
        $this->setColor = $setColor;
        return collect($data);
    }


    public function styles(Worksheet $sheet)
    {
        $setColor = $this->setColor;
        $backgroundColor = [
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'FFFF00'],
        ];
        if(!empty($setColor)){
            for ($i=0; $i < count($setColor) ; $i++) {
                $sheet->getStyle('H'.($setColor[$i]+1))->getFill()->applyFromArray($backgroundColor);
            }
        }
        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles
    }

    public function title(): string
    {
        return '匯出鼎新(啟用廠商)';
    }

    public function headings(): array
    {
        return [
            '品號(MB001)',
            '品名(MB002)',
            '規格(MB003)',
            '庫存單位(MB004)',
            '條碼編號(MB013)',
            '有效天數(MB023)',
            '主供應商(MB032)',
            '標準進價(MB046)',
        ];
    }
}



