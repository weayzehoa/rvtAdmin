<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Exports\OrderExport;
use App\Exports\ProductExport;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use File;
use PDF;
use App\Models\GateExportCenter as ExportCenterDB;

class AdminExportJob implements ShouldQueue
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
        $param = $this->param;

        //目的目錄
        $destPath = '/exports/';
        //檢查本地目錄是否存在，不存在則建立
        !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
        //檢驗是否為pdf類型
        if($param['cate'] == 'excel'){
            if($param['model'] == 'products'){
                if (!empty($param['store']) && $param['store'] == true) {
                    Excel::store(new ProductExport($param), $destPath.$param['filename'], 'real_public');
                }else{
                    return Excel::download(new ProductExport($param), $param['filename']);
                }
            }
        }
        //儲存紀錄到匯出中心資料表
        $param['end_time'] = date('Y-m-d H:i:s');
        $param['condition'] = json_encode($param,true);
        $param['cate'] = $param['model'];
        $log = ExportCenterDB::create($param);
    }
}
