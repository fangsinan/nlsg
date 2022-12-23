<?php

namespace App\Console;

use App\Http\Controllers\Api\V4\ImMsgController;
use App\Http\Controllers\Api\V4\LiveController;
use App\Http\Controllers\Api\V4\UserWechat;
use App\Models\Coupon;
use App\Models\History;
use App\Models\LiveConsole;
use App\Models\MallOrder;
use App\Models\MallOrderFlashSale;
use App\Models\MallOrderGroupBuy;
use App\Models\Order;
use App\Models\PayRecordDetailStay;
use App\Models\ShortVideoModel;
use App\Models\Task;
use App\Models\VipUserBind;
use App\Models\Works;
use App\Models\WorksInfo;
use App\Servers\AliUploadServers;
use App\Servers\CacheServers;
use App\Servers\ChannelServers;
use App\Servers\DealServers;
use App\Servers\ErpServers;
use App\Servers\ImDocServers;
use App\Servers\MallRefundJob;
use App\Servers\LiveConsoleServers;
use App\Servers\removeDataServers;
use App\Servers\UserWechatServers;
use App\Servers\V5\JpushService;
use App\Servers\V5\ShareServers;
use App\Servers\V5\WechatServers;
use App\Servers\V5\XiaoeTechServers;
use EasyWeChat\Factory;
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
        //微信小程序获取access_token
        $schedule->command('command:WeChatTools')
            ->everyFiveMinutes()->withoutOverlapping(5)
            ->runInBackground()->onOneServer();


        //抖音订单测试部分
        $schedule->command('command:DouDianOrder 3')
            ->everyMinute()->withoutOverlapping(2)
            ->runInBackground()->onOneServer();
        $schedule->command('command:DouDianOrder 1')
            ->everyFiveMinutes()->withoutOverlapping(5)
            ->runInBackground()->onOneServer();
        $schedule->command('command:DouDianOrder 2')
            ->everyFiveMinutes()->withoutOverlapping(5)
            ->runInBackground()->onOneServer();
        $schedule->command('command:DouDianOrderDecrypt')
            ->everyFiveMinutes()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();
        $schedule->command('command:DouDianProduct')
            ->everyFifteenMinutes()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();

        $schedule->call(function () {
            JpushService::TimedPush();
        })->everyMinute()->runInBackground();//每分发送极光消息
        $schedule->call(function () {
            $servers = new JpushService();
            $servers->Statistics();
        })->everyFiveMinutes()->runInBackground();//每5分同步发送到达量

        //im社群的发送任务
//        $schedule->command('imJob')
//            ->everyMinute()->withoutOverlapping(2)
//            ->runInBackground()->onOneServer();
//        $schedule->command('imJob_1')
//            ->everyMinute()
//            ->withoutOverlapping(1)
//            ->runInBackground()
//            ->onOneServer();
//        $schedule->command('imJob_2')
//            ->everyMinute()->withoutOverlapping(2)
//            ->runInBackground()->onOneServer();

        //⬇⬇⬇⬇xe_user_job⬇⬇⬇⬇
//        $schedule->command('command:XeUserJob 1')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 10')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 11')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 12')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 13')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 14')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 15')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 16')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 17')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 20')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 21')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 22')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 23')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 24')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 25')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 26')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 27')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
//        $schedule->command('command:XeUserJob 30')->everyMinute()->withoutOverlapping(3)->runInBackground()->onOneServer();
        //⬆⬆⬆⬆xe_user_job⬆⬆⬆⬆


//        $schedule->command('command:orderClear')
//            ->everyMinute()->withoutOverlapping(1)
//            ->runInBackground()->onOneServer();
//
//        $schedule->command('command:mallOrderClear')
//            ->everyMinute()->withoutOverlapping(1)
//            ->runInBackground()->onOneServer();
//
//        $schedule->command('command:cytxJob')
//            ->everyMinute()->withoutOverlapping(1)
//            ->runInBackground()->onOneServer();

        $schedule->command('command:shillJob')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();

        //开通课程与直播
        $schedule->command('command:subListClose')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();
        //关闭课程与直播
        $schedule->command('command:subListOpen')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();

        //vip新增课程追加订阅
        $schedule->command('command:VipWorksListAppendSub')
            ->everyFiveMinutes()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();

        //order表推送到erp
        $schedule->command('command:ErpOrderPush')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();
        $schedule->command('command:ErpOrderAddAddress')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();

        //mall order表推送到erp
        $schedule->command('command:ErpMallOrderPush')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();

        //erp物流回写
        $schedule->command('command:ErpQuery')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();

        $schedule->command('command:ErpTradeQuery')->dailyAt('00:10');

