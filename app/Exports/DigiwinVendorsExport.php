<?php

namespace App\Exports;

use App\Models\Vendor as VendorsDB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DigiwinVendorsExport implements FromCollection,WithProperties,ShouldAutoSize,WithStrictNullComparison, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $vendors = VendorsDB::orderBy('id','asc')->get();
        foreach($vendors as $vendor){
            $data[] = [
                'A'.str_pad($vendor->id,5,"0",STR_PAD_LEFT),
                $vendor->name,
                $vendor->company,
                $vendor->vat_number,
                $vendor->tel,
                $vendor->fax,
                $vendor->email,
                $vendor->boss,
                $vendor->contact,
                $vendor->address,
                $vendor->factory_address,
            ];
        }
        return collect($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#');
        $sheet->getStyle('D')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        //參數參考連結
        //https://phpspreadsheet.readthedocs.io/en/latest/topics/recipes/#styles

    }

    public function headings(): array
    {
        return [
            '廠商代號',
            '簡稱',
            '公司全名',
            '統一編號',
            'TEL(一)',
            'FAXNO.',
            'E-MAIL',
            '負責人',
            '聯絡人(一)',
            '聯絡地址(一)',
            '聯絡地址(二)',
        ];
    }

    public function properties(): array
    {
        return [
            'creator'        => 'iCarry系統管理員',
            'lastModifiedBy' => 'iCarry系統管理員',
            'title'          => 'iCarry後台管理-鼎新資料處理_供應商匯出',
            'description'    => 'iCarry後台管理-鼎新資料處理_供應商匯出',
            'subject'        => 'iCarry後台管理-鼎新資料處理_供應商匯出',
            'keywords'       => '',
            'category'       => '',
            'manager'        => 'iCarry系統管理員',
            'company'        => 'iCarry.me 直流電通股份有限公司',
        ];
    }
}
