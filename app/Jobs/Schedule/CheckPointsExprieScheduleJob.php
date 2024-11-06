<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User as UserDB;
use App\Models\UserPoint as UserPointDB;
use Carbon\Carbon;
use DB;

class CheckPointsExprieScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = Carbon::now();
        $before180days = Carbon::now()->subDays(180);
        $userIds = UserPointDB::where([['dead_time','<=',$now],['is_dead',0],['points','>',0]])->select('user_id')->groupBy('user_id')->get();
        $users = UserDB::where('points','>',0)->whereIn('id',$userIds)
            ->select([
                'id',
                'points',
                'use_before_points' => UserPointDB::whereColumn('user_points.user_id','users.id')
                    ->where([['user_points.created_at','<=',$now],['user_points.created_at','>=',$before180days],['user_points.points','<',0]])
                    ->selectRaw('SUM(user_points.points)')->limit(1),
            ])->get();
        foreach ($users as $user) {
            //校正使用者點數負值歸0
            $user->points < 0 ? $user->points = 0 : '';
            //找出is_dead未被標記1的贈點
            $userPoints = UserPointDB::whereNotNull('dead_time')->where([['user_id',$user->id],['dead_time','<=',$now],['is_dead',0],['points','>',0]])->orderBy('created_at','asc')->get();
            //計算及更新
            foreach($userPoints as $userPoint){
                //use_before_points這段時間內總共使用了多少點數(只有減)
                $balance = $userPoint->points + $user->use_before_points;
                if(empty($user->use_before_points)){ //從來沒用過
                    //扣除使用者的points
                    $user->points - $userPoint->points <= 0 ? $points = 0 : $points = $user->points - $userPoint->points;
                    $user->update(['points' => $points]);
                    //新增一筆記錄
                    $user->points <= 0 ? $user->points = 0 : '';
                    UserPointDB::create([
                        'user_id' => $user->id,
                        'point_type' => $userPoint->point_type.'[到期]',
                        'points' => $userPoint->points * -1,
                        'balance' => $user->points,
                        'is_dead' => 1,
                    ]);
                    //改為過期
                    $userPoint->update(['is_dead' => 1]);
                }elseif($balance == 0){//完全抵消
                    //改為過期
                    $userPoint->update(['is_dead' => 1]);
                }elseif($balance > 0){//例如送50點 只用10點 那還有40點要銷毀
                    //扣除使用者的points
                    $user->points - $balance <= 0 ? $points = 0 : $points = $user->points - $balance;
                    $user->update(['points' => $points]);
                    //新增一筆記錄
                    UserPointDB::create([
                        'user_id' => $user->id,
                        'point_type' => $userPoint->point_type.'[到期]',
                        'points' => $balance * -1,
                        'balance' => $user->points,
                        'is_dead' => 1,
                    ]);
                    //改為過期
                    $userPoint->update(['is_dead' => 1]);
                }elseif($balance < 0){//例如送50點 用100點 多用了50點(可能是有其他加點)
                    //改為過期
                    $userPoint->update(['is_dead' => 1]);
                }
            }
        }
        //找出point_type內有到期字眼,將dead_time清除, is_dead 轉為1
        UserPointDB::where('point_type','like',"%到期]")->update(['dead_time' => null, 'is_dead' => 1]);
        return true;
    }
}
