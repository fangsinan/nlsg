<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class LiveConsole extends Base
{

    protected $table = 'nlsg_live';


    public function checkHelper($params, $user_id)
    {

    }

    public function changeStatus($params, $user_id)
    {

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

        $live_data = $live_info_data = [];

        $live_data['user_id'] = $user_id;
        $live_data['is_show'] = 1;
        if (($params['is_free'] ?? 0) == 0) {
            if (($params['price'] ?? 0) <= 0) {
                return ['code' => false, 'msg' => '金额错误'];
            }
            $live_data['price'] = $params['price'];
            $live_data['appoint_price'] = $params['appoint_price'] ?? $params['price'];
            $live_data['limit_price'] = $params['limit_price'] ?? $params['price'];
        } else {
            $live_data['price'] = $live_data['appoint_price'] = $live_data['limit_price'] = 0;
        }
        $live_data['twitter_money'] = $params['twitter_money'] ?? 0;

        if (empty($params['title'] ?? '')) {
            return ['code' => false, 'msg' => '直播名称错误'];
        } else {
            $check_title = $this->haveEmojiChar($params['title']);
            if ($check_title) {
                return ['code' => false, 'msg' => '直播名称错误'];
            }
        }
        if (empty($params['describe'] ?? '')) {
            return ['code' => false, 'msg' => '直播简介错误'];
        }
        if (empty($params['cover_img'] ?? '')) {
            return ['code' => false, 'msg' => '直播封面错误'];
        }
        if (empty($params['msg'] ?? '')) {
            return ['code' => false, 'msg' => '直播预约公告错误'];
        }
        if (empty($params['content'] ?? '')) {
            return ['code' => false, 'msg' => '直播内容介绍错误'];
        }
        if (empty($params['can_push'] ?? '')) {
            return ['code' => false, 'msg' => '推送权限错误'];
        }
        if (!empty($params['helper'] ?? '')) {
            $live_data['helper'] = preg_replace('/[^0-9]/i', ',', $params['helper']);
        }
        if (!empty($params['password'] ?? '')) {
            $live_data['password'] = bcrypt($params['password']);
        }
        $live_data['title'] = $params['title'];
        $live_data['describe'] = $params['describe'];
        $live_data['cover_img'] = $params['cover_img'];
        $live_data['msg'] = $params['msg'];
        $live_data['content'] = $params['content'];
        $live_data['can_push'] = $params['can_push'];
        $live_data['is_free'] = $params['is_free'];
        $live_begin_at = 0;
        $live_end_at = 0;

        if (empty($params['list'] ?? '') || !is_array($params['list'] ?? '')) {
            return ['code' => false, 'msg' => '直播时间信息错误'];
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
                $temp_push_end_time = date('Y-m-d H:i:s',
                    strtotime($v['end_at'] . " +1 days")
                );

                $params['list'][$k]['push_end_time'] = $temp_push_end_time;

                $temp_get_url = $this->getPushUrl(
                    rand(100, 999) . $user_id . $now, strtotime($temp_push_end_time)
                );
                $params['list'][$k]['push_live_url'] = $temp_get_url['push_url'];
                $params['list'][$k]['live_url'] = $temp_get_url['play_url'];
                $params['list'][$k]['live_url_flv'] = $temp_get_url['play_url_flv'];
            }
        }

        $live_data['begin_at'] = $live_begin_at;
        $live_data['end_at'] = $live_end_at;

        DB::beginTransaction();

        if (isset($params['id'])) {
            $liveModel = self::whereId($params['id']);
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
            $live_res = $liveModel->whereId($params['id'])->update($live_data);
            if ($live_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '添加错误', 'ps' => __LINE__];
            }
            $live_id = $params['id'];

            //删除未开始的直播
            $info_del_res = LiveInfo::where('live_pid', '=', $params['id'])
                ->where('begin_at', '>', $now_date)
                ->where('status', '=', 1)
                ->delete();
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
            $v['user_id'] = $user_id;
            $v['created_at'] = $now_date;
        }

        $info_res = DB::table('nlsg_live_info')->insert($params['list']);
        if($info_res === false){
            DB::rollBack();
            return ['code'=>false,'msg'=>'添加错误','ps'=>__LINE__];
        }

        //todo 写入权限表
        if(!empty($params['helper'])){
            $helper_list = explode(',',$params['helper']);
            dd($helper_list);
        }

        return [$live_data, $params['list'], $live_info_data];

        DB::commit();
        return ['code'=>true,'msg'=>'添加成功'];
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
    //echo getPushUrl("123456","时间戳");
    function getPushUrl($streamName, $time = null)
    {
        $key = env('LIVE_PUSH_URL');
        $push_url = env('LIVE_PLAY_URL');
        $play_url = env('Live_API_KEY');

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

}