//        $schedule->command('command:erpJob')
//            ->everyMinute()->withoutOverlapping(1)
//            ->runInBackground()->onOneServer();
//
//
//        //直播间在线人数入库，方便调试
//        $schedule->command('command:liveOnlineUser')
//            ->everyMinute()
//            ->runInBackground()->onOneServer();
//
//        //直播间在线人数存入redis
//        $schedule->command('command:liveOnlineUserRedis')
//            ->everyMinute()
//            ->runInBackground()->onOneServer();

//        $schedule->exec('php artisan imJob')->everyMinute();

//        $schedule->call(function () {
//            LiveConsoleServers::CrontabOnlineUserRedis();//直播间在线人数存入redis
//        })->everyMinute()->runInBackground();//每分
//
//        $schedule->call(function () {
//            LiveConsoleServers::CrontabOnlineUser();//直播间在线人数入库，方便调试
//        })->everyMinute()->runInBackground();//每分
//
//        $schedule->call(function () {
//            LiveConsoleServers::CrontabJoinRedis();//加入直播间记录入库
//        })->everyMinute()->runInBackground();//每分
//
//        $schedule->call(function () {
//            LiveConsoleServers::CrontabCommentRedis();//评论入库
//        })->everyMinute()->runInBackground();//每分
//
//        $schedule->call(function () {
//            LiveConsoleServers::CrontabGiftRedis();//直播打赏入库
//        })->everyFiveMinutes()->runInBackground();//每5分

        $schedule->command('command:CrontabOnlineUserRedis')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();
        $schedule->command('command:CrontabOnlineUser')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();
        $schedule->command('command:CrontabJoinRedis')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();
        $schedule->command('command:CrontabCommentRedis')
            ->everyMinute()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();
        $schedule->command('command:CrontabGiftRedis')
            ->everyFiveMinutes()->withoutOverlapping(1)
            ->runInBackground()->onOneServer();




        $schedule->call(function () {
            WechatServers::GetOpenId(); //抓取微信公众号关注用户
        })->everyMinute()->between('22:40', '23:59')->runInBackground();//每分钟执行一次

//        $schedule->call(function () {
//            WechatServers::SetAccessToken(); //生成token
//        })->everyFiveMinutes()->runInBackground();//每5分

        $schedule->call(function () {
            WechatServers::TemplateLiveDesc(); //发送模板消息
        })->dailyAt('09:55');

        $schedule->call(function () {
            WechatServers::TemplateLiveAsc(); //发送模板消息升序
        })->dailyAt('09:55');


        $schedule->call(function () {
            $m = new LiveConsole();
            $m->LiveAutoConfig();//直播自动开始结束和人数
            LiveConsoleServers::getPhoneRegion();//识别手机号归属地
        })->everyMinute()->runInBackground();//每分


