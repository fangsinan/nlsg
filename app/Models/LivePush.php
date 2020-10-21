<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 1:36 PM
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class LivePush extends Base
{
    protected $table = 'nlsg_live_push';

    public function pushMsgList($params, $user_id)
    {
        $live_id = $params['live_id'] ?? 0;
        $live_info_id = $params['live_info_id'] ?? 0;
        $page = intval($params['page'] ?? 1);
        $size = intval($params['size'] ?? 10);

        if (empty($live_id) || empty($live_info_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check_is_admin = LiveConsole::isAdmininLive($user_id, $live_id);
        if ($check_is_admin === false) {
            return ['code' => false, 'msg' => '需要管理员权限'];
        }

        $query = self::where('live_id', '=', $live_id)
            ->where('live_info_id', '=', $live_info_id);

        $list = $query
            ->select([
                'id', 'live_id', 'live_info_id', 'push_type',
                'push_gid', 'click_num', 'close_num', 'is_push', 'push_at',
                DB::raw("if(user_id=$user_id,1,0) as is_self")
            ])
            ->with(['infoOfColumn','infoOfWorks','infoOfGoods','infoOfOffline'])
            ->orderBy('id', 'desc')
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        if ($list->isEmpty()){
            return [];
        }

        return $list;
    }

    // 1专栏 2精品课 3商品 4 线下产品门票类 6新会员 7:讲座 8:听书

    //讲座和专栏
    public function infoOfColumn()
    {
        return $this->hasOne(Column::class, 'id', 'push_gid')
            ->select(['id', 'name as title', 'subtitle', 'cover_pic as cover_img', 'price',
                DB::raw('if(type=1,1,7) as with_type')]);
    }

    //精品课和听书
    public function infoOfWorks()
    {
        return $this->hasOne(Works::class, 'id', 'push_gid')
            ->select(['id', 'title', 'subtitle', 'cover_img', 'price',
                DB::raw('if(is_audio_book=1,8,2) as with_type')]);
    }

    //商品
    public function infoOfGoods()
    {
        return $this->hasOne(MallGoods::class, 'id', 'push_gid')
            ->select(['id','name as title', 'subtitle', 'picture as cover_img', 'price',
                DB::raw('3 as with_type')]);
    }

    //线下课
    public function infoOfOffline()
    {
        return $this->hasOne(OfflineProducts::class, 'id', 'push_gid')
            ->select(['id','title', 'subtitle', 'cover_img', 'price',
                DB::raw('4 as with_type')]);
    }


}
