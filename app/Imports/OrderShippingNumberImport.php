<?php

namespace App\Imports;

use App\Models\Order as OrderDB;
use App\Models\OrderShipping as OrderShippingDB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrderShippingNumberImport implements ToCollection, WithStartRow, WithMultipleSheets
{
    public function collection(Collection $rows)
    {
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
