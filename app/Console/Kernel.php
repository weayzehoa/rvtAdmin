<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\EverydayScheduleJob as Everyday;
use App\Jobs\EverydayAtThreeOclockScheduleJob as EverydayAtThreeOclock;
use App\Jobs\EveryFiveMinuteScheduleJob as EveryFiveMinute;
use App\Jobs\EveryTenMinuteScheduleJob as EveryTenMinute;
use App\Jobs\EverySixHourScheduleJob as EverySixHour;
use App\Jobs\EverydayAt22OclockScheduleJob as EverydayAt22Oclock;
use App\Jobs\EveryFridayAt22OclockScheduleJob as EveryFridayAt22Oclock;
use App\Models\SmsLog as SmsLogDB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     * @參考 https://laravel.com/docs/8.x/scheduling
     * @參考 https://learnku.com/docs/laravel/8.x/scheduling/9399
     * @在系統的 crontab -e 新增一行常駐 * * * * * cd /var/www/html/iCarryBackend && php artisan schedule:run >> ~/crontab_log 2>&1
     * @在系統的 /etc/rc.d/rc.local 新增一行常駐 cd /var/www/html/iCarryBackend && php artisan queue:work >> ~/laravel_queue_log &
     */
    protected function schedule(Schedule $schedule)
    {
        //每週五晚上十點排程
        $schedule->job(new EveryFridayAt22Oclock)->weekly()->fridays()->at('22:00');
        //每五分鐘排程
        $schedule->job(new EveryFiveMinute)->everyFiveMinutes();
        //每十分鐘排程
        $schedule->job(new EveryTenMinute)->everyTenMinutes();
        //每六小時排程
        $schedule->job(new EverySixHour)->everySixHours();
        //每天晚上十點排程
        $schedule->job(new EverydayAt22Oclock)->dailyAt('22:00');
        //每天排程(0:00)
        $schedule->job(new Everyday)->daily();
        //凌晨三點排程
        $schedule->job(new EverydayAtThreeOclock)->dailyAt('03:00');
        //凌晨四點重開機
        $schedule->exec('sudo reboot')->dailyAt('04:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    //schedule 參考
    protected function reference(Schedule $schedule)
    {
        //呼叫系統 exec 方式
        $schedule->exec('node /home/forge/script.js')->daily();

        //呼叫 function 方式
        $schedule->call(function () {
            SmsLogDB::create([
                'status' => 1,
                'message' => 1,
                'msg_id' => 1,
                'aws_id' => 1,
            ]);
        })->everyMinute();

        //直接馬上執行 job
        $schedule->call(function () {
            CrontabJob::dispatchNow();
        })->everyMinute();

       //使用下面方法需要開機常駐,
       //執行 cd /var/www/html/iCarryBackend && php artisan queue:work >> ~/laravel_queue_log &
        $schedule->job(new CrontabJob)->everyMinute();
    }
}
