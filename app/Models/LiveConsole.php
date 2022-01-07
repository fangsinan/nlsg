<?php


namespace App\Models;

use App\Servers\JobServers;
use Illuminate\Support\Facades\DB;

class LiveConsole extends Base
{

    protected $table = 'nlsg_live';

    //校验助手是否合法
    public function checkHelper($params, $user_id)
    {
        if ($params['helper'] ?? false) {
            $helper = preg_replace('/[^0-9]/i', ',', $params['helper']);
            $helper = explode(',', $helper);

            $check_user = User::whereIn('phone', $helper)->select(['id', 'phone'])->get();
            if ($check_user->isEmpty()) {
                return ['code' => false, 'msg' => '未查询到该手机号信息'];
            } else {
                $check_user = $check_user->toArray();
                $check_user = array_column($check_user, 'phone');

                $diff = array_diff($helper, $check_user);
                if ($diff) {
                    return ['code' => false, 'msg' => implode(',', $diff) . '不是注册账号'];
                } else {
                    return ['code' => true, 'msg' => '成功'];
                }
            }
        } else {
            return ['code' => true, 'msg' => '没有数据'];
        }

    }

    public function changeStatus($params, $user_id)
    {
        if (empty($params['id'] ?? 0)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $live = Live::whereId($params['id'])->where('user_id', $user_id)
            ->first();
        if (empty($live)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        switch ($params['flag'] ?? '') {
            case 'off';
                $data['status'] = 2;
                break;
            case 'del':
                if ($live->status == 2) {
                    $data['is_del'] = 1;
                } else {
                    return ['code' => false, 'msg' => '状态错误'];
                }
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }
        $data['updated_at'] = date('Y-m-d H:i:s');

        $res = Live::whereId($params['id'])->update($data);

        if ($res === false) {
            return ['code' => false, 'msg' => '错误,请重试'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }

    //校验用户是否有创建直播的权限
    public static function checkUserPrivilege($user_id = 0)
    {
        if ($user_id) {
            $check = LiveUserPrivilege::where('user_id', '=', $user_id)
                ->where('pri_level', '=', 1)
                ->where('privilege', '=', 2)
                ->where('is_del', '=', 0)
                ->first();
            if ($check) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //校验字符串是否含有emoji表情
    function haveEmojiChar($str)
    {
        $mbLen = mb_strlen($str);

        $strArr = [];
        for ($i = 0; $i < $mbLen; $i++) {
            $strArr[] = mb_substr($str, $i, 1, 'utf-8');
            if (strlen($strArr[$i]) >= 4) {
                return true;
            }
        }
        return false;
    }

    public function add($params, $user_id)
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $check_user = self::checkUserPrivilege($user_id);
        if ($check_user === false) {
            return ['code' => false, 'msg' => '无权限'];
        }

        //必须是注册账号
        if ($params['helper'] ?? false) {
            $check_helper = $this->checkHelper($params, $user_id);
            if ($check_helper['code'] === false) {
                return $check_helper;
            }
        }

        $live_data['user_id'] = $user_id;
        $live_data['is_show'] = $params['is_show'] ?? 1;
        if (($params['is_free'] ?? 0) == 0) {
            if (($params['price'] ?? 0) <= 0) {
                return ['code' => false, 'msg' => '金额错误'];
            }
            $live_data['price'] = $params['price'];
        } else {
            $live_data['price'] = 0;
        }
        $live_data['playback_price'] = $params['playback_price'] ?? 0;
        $live_data['twitter_money'] = $params['twitter_money'] ?? 0;

        if (empty($params['title'] ?? '')) {
            return ['code' => false, 'msg' => '直播名称错误'];
        } else {
            $check_title = $this->haveEmojiChar($params['title']);
            if ($check_title) {
                return ['code' => false, 'msg' => '直播名称非法字符'];
            }
        }
        if (empty($params['describe'] ?? '')) {
            return ['code' => false, 'msg' => '直播简介错误'];
        }
        if (empty($params['cover_img'] ?? '')) {
            return ['code' => false, 'msg' => '直播封面错误'];
        } else {
            $params['cover_img'] = parse_url($params['cover_img']);
            $params['cover_img'] = $params['cover_img']['path'] ?? '';
            if (empty($params['cover_img'])) {
                return ['code' => false, 'msg' => '直播封面错误'];
            }
        }

        if (empty($params['msg'] ?? '')) {
            return ['code' => false, 'msg' => '直播预约公告错误'];
        }
        if (!empty($params['helper'] ?? '')) {
            $live_data['helper'] = preg_replace('/[^0-9]/i', ',', $params['helper']);
        }
        if (!empty($params['password'] ?? '')) {
            $live_data['password'] = bcrypt(trim($params['password']));
        }
        $live_data['title'] = $params['title'];
        $live_data['describe'] = $params['describe'];
        $live_data['cover_img'] = $params['cover_img'];
        $live_data['msg'] = $params['msg'];
        $live_data['content'] = $params['content'] ?? '';
        $live_data['can_push'] = $params['can_push'] ?? 0;
        $live_data['is_free'] = $params['is_free'];
        $live_begin_at = 0;
        $live_end_at = 0;

        if (empty($params['list'] ?? '')) {
            return ['code' => false, 'msg' => '直播时间信息错误'];
        }

        if (!is_string($params['list'])) {
            return ['code' => false, 'msg' => '直播时间格式错误'];
        }
        $params['list'] = json_decode($params['list'], true);
        if (!is_array($params['list'] ?? '')) {
            return ['code' => false, 'msg' => '直播时间信息错误'];
        }
        if (count($params['list']) != 1) {
            return ['code' => false, 'msg' => '直播时间信息过多'];
        }
        //编辑时判断info_id
        foreach ($params['list'] as $v) {
            if (!empty($params['id'])) {
                if (empty($v['id'])) {
                    return ['code' => false, 'msg' => '编辑数据格式错误'];
                }
            }
        }

        $p_l_time = array_column($params['list'], 'begin_at');
        array_multisort($p_l_time, SORT_ASC, $params['list']);

        foreach ($params['list'] as $k => $v) {
            if (empty($v['end_at'] ?? '')) {
                if (is_numeric($v['length'])) {
                    $params['list'][$k]['end_at'] = date('Y-m-d H:i:s',
                        strtotime($v['begin_at'] . " +" . intval(floatval($v['length']) * 60) . ' minutes')
                    );
                } else {
                    return ['code' => false, 'msg' => '时长错误'];
                }
            }
        }

        foreach ($params['list'] as $k => $v) {
            if ($v['end_at'] < $now_date) {
                unset($params['list'][$k]);
            } else {
                if ($live_begin_at === 0) {
                    $live_begin_at = $v['begin_at'];
                }
                if ($live_end_at === 0) {
                    $live_end_at = $v['end_at'];
                }
                if ($v['begin_at'] < $live_begin_at) {
                    $live_begin_at = $v['begin_at'];
                }
                if ($v['end_at'] > $live_end_at) {
                    $live_end_at = $v['end_at'];
                }
                if (empty($v['id'] ?? 0)) {
                    unset($params['list'][$k]['id']);
                }
                $temp_push_end_time = date('Y-m-d 23:59:59',
                    strtotime($v['end_at'] . " +1 days")
                );

                $params['list'][$k]['push_end_time'] = $temp_push_end_time;

//                $temp_get_url = $this->getPushUrl(
//                    rand(100, 999) . $user_id . $now, strtotime($temp_push_end_time)
//                );
                $temp_get_url = $this->getPushUrl(
                    md5($user_id . $temp_push_end_time), strtotime($temp_push_end_time)
                );
                $params['list'][$k]['push_live_url'] = $temp_get_url['push_url'];
                $params['list'][$k]['live_url'] = $temp_get_url['play_url'];
                $params['list'][$k]['live_url_flv'] = $temp_get_url['play_url_flv'];
            }
        }

        $live_data['begin_at'] = $live_begin_at;
        $live_data['end_at'] = $live_end_at;

        DB::beginTransaction();

        if (!empty($params['id'] ?? 0)) {
            $liveModel = self::whereId($params['id'])->first();
            if (!$liveModel) {
                return ['code' => false, 'msg' => 'id错误'];
            }
            $live_data['updated_at'] = $now_date;
            $check_live_type = LiveInfo::where('live_pid', '=', $params['id'])
                ->where('end_at', '<', $now_date)
                ->where('status', '=', 1)
                ->first();
            if ($check_live_type) {
                $live_data['type'] = 2;
            } else {
                $live_data['type'] = 1;
            }
            if (empty($params['list'])) {
                $live_data['is_finish'] = 0;
            }
            $live_res = $liveModel->whereId($params['id'])->update($live_data);
            if ($live_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '添加错误', 'ps' => __LINE__];
            }
            $live_id = $params['id'];

            if (1) {
                //直播只能单场,修改为删除所有已有直播场次
                $info_del_res = LiveInfo::where('live_pid', '=', $params['id'])
                    ->where('status', '=', 1)
                    ->delete();
            } else {
                //删除未开始的直播
                $info_del_res = LiveInfo::where('live_pid', '=', $params['id'])
                    ->where('begin_at', '>', $now_date)
                    ->where('status', '=', 1)
                    ->delete();
            }

            if ($info_del_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '添加失败', 'ps' => __LINE__];
            }
        } else {
            $live_data['status'] = 1;
            $live_data['created_at'] = $live_data['updated_at'] = $now_date;
            //计算单场多场
            if (count($params['list']) > 1) {
                $live_data['type'] = 2;
            } else {
                $live_data['type'] = 1;
            }
            $live_res = DB::table('nlsg_live')->insertGetId($live_data);
            if ($live_res) {
                $live_id = $live_res;
            } else {
                DB::rollBack();
                return ['code' => false, 'msg' => '添加错误', 'ps' => __LINE__];
            }
        }

        foreach ($params['list'] as &$v) {
            $v['live_pid'] = $live_id;
            $v['id'] = $live_id;
            $v['user_id'] = $user_id;
            $v['created_at'] = $v['updated_at'] = $now_date;
        }

        $info_res = DB::table('nlsg_live_info')->insert($params['list']);
        if ($info_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '添加错误', 'ps' => __LINE__];
        }

        //写入权限表
        if (!empty($params['helper'])) {
            $helper_list = explode(',', $params['helper']);
            $helper_user_list = User::whereIn('phone', $helper_list)->select(['id'])->get()->toArray();
            $helper_user_list = array_column($helper_user_list, 'id');

            $helper_add_data = [];
            foreach ($helper_user_list as $hv) {
                $check_hl = LiveUserPrivilege::where('user_id', '=', $hv)->where('is_del', '=', 0)->first();
                if (!$check_hl) {
                    $temp_helper_add_data['user_id'] = $hv;
                    $temp_helper_add_data['pri_level'] = 1;
                    $temp_helper_add_data['created_at'] = $now_date;
                    $helper_add_data[] = $temp_helper_add_data;
                }
            }
            if ($helper_add_data) {
                $helper_res = DB::table('nlsg_live_user_privilege')->insert($helper_add_data);
                if ($helper_res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '添加错误', 'ps' => __LINE__];
                }
            }
        }

        DB::commit();
        return ['code' => true, 'msg' => '已提交，请您耐心等待审核。'];
    }

    public function info($id, $user_id)
    {
        if (empty($id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $live = self::whereId($id)
            ->where('user_id', $user_id)
            ->select(['id', 'title', 'describe', 'cover_img', 'status', 'msg', 'content', 'created_at',
                'twitter_money', 'reason', 'check_time', 'price', 'playback_price', 'helper', 'is_free',
                'is_show', 'can_push', 'is_finish', 'user_id'])
            ->with(['infoList', 'userInfo'])
            ->first();
        if (empty($live)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $live->statistics = $this->liveStatistisc($id, $user_id);
        return $live;
    }

    //todo 直播的相关统计
    public function liveStatistisc($live_id, $user_id)
    {
        $live_info = LiveInfo::where('live_pid', '=', $live_id)->select(['id'])->get()->toArray();
        $live_info_id = array_column($live_info, 'id');

        return [
            ['key' => '预约人数', 'value' => LiveCountDown::whereIn('live_id', $live_info_id)->count()],
            ['key' => '观看人数', 'value' => LiveWatchRecord::where('live_id', '=', $live_id)->count()],
            ['key' => '打赏人数', 'value' => Order::where('type', '=', 5)->where('relation_id', '=', $live_id)->where('status', '=', 1)->count()],
            ['key' => '打赏金额', 'value' => Order::where('type', '=', 5)->where('relation_id', '=', $live_id)->where('status', '=', 1)->sum('price')],
//            ['key' => '分销收入', 'value' => '100元'],
//            ['key' => '报名收入', 'value' => '100元'],
//            ['key' => '回放收入', 'value' => '100元'],
            ['key' => '报名流水', 'value' => Order::where('type', '=', 10)->where('relation_id', '=', $live_id)->where('status', '=', 1)->sum('price')],
//            ['key' => '回放流水', 'value' => '100元'],
        ];
    }

    public function infoList()
    {
        return $this->hasMany('App\Models\LiveInfo', 'live_pid', 'id')
            ->where('status', '=', 1)
            ->select(['id', 'begin_at', 'end_at', 'length', 'live_pid', 'playback_url', 'is_finish']);
    }

    public function userInfo()
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->select(['id', 'nickname']);
    }

    public function list($params, $user_id)
    {
        //全部,待审核,待直播,已结束,已取消(直播状态 1:待审核  2:已取消 3:已驳回  4:通过)

        //list_flag = 1 待审核 status = 1
        //list_flag = 2 已取消 status = 2,3
        //list_flag = 3 待直播 status = 4  and info end_time > now
        //list_flag = 4 已结束 status = 4 and info end_time < now

        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $page = intval($params['page'] ?? 1);
        $size = intval($params['size'] ?? 10);

        $query = self::from('nlsg_live as l')
            ->leftJoin('nlsg_user as u', 'l.user_id', '=', 'u.id')
            ->where('l.user_id', '=', $user_id)
            ->where('l.is_del', '=', 0);

        $fields = ['l.id', 'l.title', 'l.describe', 'l.cover_img', 'l.status', 'l.msg', 'l.content', 'l.twitter_money',
            'l.reason', 'l.check_time', 'l.price', 'l.playback_price', 'l.helper', 'l.is_free', 'l.is_show', 'l.can_push',
            'u.nickname', 'l.end_at', DB::raw('(SELECT count(1)*2 = SUM(`status`)
            from nlsg_live_info where live_pid = l.id) as all_pass_flag')];

        switch (intval($params['list_flag'] ?? 0)) {
            case 1:
                $query->where('l.status', '=', 1);
                break;
            case 2:
                $query->whereIn('l.status', [2, 3]);
                break;
            case 3:
                $query->where('l.status', '=', 4)
                    ->where('l.end_at', '>', $now_date)
                    ->whereRaw(
                        '(SELECT count(1)*2 = SUM(`status`) from nlsg_live_info where live_pid = l.id) = 0'
                    );
                break;
            case 4:
                $query->where(function ($query) use ($params, $now_date) {
                    $query->whereRaw('(l.status = 4 and l.end_at < "' . $now_date . '")')
                        ->orWhereRaw('((SELECT count(1)*2 = SUM(`status`) from nlsg_live_info where live_pid = l.id) = 1)');
                });
                break;
        }

        $list = $query
            ->with(['infoList'])
            ->select($fields)
            ->orderBy('l.id', 'desc')
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        foreach ($list as &$v) {
            if ($v->status == 1) {
                $v->list_flag = 1;
            } elseif ($v->status == 2 || $v->status == 3) {
                $v->list_flag = 2;
            } else {
                if ($v->end_at <= $now_date) {
                    $v->list_flag = 4;
                } else {
                    $v->list_flag = 3;
                }
                if ($v->all_pass_flag == 1) {
                    $v->list_flag = 4;
                }
            }
        }

        return $list;
    }

    public function listNew($params, $user_id)
    {
        $page = intval($params['page'] ?? 1);
        $size = intval($params['size'] ?? 10);

        $query = self::from('nlsg_live as l')
            ->leftJoin('nlsg_user as u', 'l.user_id', '=', 'u.id')
            ->where('l.user_id', '=', $user_id)
            ->where('l.is_del', '=', 0);

        $fields = ['l.id', 'l.title', 'l.describe', 'l.cover_img', 'l.status', 'l.msg', 'l.content', 'l.twitter_money',
            'l.reason', 'l.check_time', 'l.price', 'l.playback_price', 'l.helper', 'l.is_free', 'l.is_show',
            'l.can_push', 'u.nickname', 'l.end_at', 'l.is_finish'];

        switch (intval($params['list_flag'] ?? 0)) {
            case 1:
                $query->where('l.status', '=', 1);
                break;
            case 2:
                $query->whereIn('l.status', [2, 3]);
                break;
            case 3:
                $query->where('l.status', '=', 4)->where('l.is_finish', '=', 0);
                break;
            case 4:
                $query->where('l.status', '=', 4)->where('l.is_finish', '=', 1);
                break;
        }

        $list = $query
            ->with(['infoList'])
            ->select($fields)
            ->orderBy('l.id', 'desc')
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();

        foreach ($list as &$v) {
            if ($v->status == 1) {
                $v->list_flag = 1;
            } elseif ($v->status == 2 || $v->status == 3) {
                $v->list_flag = 2;
            } else {
                if ($v->is_finish == 1) {
                    $v->list_flag = 4;
                } else {
                    $v->list_flag = 3;
                }
            }
        }

        return $list;
    }

    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param domain 您用来推流的域名
     *        streamName 您用来区别不同推流地址的唯一流名称
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    function getPushUrl($streamName, $time = null)
    {
        $key = config('env.Live_API_KEY');
        $push_url = config('env.LIVE_PUSH_URL');
        $play_url = config('env.LIVE_PLAY_URL');

        if ($key && $time) {
            $txTime = strtoupper(base_convert(($time), 10, 16));
            $txSecret = md5($key . $streamName . $txTime);
            $ext_str = "?" . http_build_query(array(
                    "txSecret" => $txSecret,
                    "txTime" => $txTime
                ));
        }

        return [
            'push_url' => "rtmp://" . $push_url . "/live/" . $streamName . (isset($ext_str) ? $ext_str : ""),
            'play_url' => "http://" . $play_url . "/live/" . $streamName . '.m3u8' . (isset($ext_str) ? $ext_str : ""),
            'play_url_flv' => "http://" . $play_url . "/live/" . $streamName . '.flv' . (isset($ext_str) ? $ext_str : ""),
        ];
    }

    /*****************************直播画面页部分***************************************/

    /**
     * 校验用户是否为指定直播的管理员
     * @param $user_id
     * @param $live_id
     * @return bool
     */
    public static function isAdmininLive($user_id, $live_id)
    {
        $live_info = Live::whereId($live_id)->select(['user_id', 'helper'])->first();

        if (empty($live_info)) {
            return false;
        }

        if ($user_id == $live_info->user_id) {
            return true;
        }

        $user_info = User::whereId($user_id)->select(['phone'])->first();
        $helper = explode(',', $live_info->helper);
        if (empty($user_info) || empty($helper)) {
            return false;
        }
        if (in_array($user_info->phone, $helper)) {
            return true;
        } else {
            return false;
        }
    }

    public function changeInfoState($params, $user_id)
    {
        $live_id = $params['live_id'] ?? 0;
        $live_info_id = $params['live_info_id'] ?? 0;
        $flag = $params['flag'] ?? 0;
        if (empty($live_id) || empty($live_info_id) || empty($flag)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        if (!in_array($flag, ['on', 'off', 'finish'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = LiveInfo::whereId($live_info_id)
            ->where('live_pid', $live_id)
            ->where('user_id', $user_id)
            ->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => '直播不存在'];
        }


        if (!empty($check['task_id'])) {
            return ['code' => false, 'msg' => '直播正在自动推流中，请在管理后台关闭'];
        }
        switch ($flag) {
            case 'on':
                $data['is_begin'] = 1;
                $data['begin_status'] = 0;//socket推送状态
                break;
            case 'off':
                $data['is_begin'] = 0;
                $data['begin_status'] = 1;//推送状态
                break;
            case 'finish':
                $data['is_begin'] = 0;
                $data['begin_status'] = 1;//推送状态
                $data['is_finish'] = 1;
                $data['finished_at'] = date('Y-m-d H:i:s');
                break;
        }

        DB::beginTransaction();

        $info_res = LiveInfo::whereId($live_info_id)->update($data);
        if ($info_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试'];
        }
        if ($flag == 'finish') {
            $check_all_finish = LiveInfo::where('live_pid', $live_id)
                ->where('status', 1)
                ->where('is_finish', 0)
                ->select(['id'])
                ->first();

            if (empty($check_all_finish)) {
                $live_res = self::whereId($live_id)->update([
                    'is_finish' => 1,
                    'finished_at' => date('Y-m-d H:i:s')
                ]);
                if ($live_res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败,请重试'];
                }
            }
        }

        DB::commit();
        JobServers::pushToSocket($live_id, $live_info_id, 8);
        return ['code' => true, 'msg' => '成功'];
    }

    public function begin($params)
    {
        $live_id = $params['live_id'] ?? 0;
        $job_type = $params['job_type'] ?? 0;
        if (empty($live_id) || empty($job_type)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $check = LiveConsole::where('id', '=', $live_id)->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => 'id error'];
        }

        $now_date = date('Y-m-d H:i:s');

        //1开始  2恢复未开始  3结束
        switch (intval($job_type)) {
            case 1:
                $res = LiveInfo::where('live_pid', '=', $live_id)
                    ->update([
                        'is_begin' => 1,
                        'is_finish' => 0
                    ]);
                break;
            case 2:
                $res = LiveInfo::where('live_pid', '=', $live_id)
                    ->update([
                        'is_begin' => 0,
                        'is_finish' => 0,
                        'finished_at' => null
                    ]);
                Live::where('id', '=', $live_id)
                    ->update([
                        'is_finish' => 0,
                        'finished_at' => null,
                    ]);
                break;
            case 3:
                $res = LiveInfo::where('live_pid', '=', $live_id)
                    ->update([
                        'is_begin' => 0,
                        'is_finish' => 1,
                        'finished_at' => $now_date,
                    ]);

                Live::where('id', '=', $live_id)
                    ->update([
                        'is_finish' => 1,
                        'finished_at' => $now_date,
                        'virtual_online_num' => 0,
                    ]);
                break;
            default:
                return ['code' => false, 'msg' => 'job_type error'];
        }

        if ($res === false) {
            return ['code' => false, 'msg' => 'error'];
        } else {
            return ['code' => true, 'msg' => 'ok'];
        }

    }

    public function LiveAutoConfig()
    {
        $date_begin = date('Y-m-d H:i:00');
        $date_end = date('Y-m-d H:i:59');

//        $date_begin = '2021-07-01 00:00:00';
//        $date_end = '2021-07-01 00:00:59';

        //自动开始的
        $begin_list_sql = "select l.id as live_id from nlsg_live as l
join nlsg_live_info as li on l.id = li.live_pid and li.task_id <> 0 and li.is_begin = 0
where l.status = 4
and l.steam_begin_time >= '$date_begin'
and l.steam_begin_time <= '$date_end'";

        $begin_list = DB::select($begin_list_sql);

        if (!empty($begin_list)) {
            foreach ($begin_list as $v) {
                $temp = [];
                $temp['live_id'] = $v->live_id;
                $temp['job_type'] = 1;
                $this->begin($temp);
            }
        }

        //自动结束的
        $end_list_sql = "select l.id as live_id from nlsg_live as l
join nlsg_live_info as li on l.id = li.live_pid and li.task_id > 0 and li.is_begin = 1 and li.is_finish = 0
where l.status = 4
and l.steam_end_time >= '$date_begin'
and l.steam_end_time <= '$date_end'";
        $end_list = DB::select($end_list_sql);
        if (!empty($end_list)) {
            foreach ($end_list as $v) {
                $temp = [];
                $temp['live_id'] = $v->live_id;
                $temp['job_type'] = 3;
                $this->begin($temp);
            }
        }

        //虚拟人数
//        $online_num_sql = "update nlsg_live as l
//join nlsg_live_info as li on l.id = li.live_pid and li.task_id > 0
//set l.virtual_online_num = case when (timestampdiff(MINUTE,li.begin_at,li.end_at)*100) > 3000  then 3000 else (timestampdiff(MINUTE,li.begin_at,li.end_at)*100) > 3000 end
//where li.is_begin = 1 and l.virtual_online_num < 3000";

        $online_num_sql = "UPDATE nlsg_live AS l
JOIN nlsg_live_info AS li ON l.id = li.live_pid
AND li.task_id > 0
SET l.virtual_online_num =
CASE
		WHEN ( timestampdiff( MINUTE, li.begin_at,current_timestamp )* 100 ) > need_virtual_num THEN
		need_virtual_num ELSE ( timestampdiff( MINUTE, li.begin_at,current_timestamp )* 100 )
	END
	WHERE
		li.is_begin = 1
	AND l.need_virtual = 1
	AND l.virtual_online_num < l.need_virtual_num;";
        DB::select($online_num_sql);
    }

}
