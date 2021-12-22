<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\ChannelWorksList;
use App\Models\Click;
use App\Models\User;
use App\Models\Works;
use App\Servers\ChannelServers;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    //创业天下推送校验
    public function cytxOrderCheck(Request $request){
        $cs = new ChannelServers();
        $res = $cs->cytxOrderCheck($request->input());
        return $this->getRes($res);
    }

    /**
     * 创业天下课程列表(旧)
     * @api {get} /api/v4/channel/cytx 创业天下课程列表(旧)
     * @apiVersion 4.0.0
     * @apiName /api/v4/channel/cytx
     * @apiGroup  创业天下
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/channel/cytx
     * @apiDescription 创业天下课程列表(旧)
     * */
    public function cytx(Request $request)
    {
        $model = new Works();
        $data = $model->listForCytx($request->input());
        return $this->getRes($data);
    }

    /**
     * 创业天下课程列表
     * @api {get} /api/v4/channel/cytx_new 创业天下课程列表
     * @apiVersion 4.0.0
     * @apiName /api/v4/channel/cytx_new
     * @apiGroup  创业天下
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/channel/cytx_new
     * @apiParam {number=0,1,2} [is_buy] 是否已购(0全部 1已购 2未购)
     * @apiParam {number=0,1,2} [works_type] 类型(0全部 1视频 2音频)
     * @apiParam {number} [category_id] 分类id
     * @apiParam {string} [ob] 排序(view_num,created,price)
     * @apiDescription 创业天下课程列表
     */
    public function cytxNew(Request $request)
    {
        $model = new ChannelWorksList();
        $data = $model->listForCytx($request->input(),$this->user);
        return $this->getRes($data);
    }

    /**
     * 创业天下消费记录
     * @api {get} /api/v4/channel/cytx_order 创业天下消费记录
     * @apiVersion 4.0.0
     * @apiName /api/v4/channel/cytx_order
     * @apiGroup  创业天下
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/channel/cytx_order
     * @apiDescription 创业天下消费记录
     */
    public function cytxOrder(Request $request){
        $model = new ChannelWorksList();
        $data = $model->cytxOrder($request->input(),$this->user);
        return $this->getRes($data);
    }

    /**
     * 创业天下banner
     * @api {get} /api/v4/channel/banner 创业天下banner
     * @apiVersion 4.0.0
     * @apiName /api/v4/channel/banner
     * @apiGroup  创业天下
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/channel/banner
     * @apiDescription 创业天下banner
     */
    public function cytxBanner(Request $request){
        $model = new ChannelWorksList();
        $data = $model->cytxBanner($request->input(),$this->user);
        return $this->getRes($data);
    }

    /**
     * 点击统计
     * @api {get} /api/v4/channel/click 点击统计
     * @apiVersion 4.0.0
     * @apiName /api/v4/channel/click
     * @apiGroup  创业天下
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/v4/channel/click
     * @apiDescription 点击统计
     * @apiParam {number=1,2,3,4} type(1：专栏  2：商品  3：精品课 4: banner)
     * @apiParam {number} wid 作品id
     * @apiParam {string} flag(cytx)
     * */
    public function click(Request $request)
    {
        $model = new Click();
        $data = $model->add($request->input(), $this->user, $this->getIp($request));
        return $this->getRes($data);
    }

    public function login(Request $request)
    {
        $partner_flag = strtolower($request->input('partner_flag', ''));
        switch ($partner_flag) {
            case 'cytx':
                $data['phone'] = $request->input('telephone', '');
                $data['nickname'] = $request->input('user_name', '');
                $data['headimg'] = 'image/202009/13f952e04c720a550193e5655534be86.jpg';
                $data['ref'] = 1;
                $sign = $request->input('sign', '');
                $check_sign = md5($data['phone'] . 'cytxnlsg_v4');
                if ($sign !== $check_sign) {
                    return $this->getRes(['code' => false, 'msg' => '签名失败']);
                }
                break;
            default:
                return $this->getRes(['code' => false, 'msg' => '参数错误']);
        }
        $url = $request->input('url', '');
        if (empty($data['phone'])) {
            return $this->getRes(['code' => false, 'msg' => '参数错误']);
        }
        if (empty($data['nickname'])) {
            $data['nickname'] = substr_replace($data['phone'], '****', 3, 4);
        }

        //注册或登陆
        $userModel = new User();
        $user = $userModel->channelLogin($data);
        if (($user['code'] ?? true) == false) {
            return $this->getRes(['code' => false, 'msg' => '注册失败,请重试']);
        }

        $token = auth('api')->login($user);

        $data = [
            'user_id' => $user->id,
            'token' => $token,
            'url' => $url,
            'type' => $request->input('type', ''),
        ];

        return $this->getRes($data);


    }

}
