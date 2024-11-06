<?php

namespace App\Imports;

use App\Models\DigiwinImportOrder as DigiwinImportOrderDB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DigiwinOrderImport implements ToModel, WithStartRow, WithMultipleSheets
{
    public function __construct($param)
    {
        $this->param = $param;
    }

    public function model(array $row)
    {
        $chk = MomoOrderDB::where('order_number', $row[7])->first();
        if (empty($chk)) { //檢查是否不存在避免重複匯入
            return new DigiwinImportOrderDB([
                'order_number' => $row[7],
                'colA' => $row[0],
                'colB' => $row[1],
                'colC' => $row[2],
                'colD' => $row[3],
                'colE' => $row[4],
                'colF' => $row[5],
                'colG' => $row[6],
                'colH' => $row[7],
                'colI' => $row[8],
                'colJ' => $row[9],
                'colK' => $row[10],
                'colL' => $row[11],
                'colM' => $row[12],
                'colN' => $row[13],
                'colO' => $row[14],
                'colP' => $row[15],
                'colQ' => $row[16],
                'colR' => $row[17],
                'created_at' => $this->param['created_at'],
            ]);
        }
    }

    /** 從第二行開始
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
}
