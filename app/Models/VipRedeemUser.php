<?php


namespace App\Models;

use App\Http\Controllers\Api\V4\CreatePosterController;
use Illuminate\Support\Facades\DB;
use function EasyWeChat\Kernel\Support\str_random;

class VipRedeemUser extends Base
{
    protected $table = 'nlsg_vip_redeem_user';


    public function list($user, $params)
    {
        if (empty($user['new_vip']['level'] ?? 0)) {
            return [];
        }
        $page = intval($params['page'] ?? 1);
        $size = intval($params['size'] ?? 10);
        $price = ConfigModel::getData(25);

        $query = self::query();

        if (!empty($params['id'] ?? 0)) {
            $query->whereId($params['id']);
        }

        $query->where('user_id', '=', $user['id']);

        if (empty($params['id'] ?? 0)) {
            //1未使用 2已使用 3赠送中 4已送出
            switch (intval($params['flag'] ?? 1)) {
                case 1:
                    $query->where('status', '=', 1);
                    break;
                case 2:
                    $query->where('status', '=', 2);
                    break;
                case 3:
                    $query->where('status', '=', 3);
                    break;
                case 4:
                    $query->where('status', '=', 4);
                    break;
                case 5:
                    $query->where(function ($query) {
                        $query->where('status', '=', 2)->orWhere('status', '=', 4);
                    });
                    break;
            }
        }


        switch ($params['ob'] ?? '') {
            case 't_asc':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        $query->orderBy('id', 'desc');
        $query->with(['codeInfo']);

        $list = $query->limit($size)
            ->offset(($page - 1) * $size)
            ->select(['id', 'redeem_code_id', 'status', 'created_at', DB::raw("concat('¥',$price) as price")])
            ->get();

        if (!empty($params['id']??0)){
            foreach ($list as $v){
                if($v->status == 3){
                    $base_url = ConfigModel::getData(26);
                    $base_url = parse_url($base_url);

                    //todo 分享二维码参数待定
                    $url_data = [
                        'c'=>$params['id'],
                        'r'=>str_random(10)
                    ];
                    $url_data = http_build_query($url_data);

                    $qr_url = $base_url['scheme'].'://'.$base_url['host'].$base_url['path'];
                    $qr_url = $qr_url.'?'.$url_data;

                    $qrModel = new CreatePosterController();
                    $qr_data = $qrModel->createQRcode($qr_url,true);

                    $v->qr_code = $qr_data;
                }
            }
        }

        return $list;
    }

    public function send($user, $params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if (empty($user['new_vip']['level'] ?? 0)) {
            return ['code' => false, 'msg' => '会员信息错误'];
        }

        $check = self::query()->whereId($params['id'])->where('user_id', '=', $user['id'])->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '兑换券不存在'];
        }
        if ($check->status != 1) {
            switch ($check->status) {
                case 2:
                    return ['code' => false, 'msg' => '兑换券已被使用'];
                case 3:
                    return ['code' => false, 'msg' => '兑换券状态错误'];
                case 4:
                    return ['code' => false, 'msg' => '兑换券已送出'];
            }
        }

        $check->status = 3;
        $res = $check->save();

        if ($res === false) {
            return ['code' => false, 'msg' => '失败,请重试'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }

    public function takeBack($user, $params)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if (empty($user['new_vip']['level'] ?? 0)) {
            return ['code' => false, 'msg' => '会员信息错误'];
        }
        $check = self::query()->whereId($params['id'])->where('user_id', '=', $user['id'])->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '兑换券不存在'];
        }
        if ($check->status != 3) {
            return ['code' => false, 'msg' => '兑换券状态错误'];
        }
        $check->status = 1;
        $res = $check->save();

        if ($res === false) {
            return ['code' => false, 'msg' => '失败,请重试'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }

    public function use($user, $params)
    {

    }

    public function codeInfo()
    {
        return $this->hasOne(VipRedeemCode::class, 'id', 'redeem_code_id')
            ->select(['id', 'name', 'number']);
    }
}
