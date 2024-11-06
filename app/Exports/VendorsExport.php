<?php

namespace App\Exports;

use App\Models\iCarryVendor as VendorsDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorsExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $vendors = VendorsDB::orderBy('is_on','desc')->get();

        foreach($vendors as $vendor){
            if($vendor->service_fee){
                $serviceFees=collect(json_decode(str_replace('"percent":}','"percent":0}',$vendor->service_fee)));
            }else{
                $serviceFees = collect(json_decode('[{"name":"天虹","percent":0},{"name":"閃店","percent":0},{"name":"iCarry","percent":0},{"name":"現場提貨","percent":0}]'));
            }
            for($j=0;$j<count($serviceFees);$j++){
                $fee[$j]=$serviceFees[$j]->percent ? $serviceFees[$j]->percent : '0';
            }
            $data[] = [
                $vendor->name,
                $vendor->company,
                $vendor->vat_number,
                $vendor->boss,
                $vendor->contact,
                $vendor->tel,
                $vendor->fax,
                $vendor->email,
                $vendor->address,
                $fee[0],
                $fee[1],
                $fee[2],
                $fee[3],
                'A'.str_pad($vendor->id,5,"0",STR_PAD_LEFT),
                $vendor->is_on == 1 ? '啟用' : '停用'
            ];
        }
        return collect($data);
    }

    public function headings(): array
    {
        return [
            '店名/品牌',
            '公司名稱',
            '統一編號',
            '負責人',
            '聯絡人',
            '電話',
            '傳真',
            '電子郵件',
            '地址',
            '天虹服務費',
            '閃店服務費',
            'iCarry服務費',
            '現場提貨服務費',
            '店家代號',
            '啟用/停用',
        ];
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-商家資料匯出',
            'description'    => 'iCarry後台管理-商家資料匯出',
            'subject'        => 'iCarry後台管理-商家資料匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
