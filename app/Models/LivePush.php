<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/25
 * Time: 1:36 PM
 */


namespace App\Models;

use App\Servers\JobServers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class LivePush extends Base
{
    protected $table = 'nlsg_live_push';
   protected $fillable = [
       'id', 'live_id', 'live_info_id','user_id','push_type', 'push_gid','is_push','is_done','push_at', 'done_at'
   ];
    public function add($params, $user_id)
    {
        $live_id = $params['live_id'] ?? 0;
        $live_info_id = $params['live_info_id'] ?? 0;
        $push_type = $params['type'] ?? 0;
        $push_gid = $params['gid'] ?? 0;
        $push_at = $params['time'] ?? 0;
        $length = $params['length'] ?? 7200;
        $now = time();

        if (empty($live_id) || empty($live_info_id) || empty($push_gid) || empty($push_type)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if (empty($push_at) || strtotime($push_at) === false) {
            return ['code' => false, 'msg' => '时间错误'];
        }

        if ( ! in_array($push_type, [1, 2, 3, 4, 6, 7, 8, 9, 10, 11])) {
            return ['code' => false, 'msg' => '类型错误'];
        }

        $check_live_info = LiveInfo::whereId($live_info_id)
            ->where('live_pid', '=', $live_id)
            ->where('status', '=', 1)
            ->select(['id', 'is_begin', 'is_finish', 'user_id'])
            ->first();

        if (empty($check_live_info)) {
            return ['code' => false, 'msg' => '直播不存在'];
        }

        if ($check_live_info->is_begin == 0) {
            return ['code' => false, 'msg' => '直播未开始'];
        }

        if ($check_live_info->is_finish == 1) {
            return ['code' => false, 'msg' => '直播已结束'];
        }

        $check_is_admin = LiveConsole::isAdmininLive($user_id, $live_id);
        if ($check_is_admin === false) {
            return ['code' => false, 'msg' => '需要管理员权限'];
        }

//        $check_push = LivePush::where('live_id', '=', $live_id)
//            ->where('live_info_id', '=', $live_info_id)
//            ->where('is_del', '=', 0)
//            ->whereBetween('push_at', [
//                date('Y-m-d H:i:s', strtotime("$push_at -65 second")),
//                date('Y-m-d H:i:s', strtotime("$push_at +65 second"))
//            ])
//            ->select(['id'])
//            ->first();

        $send_timestamp = strtotime($push_at) + 4;

        $check_sql = "select * from (
SELECT
	id,
	live_id,
	live_info_id,
	push_at,
	UNIX_TIMESTAMP( push_at ) + length AS s_end,
	UNIX_TIMESTAMP( push_at ) AS s_begin
FROM
	nlsg_live_push
WHERE
	live_id = $live_id
	AND live_info_id = $live_info_id
	AND is_push = 1) as a
	where s_begin  <= $send_timestamp
	and s_end > $send_timestamp limit 1";
        $check_push = DB::select($check_sql);

        if (empty($params['id'] ?? 0)) {
            if ( ! empty($check_push)) {
                return ['code' => false, 'msg' => '所选时间已有推送内容,请更换时间.'];
            }
        } else {
            if ($params['id'] != $check_push->id) {
                return ['code' => false, 'msg' => '所选时间已有推送内容,请更换时间.'];
            }
        }

        if (empty($params['id'] ?? 0)) {
            $model = new LivePush();
        } else {
            $model = LivePush::whereId($params['id'])->where('user_id', '=', $user_id)->first();
            if (empty($model)) {
                return ['code' => false, 'msg' => '需本人修改'];
            }
            if ($model->is_done == 1) {
                return ['code' => false, 'msg' => '已经推送,无法修改'];
            }
        }

        $model->live_id = $live_id;
        $model->live_info_id = $live_info_id;
        $model->user_id = $user_id;
        $model->push_type = $push_type;
        $model->push_gid = $push_gid;
        $model->push_at = date('Y-m-d H:i:s', strtotime($push_at) + 4);
        $model->length = $length;;

        $res = $model->save();

        $this->getPushWorks($live_id, $push_type, $push_gid);

        if ($res) {
            JobServers::pushToSocket($live_id, $live_info_id, 6);
            return ['code' => true, 'msg' => '添加成功'];
        } else {
            return ['code' => false, 'msg' => '失败,请重试'];
        }

    }

    public function changeState($params, $user_id)
    {
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = self::whereId($id)->where('user_id', '=', $user_id)
            ->where('is_del', '=', 0)
            ->select(['id', 'is_push', 'live_id', 'live_info_id'])
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
            JobServers::pushToSocket($check->live_id, $check->live_info_id, 6);
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }

    }

    public function list($params, $user_id)
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

        if (empty($params['id'] ?? 0)) {
            $query = self::query();
        } else {
            $query = self::whereId($params['id']);
        }

        $query->where('live_id', '=', $live_id)
            ->where('live_info_id', '=', $live_info_id)
            ->where('is_del', '=', 0)
            ->select([
                'id', 'live_id', 'live_info_id', 'push_type',
                'push_gid', 'click_num', 'close_num', 'is_push', 'push_at', 'is_done', 'done_at',
                DB::raw("if(user_id=$user_id,1,0) as is_self")
            ])
            ->with(['liveInfo', 'infoOfColumn', 'infoOfWorks', 'infoOfGoods', 'infoOfOffline']);

        switch ($params['ob'] ?? '') {
            case 't_asc':
                $query->orderBy('id', 'asc');
                break;
            default:
                $query->orderBy('id', 'desc');
        }

        $list = $query->limit($size)
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
                        'id'        => 0,
                        'title'     => '360幸福大使',
                        'subtitle'  => '',
                        'cover_img' => ConfigModel::getData(22),
                        'price'     => 360,
                        'with_type' => 6
                    ];
                    break;
            }

            unset($v['info_of_column'], $v['info_of_works'], $v['info_of_goods'], $v['info_of_offline']);

            //todo 统计数据
            $v['order_count'] = '暂无';
            $v['money_count'] = '暂无';
            $v['push_at'] = date('Y-m-d H:i', strtotime($v['push_at']));
        }

        return $list;
    }

    // 1专栏 2精品课 3商品 4 线下产品门票类 6新会员 7:讲座 8:听书

    //讲座和专栏
    public function infoOfColumn()
    {
        return $this->hasOne(Column::class, 'id', 'push_gid')
            ->select([
                'id', 'name as title', 'subtitle', 'cover_pic as cover_img', 'price',
                DB::raw('if(type=1,1,7) as with_type')
            ]);
    }

    //精品课和听书
    public function infoOfWorks()
    {
        return $this->hasOne(Works::class, 'id', 'push_gid')
            ->select([
                'id', 'title', 'subtitle', 'cover_img', 'price',
                DB::raw('if(is_audio_book=1,8,2) as with_type')
            ]);
    }

    //商品
    public function infoOfGoods()
    {
        return $this->hasOne(MallGoods::class, 'id', 'push_gid')
            ->select([
                'id', 'name as title', 'subtitle', 'picture as cover_img', 'price',
                DB::raw('3 as with_type')
            ]);
    }

    //线下课
    public function infoOfOffline()
    {
        return $this->hasOne(OfflineProducts::class, 'id', 'push_gid')
            ->select([
                'id', 'title', 'subtitle', 'cover_img', 'price',
                DB::raw('4 as with_type')
            ]);
    }

    public function liveInfo()
    {
        return $this->hasOne(Live::class, 'id', 'live_id')
            ->select(['id', 'title']);
    }

    public function getPushWorks($live_id = 0, $push_type = 7, $push_id = 0)
    {
        $cache_live_name = 'live_push_works_'.$live_id;
        $data = Cache::get($cache_live_name);
        if (false && $data) {
            foreach ($data as $v) {
                if ($v['id'] != $push_id && $v['type'] != $push_type) {
                    Cache::forget('live_push_works_'.$live_id);
                    $this->getWorksList($live_id);
                    break;
                }
            }
        }
        $data = $this->getWorksList($live_id);
        return $data;
    }

    public function getWorksList($live_id = 0)
    {
        if ( ! $live_id) {
            return false;
        }
        $cache_live_name = 'live_push_works_'.$live_id;
        $data = Cache::get($cache_live_name);

        $lists = LivePush::select('id', 'live_id', 'push_type', 'push_gid', 'is_del')
            ->where('live_id', $live_id)
            ->orderBy('push_at', 'desc')
            ->groupBy('push_type', 'push_gid')
            ->get()
            ->toArray();

        if ($lists) {
            $data = [];
            foreach ($lists as $v) {
                if ($v['push_type'] == 7 || $v['push_type'] == 11) {
                    $res = Column::select('id', 'name as title', 'subtitle', 'original_price', 'price',
                        'cover_pic as cover_img','index_pic','details_pic')
                        ->where('id', $v['push_gid'])
                        //->where('type', 2)
                        ->where('status', 1)
                        ->first();
                    if (!$res){
                        continue;
                    }
                    $res->type = 1;
                    if($v['push_type'] == 11){
                        $res->type = 11;
                    }
                    $res = $res->toArray();
                } elseif ($v['push_type'] == 2) {
                    $res = Works::select('id', 'title', 'subtitle', 'cover_img', 'original_price', 'price')
                        ->where('id', $v['push_gid'])
                        ->where('status', 4)
                        ->first();
                    $res->type = 2;
                    $res = $res->toArray();
                } elseif ($v['push_type'] == 3) {
                    $res = MallGoods::select('id', 'name as title', 'subtitle', 'picture as cover_img',
                        'original_price', 'price')
                        ->where('id', $v['push_gid'])
                        ->first();
                    $res->type = 3;
                    $res = $res->toArray();
                } elseif ($v['push_type'] == 6) {
                    $res = [
                        'id'            => 999999,
                        'title'         => '幸福360会员',
                        'price'         => 360.00,
                        'cover_img'     => '/live/recommend/360_xhc.png',
                        'cover_details' => '/live/recommend/360_tc.png',
                        'type'          => 4
                    ];
                } elseif ($v['push_type'] == 4) {
                    $res = OfflineProducts::select('id', 'title', 'subtitle', 'cover_img',
                        'image as cover_details', 'total_price as original_price', 'price','off_line_pay_type')
                        ->where('id', $v['push_gid'])
                        ->first();
                    $res->type = 5;
                    $res = $res->toArray();
                } elseif ($v['push_type'] == 9) {
                    $res = Live::select('id', 'title', 'describe as subtitle', 'cover_img','is_free','price as original_price','price')
                        ->where('id', $v['push_gid'])
                        ->first();
                    if ($res){
                        $infoid =LiveInfo::where('live_pid', $res->id)->value('id');
                        $res->live_info_id = $infoid ??  0;
                    }
                    $res->type = 9;
                    $res = $res->toArray();
                } elseif ($v['push_type'] == 10) {
                    $res = LiveUrl::select('id', 'name', 'describe', 'url', 'image', 'img', 'user_id')
                        ->where('id', $v['push_gid'])
                        ->first();
                    if ($res){
                        $infoid =LiveInfo::where('live_pid', $res->id)->value('id');
                        $res->live_info_id = $infoid ??  0;
                    }
                    $res->type = 10;
                    $res = $res->toArray();
                }
                $data[] = $res ?? [];
            }
            $expire_num = CacheTools::getExpire('live_push_works');
            Cache::put($cache_live_name, $data, $expire_num);

            return $data;
        }


    }

    public static function parsePushList($data =[])
    {
        if (!$data){
            return  false;
        }
        foreach ($data as $v) {
            if ($v['push_type'] == 7) {
                $res = Column::select('id', 'name as title', 'subtitle', 'original_price', 'price',
                    'cover_pic as cover_img')
                    ->where('id', $v['push_gid'])
                    ->where('type', 2)
                    ->where('status', 1)
                    ->first();
                $res->type = 1;
                $res = $res->toArray();
            } elseif ($v['push_type'] == 2) {
                $res = Works::select('id', 'title', 'subtitle', 'cover_img', 'original_price', 'price')
                    ->where('id', $v['push_gid'])
                    ->where('status', 4)
                    ->first();
                $res->type = 2;
                $res = $res->toArray();
            } elseif ($v['push_type'] == 3) {
                $res = MallGoods::select('id', 'name as title', 'subtitle', 'picture as cover_img',
                    'original_price', 'price')
                    ->where('id', $v['push_gid'])
                    ->first();
                $res->type = 3;
                $res = $res->toArray();
            } elseif ($v['push_type'] == 6) {
                $res = [
                    'id'            => 999999,
                    'title'         => '幸福360会员',
                    'price'         => 360.00,
                    'cover_img'     => '/live/recommend/360_xhc.png',
                    'cover_details' => '/live/recommend/360_tc.png',
                    'type'          => 4
                ];
            } elseif ($v['push_type'] == 4) {
                $res = OfflineProducts::select('id', 'title', 'subtitle', 'cover_img',
                    'image as cover_details', 'total_price as original_price', 'price')
                    ->where('id', $v['push_gid'])
                    ->first();
                $res->type = 5;
                $res = $res->toArray();
            }
            $arr[] = $res ?? [];
        }

        return  $arr;
    }
}
