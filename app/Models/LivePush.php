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

    public function changePushMsgState($params, $user_id)
    {
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = self::whereId($id)->where('user_id', '=', $user_id)
            ->where('is_del', '=', 0)
            ->select(['id', 'is_push'])
            ->first();

        switch ($params['flag'] ?? '') {
            case 'off':
                if ($check->is_done == 1) {
                    return ['code' => false, 'msg' => '已经推送,无法取消'];
                }
                $check->is_push = 0;
                break;
            case 'del':
                if ($check->is_push == 1) {
                    return ['code' => false, 'msg' => '取消之后删除'];
                }
                $check->is_del = 1;
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        $res = $check->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }

    }

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
            ->where('live_info_id', '=', $live_info_id)
            ->where('is_del', '=', 0);

        $list = $query
            ->select([
                'id', 'live_id', 'live_info_id', 'push_type',
                'push_gid', 'click_num', 'close_num', 'is_push', 'push_at', 'is_done', 'done_at',
                DB::raw("if(user_id=$user_id,1,0) as is_self")
            ])
            ->with(['infoOfColumn', 'infoOfWorks', 'infoOfGoods', 'infoOfOffline'])
            ->orderBy('id', 'desc')
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        if ($list->isEmpty()) {
            return [];
        }

        $list = $list->toArray();

        foreach ($list as &$v) {
            switch (intval($v['push_type'])) {
                case 1:
                case 7:
                    $v['info'] = $v['info_of_column'];
                    break;
                case 2:
                case 8:
                    $v['info'] = $v['info_of_works'];
                    break;
                case 3:
                    $v['info'] = $v['info_of_goods'];
                    break;
                case 4:
                    $v['info'] = $v['info_of_offline'];
                    break;
                case 6:
                    $v['info'] = [
                        'id' => 0,
                        'title' => '360幸福大使',
                        'subtitle' => '',
                        'cover_img' => '',
                        'price' => 360,
                        'with_type' => 6
                    ];
                    break;
            }
            unset($v['info_of_column'], $v['info_of_works'], $v['info_of_goods'], $v['info_of_offline']);

            $v['order_count'] = '暂无单';
            $v['money_count'] = '¥暂无';
            $v['push_at'] = date('Y-m-d H:i', strtotime($v['push_at']));
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
            ->select(['id', 'name as title', 'subtitle', 'picture as cover_img', 'price',
                DB::raw('3 as with_type')]);
    }

    //线下课
    public function infoOfOffline()
    {
        return $this->hasOne(OfflineProducts::class, 'id', 'push_gid')
            ->select(['id', 'title', 'subtitle', 'cover_img', 'price',
                DB::raw('4 as with_type')]);
    }


}
