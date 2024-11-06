<?php

namespace App\Imports;

use App\Models\Order as OrderDB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class InvoicesImport implements ToCollection
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        for($i=1;$i<count($rows);$i++){
            OrderDB::where('order_number',$rows[$i][0])->update([
                'is_invoice_no' => $rows[$i][2],
                'is_invoice' => 1,
                'is_invoice_cancel' => 1,
                'invoice_memo' => '舊:'.$rows[$i][1],
            ]);
        }
    }
}
