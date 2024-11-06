<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ChangePriceImport;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryPriceChangeRecord as PriceChangeRecordDB;
use DateTime;

class ChangePriceFileImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = [];
        $total = $fail = $success = 0;
        $message = null;
        $param = $this->param;
        $importData = Excel::toArray(new ChangePriceImport, $param['filename']);
        $results = $importData[0];
        for($i=0;$i<count($results);$i++){
            if(!empty($results[$i][1]) && $results[$i][1] != '必填'){
                $colA = $results[$i][0];
                $id = str_replace("https://icarry.me/product-item/",'',$results[$i][1]);
                $product = ProductDB::find($id);
                if(!empty($product)){
                    $chk = PriceChangeRecordDB::where([['product_id',$id],['is_disabled',0]])
                        ->where('colG','>',$this->timeTranslate($results[$i][5]))->first();
                    if(empty($chk)){
                        $data[] = [
                            'product_id' => $id,
                            'colA' => $results[$i][0],
                            'colB' => $results[$i][1],
                            'colC' => $results[$i][2],
                            'colD' => $results[$i][3],
                            'colE' => $results[$i][4],
                            'colF' => $this->timeTranslate($results[$i][5]),
                            'colG' => !empty($results[$i][6]) ? $this->timeTranslate($results[$i][6]) : null,
                            'admin_id' => $param['admin_id'],
                            'create_time' => date('Y-m-d H:i:s'),
                        ];
                        $success++;
                    }else{
                        $message .= "product id: $id 已存在相同變動記錄。<br>";
                        $fail++;
                    }
                }else{
                    $message .= "product id: $id 不存在資料庫。<br>";
                    $fail++;
                }
            }
            $total++;
        }
        if(count($data) > 0){
            PriceChangeRecordDB::insert($data);
        }
        $result['message'] = $message;
        $result['fail'] = $fail;
        $result['success'] = $success;
        $result['total'] = $total;
        return $result;
    }

    private function timeTranslate($number){
        $number.="0000";
        $d=new DateTime($number);
        return $d->format('Y-m-d H:00:00');
    }
}
