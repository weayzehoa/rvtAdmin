<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryUserPoint as UserPointDB;
use App\Imports\UserPointsImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class UserPointsImportJob implements ShouldQueue
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
        $file = $param->file('filename');
        $result = Excel::toArray(new UserPointsImport($param), $file);
        if(count($result[0]) > 0){
            $data = $result[0];
            for($i=0;$i<count($data);$i++){
                if(!empty($data[$i][0])){
                    $id = $data[$i][0];
                    $pointType = $data[$i][1];
                    $points = $data[$i][2];
                    $user = UserDB::find($id);
                    if(!empty($user)){
                        if($points > 0 && !empty($pointType)){
                            $userPoint['user_id'] = $user->id;
                            $userPoint['points'] = $points;
                            $userPoint['balance'] = $user->points + $points;
                            $userPoint['dead_time'] = Carbon::now()->addMonth(6);
                            $userPoint['point_type'] = $pointType;
                            $userPoint['is_dead'] = 0;
                            $user->update(['points' => $user->points + $points]);
                            UserPointDB::create($userPoint);
                        }
                    }
                }
            }
        }
    }
}
