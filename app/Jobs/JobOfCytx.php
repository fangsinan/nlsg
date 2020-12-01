<?php


namespace App\Jobs;


use App\Models\ConfigModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class JobOfCytx implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $job_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->job_data = $data;
    }

    public function handle()
    {
        $is_test = true;
        if ($is_test) {
            $url = 'http://39.107.71.116:8081/v1/partner/notify';
        } else {
            $url = 'https://api.chuangyetianxia.com/v1/partner/notify';
        }


//        $data['live_id'] = $this->job_data['live_id'];
//        $data['live_info_id'] = $this->job_data['live_info_id'];
//
//        switch (intval($this->job_data['type'])) {
//            case 6:
//                $data['method'] = 'PushProduct';//产品
//                break;
//            case 8:
//                $data['method'] = 'pushEnd';//直播结束
//                break;
//            case 9:
//                $data['method'] = 'pushForbiddenWords';//禁言
//                break;
//            case 10:
//                $data['method'] = 'getLivePushOrder';//门票订单推送
//                break;
//            case 11:
//                $data['method'] = 'getLiveOrderRanking';//排行榜
//                break;
//            case 12:
//                $data['method'] = 'getLiveGiftOrder';//礼物订单
//                break;
//            default:
//                return;
//        }
//
//        $url_params = http_build_query($data);
//        $url = ConfigModel::getData(24) . '?' . $url_params;
//        Http::get($url);
    }

}
