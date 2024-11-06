<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ChangeStatusImport;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryPriceChangeRecord as PriceChangeRecordDB;
use DateTime;

class ChangeStatusFileImportJob implements ShouldQueue
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
        $importData = Excel::toArray(new ChangeStatusImport, $param['filename']);
        $results = $importData[0];
        for($i=0;$i<count($results);$i++){
            if(!empty($results[$i][1]) && !empty($results[$i][2]) && !empty($results[$i][3]) && !empty($results[$i][4]) && $results[$i][1] != '必填'){
                $colA = $results[$i][0];
                $id = str_replace("https://icarry.me/product-item/",'',$results[$i][1]);
                if(in_array($results[$i][2],[-9,-3,1])){
                    $product = ProductDB::find($id);
                    if(!empty($product)){
                        if($product->status != $results[$i][2]){
                            $chk = PriceChangeRecordDB::where([['product_id',$id],['is_disabled',0]])
                            ->where('status_updown',$results[$i][2])->first();
                            if(empty($chk)){
                                $data[] = [
                                    'product_id' => $id,
                                    'status_updown' => $results[$i][2],
                                    'colA' => $results[$i][0],
                                    'colB' => $results[$i][1],
                                    'colF' => $this->timeTranslate($results[$i][3]),
                                    'colG' => $this->timeTranslate($results[$i][4]),
                                    'admin_id' => $param['admin_id'],
                                    'create_time' => date('Y-m-d H:i:s'),
                                ];
                                $success++;
                            }else{
                                $message .= "product id: $id 已存在相同變動記錄。<br>";
                                $fail++;
                            }
                        }else{
                            $message .= "product id: $id 狀態相同。<br>";
                            $fail++;
                        }
                    }else{
                        $message .= "product id: $id 不存在資料庫。<br>";
                        $fail++;
                    }
                }else{
                    $message .= "product id: $id 狀態只能填寫-9,-3,1。<br>";
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
