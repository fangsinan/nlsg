<?php

namespace App\Console;

use App\Models\Coupon;
use App\Models\MallOrder;
use App\Models\Order;
use App\Models\MallOrderFlashSale;
use App\Models\MallOrderGroupBuy;
use App\Servers\ChannelServers;
use App\Servers\MallRefundJob;
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
            MallOrderGroupBuy::clear();//拼团超时订单处理和退款登记
            MallOrderFlashSale::clear();//秒杀订单处理
            Order::clear(); //线下课超时处理
            MallRefundJob::refundJob(1);//商城订单退款处理
            ChannelServers::cytxJob();//创业天下推送
        })->everyMinute();//每分

        $schedule->call(function () {
            MallRefundJob::refundJob(2);//商城订单退款查询
//            $c = new ChannelServers();
//            $c->getDouyinOrder();
//            $c->supplementDouYinOrder();
        })->everyFiveMinutes();//每五分

        $schedule->call(function () {

        })->hourly();//每小时

        $schedule->call(function () {
            MallOrder::receipt();//自动收货
            Coupon::clear();//失效优惠券清理
        })->dailyAt('03:00');//半夜清理


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
