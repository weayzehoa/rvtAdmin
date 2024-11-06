<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;

use App\Exports\Sheets\EmptySheet;
use App\Exports\Sheets\ProductDetailExportSheet;
use App\Exports\Sheets\DigiwinProductSheet;
use App\Exports\Sheets\ProductDigiwinNoSheet;

use App\Traits\ProductExportFunctionTrait;

class ProductExport implements WithMultipleSheets, WithProperties
{
    use ProductExportFunctionTrait;
    protected $param;

    public function __construct(array $param)
    {
        $this->param = $param;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $param = $this->param;
        $sheets = [];
        if(!empty($param['cate'])){
            if ($param['cate'] == 'excel'){
                $param['type'] == 'ProductDetail' ? $sheets = [ new ProductDetailExportSheet($this->param) ] : '';
                $param['type'] == 'DigiwinProduct' ? $sheets = [ new DigiwinProductSheet($this->param) ] : '';
                $param['type'] == 'DigiwinNo' ? $sheets = [ new ProductDigiwinNoSheet($this->param) ] : '';
            }
        }else{
            $sheets = [new EmptySheet()];
        }
        return $sheets;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-商品資料匯出',
            'description'    => 'iCarry後台管理-商品資料匯出',
            'subject'        => 'iCarry後台管理-商品資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
