<?php

namespace App\Console;

use App\Models\Column;
use App\Models\Coupon;
use App\Models\MallOrder;
use App\Models\MallOrderFlashSale;
use App\Models\MallOrderGroupBuy;
use App\Models\Order;
use App\Models\PayRecordDetailStay;
use App\Models\Works;
use App\Models\WorksInfo;
use App\Models\User;
use App\Models\Task;
use App\Servers\ChannelServers;
use App\Servers\ErpServers;
use App\Servers\MallRefundJob;
use App\Servers\removeDataServers;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


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
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            MallOrder::clear();//超时订单处理
            Order::clear(); //线下课超时处理
            MallRefundJob::refundJob(1);//商城订单退款处理
            WorksInfo::covertVideo(); //转换音频视频
            Works::deal(); //自动上架
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            ChannelServers::cytxJob();//创业天下推送
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            MallOrderGroupBuy::clear();//拼团超时订单处理和退款登记
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            MallOrderFlashSale::clear();//秒杀订单处理
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            MallRefundJob::shillJob(1);
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            //erp物流同步与回写
            $s = new ErpServers();
            $s->logisticsSync();
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            //erp订单创建与更新
            $s = new ErpServers();
            $s->pushRun();
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            //开通订阅队列
            $servers = new removeDataServers();
            $servers->worksListOfSub();
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            //队列消息发送
            $servers = new removeDataServers();
            $servers->subListSms();
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            $cs = new ChannelServers();
            $cs->getDouyinOrder();
            $cs->supplementDouYinOrder();
            $cs->douYinJob();
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            MallRefundJob::refundJob(2);//商城订单退款查询
        })->everyFiveMinutes();//每五分

        $schedule->call(function () {
            MallRefundJob::shillJob(2);
        })->everyFiveMinutes()->runInBackground();

        $schedule->call(function () {

        })->hourly();//每小时

        $schedule->call(function () {
            MallOrder::receipt();//自动收货
            Coupon::clear();//失效优惠券清理
            Works::statistic(); //数据统计
            PayRecordDetailStay::remove();//商城待到帐收益划转
            Coupon::couponEndTimeMsgTask();//将过期优惠券的提醒写入
        })->dailyAt('03:00');//半夜清理

        $schedule->call(function () {
            User::expire(); //会员过期提醒
            Column::expire(); //专栏过期提醒
            Coupon::expire(); //优惠券过期提醒
            Task::push();  //消息任务

        })->everyMinute()->runInBackground();//每分

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
