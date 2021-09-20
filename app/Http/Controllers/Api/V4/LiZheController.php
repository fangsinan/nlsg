<?php
namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

//李喆八大名师活动
class LiZheController extends Controller
{

    //删除李婷老师购买直播间记录 https://app.v4.api.nlsgapp.com/api/v4/liting/delsub?phone=13522223779&live_id=119
    public function DelSub(Request $request){

        $live_id = intval($request->get('live_id', 0));
        $phone = intval($request->get('phone', '13522223779'));

        if (empty($live_id)) {
            return $this->error(0, '直播间id不能为空');
        }
        $UserInfo=User::query()->where(['phone'=>$phone])->first();
        if(!empty($UserInfo)) {
            $info = Order::query()->where(['user_id' => $UserInfo->id, 'live_id' => $live_id, 'status' => 1])->first();
            echo '直播间ID：' . $live_id . '<br>';
            if (!empty($info)) {
                $OrderRst = Order::query()->where('id', $info->id)->delete();
                $SubRst = Subscribe::query()->where('order_id', $info->id)->delete();
                if ($OrderRst && $SubRst) {
                    echo '删除成功<br>';
                } else {
                    echo '删除失败：' . $OrderRst . $SubRst . '<br>';
                }
            } else {
                echo '未查询到订单<br>';
            }
        }else{
            echo '用户信息不存在<br>';
        }

    }

    //验证兑换券 http://127.0.0.1:8000/api/v4/lizhe/checking?code=mGC03073s2d141
    public function Checking(Request $request){

        $params = $request->input();
        $code = (empty($params['code']))?0:$params['code'];

        if (empty($code)) {
            return $this->error(0, '兑换码不能为空');
        }

        $code=strtoupper($code); //转换成大写
        $info=DB::table('lz_code')->where(['code'=>$code])->first();
        echo '兑换券：'.$code.'<br>';
        if(!empty($info)){
            $msg='通过';
            echo '验证状态：'.$msg.'<br>';
            echo '渠道：'.$info->name.'('.$info->sign.')'.'<br>';
//            return $this->success([],0,'验证通过');
        }else{
            $msg='未通过';
            echo '验证状态：'.$msg.'<br>';
//            return $this->success([],0,'验证未通过');
        }



    }

    //生成兑换券 https://app.v4.api.nlsgapp.com/api/v4/lizhe/create
    public  function CreateCode(Request $request)
    {
        return ;
        try {
            $code_name = [
//                'MGC' => '古云草',
//                'LFT' => '三度',
//                'HKD' => '盛世文航',
//                'ADQ' => '创华',
//                'MAH' => '企航',
//                'KHT' => '智上成',
//                'NYZ' => '汇成',
//                'OTA' => '慧宇',
//                'DSS' => '智客',
//                'AQW' => '久善今心',
//                'ZBF' => '主办方',
//                'AAQ' => '华德汇智',
//                'ZYL' => '收现商学院',
//                'PQH' => '女素教育',
                'UUA' => '智慧卡神',
                'GFR' => '美在当下',
                'YYQ' => '中企汇聚'
            ];

            foreach ($code_name as $key => $val) {
                $add_code_data = [];
                $i = 1;
                while ($i <= 500) {
                    $add_code_data[] = [
                        'sign'=>$key,
                        'name' => $val,
                        'code' => $key . self::get_34_Number(self::createCodeTemp(), 10),
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $i++;
                }

                $res = DB::table('lz_code')->insert($add_code_data);
                if ($res === false) {
                    return ['code' => false, 'msg' => '失败'];
                }
            }

            return ['code' => true, 'msg' => '成功'];

        } catch (\Exception $e) {
            return $this->error(0, $e->getMessage() . ' ' . $e->getLine());
        }
    }

    //生成34进制数
    public static function get_34_Number($int, $format = 5)
    {
        $dic = array(
            0 => '0',1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9',
            10 => 'A', 11 => 'B', 12 => 'C', 13 => 'D', 14 => 'E', 15 => 'F', 16 => 'G', 17 => 'H',
            18 => 'J', 19 => 'K', 20 => 'L', 21 => 'M', 22 => 'N', 23 => 'P', 24 => 'Q', 25 => 'R',
            26 => 'S', 27 => 'T', 28 => 'U', 29 => 'V', 30 => 'W', 31 => 'X', 32 => 'Y', 33 => 'Z'
        );

        $arr = array();
        $loop = true;
        while ($loop) {
            $arr[] = $dic[bcmod($int, 34)];
            $int = floor(bcdiv($int, 34));
            if ($int == 0) {
                $loop = false;
            }
        }
        $arr = array_pad($arr, $format, $dic[0]);
        return implode('', array_reverse($arr));
    }

    public static function createCodeTemp()
    {
        $time=time().rand(10000,99999);
        return str_pad(rand($time, 99999999999), 5, 0, STR_PAD_LEFT);
    }

}
