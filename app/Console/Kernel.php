<?php

namespace App\Console;

use App\Http\Controllers\Api\V4\ImMsgController;
use App\Http\Controllers\Api\V4\LiveController;
use App\Http\Controllers\Api\V4\UserWechat;
use App\Models\Coupon;
use App\Models\LiveConsole;
use App\Models\MallOrder;
use App\Models\MallOrderFlashSale;
use App\Models\MallOrderGroupBuy;
use App\Models\Order;
use App\Models\PayRecordDetailStay;
use App\Models\Task;
use App\Models\Works;
use App\Models\WorksInfo;
use App\Servers\AliUploadServers;
use App\Servers\ChannelServers;
use App\Servers\DealServers;
use App\Servers\ErpServers;
use App\Servers\ImDocServers;
use App\Servers\MallRefundJob;
use App\Servers\removeDataServers;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $schedule->call(function () {
            //直播间在线人数入库，方便调试
            LiveController::CrontabOnlineUser();
        })->everyMinute()->runInBackground();//每分

        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
//            $request->getClientIps();
            DB::table('w_erp_temp')->insert([
                'sku'=>date('Y-m-d H:i:s'),
//                'erp_spu'=>$request->getClientIp(),
                'erp_sku'=>$_SERVER['REMOTE_ADDR']
            ]);
            MallOrder::clear();//超时订单处理
            Order::clear(); //线下课超时处理
            MallRefundJob::refundJob(1);//商城订单退款处理
            WorksInfo::covertVideo(); //转换音频视频
            Works::deal(); //自动上架
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            //im doc
            $s = new ImDocServers();
            $s->sendGroupDocMsgJob();
            //秒级执行 立即发送任务

            //IM 群发后入库
            $msg = new ImMsgController();
            for ($i=1;$i<=6;$i++){
                $msg->RedisSendAllMsgCallback();
                sleep(10);
            }

        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            $m = new LiveConsole();
            $m->LiveAutoConfig();//直播自动开始结束和人数
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
//            $cs->supplementDouYinOrder();
//            $cs->douYinJob();
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
        })->dailyAt('03:00');//半夜清理

        $schedule->call(function () {
//            User::expire(); //会员过期提醒
//            Column::expire(); //专栏过期提醒
            Task::pushTo();  //消息任务

        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            //Coupon::couponEndTimeMsgTask(); //推送的优惠券提醒
            //VipUser::vipEndTimeMsgTask();//推送的会员提醒
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
//            Subscribe::expire();
        })->daily();  //每天执行一次


        //https://laravelacademy.org/post/8484.html
        $schedule->call(function () {
            //抓取腾讯IM图片到阿里云
            $AliUploadServer=new AliUploadServers();
            $AliUploadServer->UploadMediaPull();
        })->everyMinute()->between('0:00', '3:00')->runInBackground();//每分钟执行一次

        $schedule->call(function () {
            //抓取腾讯IM音视频、文件到阿里云
            $AliUploadServer=new AliUploadServers();
            $AliUploadServer->UploadMediaVideoAudio();
        })->everyFiveMinutes()->between('2:00', '6:00')->runInBackground();//5分钟执行一次

        $schedule->call(function () {
            //抓取直播间成交订单
            DealServers::getOrderInfo([], 0, 1);
        })->everyMinute()->between('21:55', '22:10')->runInBackground();//每分钟执行一次

        $schedule->call(function () {
            UserWechat::AddUserWechat();//通过部门id获取企业客户
        })->dailyAt('21:51');
        $schedule->call(function () {
            UserWechat::AddUserWechat();//通过部门id获取企业客户
        })->dailyAt('22:10');
        $schedule->call(function () {
            UserWechat::AddUserWechat();//通过部门id获取企业客户
            UserWechat::UserWechatEdit();//通过部门id获取企业客户
        })->dailyAt('0:01');
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