//        $schedule->command('inspire')->hourly();
        $schedule->call(function () {
//            OrderRefundServers::test('callJob');
            MallOrder::clear();//超时订单处理
            Order::clear(); //线下课超时处理
            MallRefundJob::refundJob(1);//商城订单退款处理
            WorksInfo::covertVideo(); //转换音频视频
            ShortVideoModel::toVideo(); //转换音频视频
            Works::deal(); //自动上架
        })->everyMinute()->runInBackground();//每分

        $schedule->call(function () {
            //im doc
            $s = new ImDocServers();
            $s->sendGroupDocMsgJob();
            //秒级执行 立即发送任务

            //IM 群发后入库
            $msg = new ImMsgController();
            for ($i = 1; $i <= 6; $i++) {
                $msg->RedisSendAllMsgCallback();
                sleep(10);
            }

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

//        $schedule->call(function () {
//            //erp物流同步与回写
//            $s = new ErpServers();
//            $s->logisticsSync();
//        })->everyMinute()->runInBackground();//每分

//        $schedule->call(function () {
//            //erp订单创建与更新
//            $s = new ErpServers();
//            $s->pushRun();
//        })->everyMinute()->runInBackground();//每分

//        $schedule->call(function () {
//            //开通订阅队列
//            $servers = new removeDataServers();
//            $servers->worksListOfSub();
//        })->everyMinute()->runInBackground();//每分
//
//        $schedule->call(function () {
//            //关闭订阅队列
//            $servers = new removeDataServers();
//            $servers->worksListOfDelSub();
//        })->everyMinute()->runInBackground();//每分

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
//            $config = [
//                'app_id' => 'wxe24a425adb5102f6',
//                'secret' => '2ded804b74f99ae2f342423dd7952620',
//                'response_type' => 'array'
//            ];
//
//            $app = Factory::officialAccount($config);
//            $app->access_token->getRefreshedToken();
            ShareServers::SetTicket();
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
            VipUserBind::clear();//关系保护过期修改
//            Subscribe::expire();
        })->daily();  //每天执行一次


        //https://laravelacademy.org/post/8484.html
        $schedule->call(function () {
            //抓取腾讯IM图片到阿里云
            $AliUploadServer = new AliUploadServers();
            $AliUploadServer->UploadMediaPull();
        })->everyMinute()->between('0:00', '3:00')->runInBackground();//每分钟执行一次

        $schedule->call(function () {
            //抓取腾讯IM音视频、文件到阿里云
            $AliUploadServer = new AliUploadServers();
            $AliUploadServer->UploadMediaVideoAudio();
        })->everyFiveMinutes()->between('2:00', '6:00')->runInBackground();//5分钟执行一次

        $schedule->call(function () {
            //抓取直播间成交订单
            DealServers::getOrderInfo([], 0, 1);
        })->everyMinute()->between('21:55', '22:10')->runInBackground();//每分钟执行一次

        /*$schedule->call(function () {
            UserWechat::AddUserWechat();//通过部门id获取企业客户
        })->dailyAt('21:51');
        $schedule->call(function () {
            UserWechat::AddUserWechat();//通过部门id获取企业客户
        })->dailyAt('22:10');*/
        $schedule->call(function () {
//            UserWechat::AddUserWechat();//通过部门id获取企业客户
            UserWechat::UserWechatEdit(3);
            UserWechat::UserWechatEdit(11);
            History::HistorySize();
        })->dailyAt('0:01');



        //每小时查询一次企业微信客户转移接口
        $schedule->call(function () {

            $UserWechatServers=new UserWechatServers();
            $UserWechatServers->consume_redis_transfer_customer();
            $UserWechatServers->transfer_result();
            $UserWechatServers->clear_user_wechat_data();

        })->everyThirtyMinutes();

        //企业微信客户user_id获取
        $schedule->call(function () {

            $UserWechatServers=new UserWechatServers();
            $UserWechatServers->set_wechat_user_id();

        })->between('1:00', '6:00')->everyThirtyMinutes();


        //小鹅通 订单
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_order_list(1);
        })->everyTenMinutes()->runInBackground();
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_order_list(0);
        })->everyMinute()->runInBackground();

        //小鹅通订单分销
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_order_detail(1);
        })->everyTenMinutes()->runInBackground();

        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_order_detail(0);
        })->everyMinute()->runInBackground();

        //小鹅通用户user_id同步
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_user_userid();
        })->everyTenMinutes()->runInBackground();

        //小鹅通 推广员
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_list(1);
        })->everyTenMinutes()->runInBackground();
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_list(0);
        })->everyMinute()->runInBackground();

        //小鹅通用户
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_user_info(1);
        })->everyTenMinutes()->runInBackground();
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_user_info(0);
        })->everyMinute()->runInBackground();


        //查询最新的推广员客户
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_fast_distributor_customer_list();
        })->between('8:00', '23:00')->everyThirtyMinutes()->runInBackground();

        //获取推广员客户
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_customer_list(1);
        })->dailyAt('0:01')->runInBackground();

        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_customer_list(0,1);
        })->everyMinute()->runInBackground();//每分钟执行一次
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_customer_list(0,2);
        })->everyMinute()->runInBackground();//每分钟执行一次
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_customer_list(0,3);
        })->everyMinute()->runInBackground();//每分钟执行一次
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_customer_list(0,4);
        })->everyMinute()->runInBackground();//每分钟执行一次
        $schedule->call(function () {
            $servers = new XiaoeTechServers();
            $servers->sync_distributor_customer_list(0);
        })->everyMinute()->runInBackground();//每分钟执行一次

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
