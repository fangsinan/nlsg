<?php


namespace App\Models;


use Illuminate\Support\Facades\DB;

class VipUser extends Base
{
    protected $table = 'nlsg_vip_user';

    public static function newVipInfo($user_id)
    {
        $now_date = date('Y-m-d H:i:s');
        $check = self::where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->where('start_time', '<', $now_date)
            ->where('expire_time', '>', $now_date)
            ->select(['id as vip_id', 'level', 'start_time', 'expire_time', 'is_open_360',
                'time_begin_360', 'time_end_360'])
            ->first();
        if (empty($check)) {
            return ['vip_id' => 0, 'level' => 0, 'start_time' => '', 'expire_time' => '', 'is_open_360' => 0];
        } else {
            return $check->toArray();
        }
    }

    public function homePage($user, $params)
    {
        //卡片(昵称,是否开通,到期天数,价格)
        $card_data['nickname'] = $user['nickname'];
        $card_data['headimg'] = $user['headimg'];
        $card_data['level'] = 0;
        $card_data['expire_time'] = '';
        $card_data['surplus_days'] = 0;
        $card_data['price'] = ConfigModel::getData(25);

        if (empty($user['new_vip']['level'] ?? 0)) {
            $card_data['is_open'] = 0;
        } else {
            $card_data['level'] = $user['new_vip']['level'];

            if ($user['new_vip']['level'] == 1) {
                $card_data['is_open'] = 1;
                $card_data['surplus_days'] = intval((strtotime($user['new_vip']['expire_time']) - time()) / 86400);
                $card_data['expire_time'] = $user['new_vip']['expire_time'];
            } else {
                if ($user['new_vip']['is_open_360'] == 1) {
                    $card_data['is_open'] = 1;
                    $card_data['surplus_days'] = intval((strtotime($user['new_vip']['time_end_360']) - time()) / 86400);
                    $card_data['expire_time'] = $user['new_vip']['time_end_360'];
                } else {
                    $card_data['is_open'] = 0;
                }
            }
        }

        //师资阵容
        $author_order_str = ConfigModel::getData(30);
        $author_order_str = 'FIELD(id,' . $author_order_str . ') desc';
        $author = User::where('is_author', '=', 1)->where('status', '=', 1)
            ->where('headimg', '<>', '')
            ->select(['id', 'nickname', 'headimg', 'intro_for_360'])
            ->orderByRaw($author_order_str)
            ->orderBy('id', 'asc')
            ->limit(10)
            ->get()
            ->toArray();

        //精品课
        $works_id_list = ConfigModel::getData(27);
        $works_order_str = 'FIELD(w.id,' . $works_id_list . ') asc';
        $works_list = DB::table('nlsg_works as w')
            ->leftJoin('nlsg_column as c','w.column_id','=','c.id')
            ->whereIn('w.id',explode(',', $works_id_list))
            ->where('w.status','=',4)
            ->orderByRaw($works_order_str)
            ->select(['w.id', 'w.type as works_type', 'w.title', 'w.subtitle',
                'w.cover_img', 'w.detail_img', 'w.price','c.type','c.column_type'])
            ->get()
            ->toArray();

        //长图
        $detail_image = ConfigModel::getData(28);

        $res['card_data'] = $card_data;
        $res['author'] = ['cover_img'=>ConfigModel::getData(31),'list'=>$author];
        $res['works_list'] = ['cover_img'=>ConfigModel::getData(32),'list'=>$works_list];
        $res['detail_image'] = $detail_image;
        return $res;
    }


    public function IsNewVip($uid)
    {
        if (!$uid) return false;
        $UserInfo = VipUser::where(['user_id' => $uid, 'is_default' => 1, 'status' => 1])->get('level', 'expire_time');

        //return $model->getLastQuery();
        $time = date('Y-m-d', (time() + 86400));
        //判断会员
        if (!empty($UserInfo) && in_array($UserInfo->level, [1, 2,]) && $UserInfo->expire_time > $time) { //会员
            return $UserInfo->level;
        } else {
            return 0;
        }
    }
}
