<?php

namespace App\Exports;

use DB;
use App\Models\iCarryProductPackageList as ProductPackageListDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductPackagesExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $lists = ProductPackageListDB::with('package','package.model','model')->orderBy('created_at','desc')->get();

        foreach($lists as $list){
            $listModel = $list->model;
            if(!empty($list->package)){
                $package = $list->package;
                if(empty($package->deleted_at)){
                    if(!empty($package->model)){
                        $model = $package->model;
                        $diginwinNo = $model->digiwin_no;
                        if(!empty($diginwinNo)){
                            $data[] = [
                                $diginwinNo, '', '', '組', '1', '', '', '104', 'W01',
                                $listModel->digiwin_no, '', '', '', 'P', $list->quantity, '1', '', '', '104', 'W01'
                            ];
                        }
                    }
                }
            }
        }
        return collect($data);
    }

    public function headings(): array
    {
        $headings[0]=['主件品號','主件品名','主件規格','主件單位','標準批量','製令單別','單頭備註','品號分類(一)','主要庫別','材料品號','材料品名','材料規格','材料單位','屬性','組成用量','底數','製程','單身備註','品號分類(一)','主要庫別'];
        $headings[1]=['C(20)','C(120)','C(120)','C(4)','','C(4)','V(255)','C(6)','C(10)','C(20)','C(120)','C(120)','C(4)','C(1)','','','C(4)','V(255)','C(6)','C(10)'];
        $headings[2]=['','','','','','允許空白','允許空白','原料、物料、半成品','','','','','','採購件、自製件、託外加工件','必須大於等於1','必須大於等於1','允許空白','允許空白','原料、物料、半成品',''];
        return $headings;
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-組合商品資料匯出',
            'description'    => 'iCarry後台管理-組合商品資料匯出',
            'subject'        => 'iCarry後台管理-組合商品資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
