<?php

namespace App\Imports;

use App\Models\MomoOrder as MomoOrderDB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MomoOrderImport implements ToModel, WithStartRow, WithMultipleSheets
{
    public function __construct($param)
    {
        $this->param = $param;
    }

    public function model(array $row)
    {
        $tmp = explode("-",$row[5]);
        $orderNumber = $tmp[0];
        $chk = MomoOrderDB::where('order_number',$orderNumber)->first();
        if(empty($chk)){ //檢查是否不存在避免重複匯入
            return new MomoOrderDB([
                'order_number' => $orderNumber,
                'colF' => $row[5],
                'colG' => $row[6],
                'colH' => $row[7],
                'colM' => $row[12],
                'colN' => $row[13],
                'colO' => $row[14],
                'colT' => $row[19],
                'colU' => $row[20],
                'all_cols' => json_encode($row),
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
