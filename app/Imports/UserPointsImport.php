<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class UserPointsImport implements ToCollection,WithStartRow,WithMultipleSheets,WithLimit,WithColumnLimit,WithColumnFormatting
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
    }
    /** 從第2行開始
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

    /*
     * 只取第一個sheets
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    //限制讀取數量
    public function limit(): int
    {
        return 10000;
    }

    //限制讀取欄位
    public function endColumn(): string
    {
        //A到B欄, 3欄
        return 'C';
    }

    //轉換格式為文字
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT
        ];
    }
}