<?php

namespace Itsm\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Itsm\Services\CrontabService;
use Itsm\Model\Usercenter\SupportHangupTask;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//         $schedule->command('inspire')
//                  ->hourly();
        $cronClass = new CrontabService();
        $schedule->call(function () use ($cronClass) {
            $cronClass->hangupRemind("no");
        });//只执行特定时间的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->hangupRemind("two");
        })->cron('*/2  * * * *');//执行没两分钟的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->hangupRemind("five");
        })->cron('*/5  * * * *');//执行没五分钟的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->hangupRemind("ten");
        })->cron('*/10  * * * *');//执行没十分钟的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->hangupRemind("fifteen");
        })->cron('*/15  * * * *');//执行没十五分钟的工单提醒任务
        /**
         * 事件通知
         */
        $schedule->call(function () use ($cronClass) {
            $cronClass->handoverRemind("no");
        });//只执行特定时间的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->handoverRemind("two");
        })->cron('*/2  * * * *');//执行每两分钟的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->handoverRemind("five");
        })->cron('*/5  * * * *');//执行每五分钟的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->handoverRemind("ten");
        })->cron('*/10  * * * *');//执行每十分钟的工单提醒任务
        $schedule->call(function () use ($cronClass) {
            $cronClass->handoverRemind("fifteen");
        })->cron('*/15  * * * *');//执行每十五分钟的工单提醒任务

        //系统生成下一期账单
        $schedule->call(function () use ($cronClass) {
            $cronClass->generateBill("no");
        })->cron('30  0 * * *');//执行每日00:30生成下期账单任务

        //系统检测账单连续性
        $schedule->call(function () use ($cronClass) {
            $cronClass->checkBillCompleteness("no");
        })->cron('*/1  * * * *');//执行每日01:00检测账单连续性任务
    }
}
