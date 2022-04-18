<?php

namespace App\Servers\V5;

use App\Models\LiveGetExcelLog;
use Illuminate\Support\Facades\DB;

class TempLiveExcelServers
{

    public function insertLog($params){
        LiveGetExcelLog::query()->create($params);
    }

    public function shouTingQingKuang($params,$admin_id) {
        $params['mothed'] = 1;
        $params['admin_id'] = $admin_id;

        $begin_time = $params['begin_time'] ?? '';
        $end_time   = $params['end_time'] ?? '';
        $live_id    = $params['live_id'] ?? 0;
        $is_watch   = $params['is_watch'] ?? 0;

        if (empty($begin_time) || empty($end_time) || empty($live_id)) {
            return [
                'code' => false,
                'msg'  => '参数错误',
            ];
        }

        $sql = "SELECT
	o.id order_id,
	CONCAT( '`', o.ordernum ) ordernum,
	CONCAT( '`', pay.transaction_id ) transaction_id,
	o.live_id,
	o.twitter_id,
	o.pay_price,
	o.pay_time ,
	u.phone,
	u.nickname,
	u.unionid,
	u.created_at u_created_at,
	qw.`name` as qw_name,
	wn.qw_name as wn_qw_name,
	FROM_UNIXTIME( qw.follow_user_createtime, '%Y%m%d %H:%i:%s' ) as add_at,
	qw.follow_user_userid
FROM
	nlsg_order AS o
	LEFT JOIN nlsg_user u ON u.id = o.user_id
	LEFT JOIN nlsg_user_wechat AS qw ON qw.unionid = u.unionid
	AND qw.unionid <> ''
	LEFT JOIN nlsg_user_wechat_name wn ON wn.follow_user_userid = qw.follow_user_userid
	LEFT JOIN nlsg_pay_record pay ON pay.ordernum = o.ordernum
	LEFT JOIN (
	SELECT
		id,
		live_id,
		user_id,
		live_son_flag,
		online_time
	FROM
		nlsg_live_online_user
	WHERE
	    live_id = $live_id AND
		( online_time > '$begin_time' AND online_time < '$end_time' )
	GROUP BY
		user_id
	) AS jrzb ON jrzb.user_id = o.user_id
WHERE
	o.live_id = $live_id
	AND o.type = 10
	AND o.`status` = 1
	AND o.is_shill = 0
	AND o.pay_price > 0.01
	AND u.is_test_pay = 0
	AND qw.follow_user_userid IS NOT NULL ";

        if ($is_watch) {
            $sql .= " AND jrzb.online_time IS NOT NULL ";
        } else {
            $sql .= " AND jrzb.online_time IS NULL ";
        }

        $sql .= "GROUP BY o.id ORDER BY o.id DESC;";

        $this->insertLog($params);

        return DB::select($sql);

    }

    public function weiJinZhiBo($params,$admin_id) {
        $params['mothed'] = 2;
        $params['admin_id'] = $admin_id;

        $begin_time_d1 = $params['begin_time_d1'] ?? '';
        $end_time_d1   = $params['end_time_d1'] ?? '';

        $begin_time_d2 = $params['begin_time_d2'] ?? '';
        $end_time_d2   = $params['end_time_d2'] ?? '';

        $is_watch = $params['is_watch'] ?? 0;
        $live_id  = $params['live_id'] ?? 0;

        if (empty($live_id)) {
            return [
                'code' => false,
                'msg'  => '参数错误,请选择直播场次',
            ];
        }

        if (empty($begin_time_d1) && empty($begin_time_d2)) {
            return [
                'code' => false,
                'msg'  => '参数错误,请选择开始时间',
            ];
        }

        if ((!empty($begin_time_d1) && empty($end_time_d1)) || (!empty($begin_time_d2) && empty($end_time_d2))) {
            return [
                'code' => false,
                'msg'  => '参数错误,请选择结束时间',
            ];
        }


        if (!empty($begin_time_d1) && !empty($begin_time_d2)) {
            $time_str = "(
                (online_time > '$begin_time_d1' AND online_time < '$end_time_d1')
                OR
                (online_time > '$begin_time_d2' AND online_time < '$end_time_d2')
            )";

        } else {
            $time_str = '(online_time > \'' .
                ($begin_time_d1 ?: $begin_time_d2) . '\' AND online_time < \'' .
                ($end_time_d1 ?: $end_time_d2) . '\')';
        }

        $sql = "
    SELECT
        o.id as order_id,
        CONCAT( '`', o.ordernum ) ordernum,
        CONCAT( '`', pay.transaction_id ) transaction_id,
        o.live_id,
        o.twitter_id,
        o.pay_price,
        o.pay_time,
        u.phone,
        u.nickname,
        u.unionid,
        u.created_at,
        qw.name as qname,
        wn.qw_name,
        FROM_UNIXTIME( qw.follow_user_createtime, '%Y%m%d %H:%i:%s' ) follow_user_createtime,
        qw.follow_user_userid
    FROM
        nlsg_order AS o
        LEFT JOIN nlsg_user u ON u.id = o.user_id
        LEFT JOIN nlsg_user_wechat AS qw ON qw.unionid = u.unionid
        AND qw.unionid <> ''
        LEFT JOIN nlsg_user_wechat_name wn ON wn.follow_user_userid = qw.follow_user_userid
        LEFT JOIN nlsg_pay_record pay ON pay.ordernum = o.ordernum
        LEFT JOIN (
        SELECT
            id,
            live_id,
            user_id,
            live_son_flag,
            online_time
        FROM
            nlsg_live_online_user
        WHERE
            live_id = $live_id
            AND $time_str
        GROUP BY
            user_id
        ) AS jrzb ON jrzb.user_id = o.user_id
    WHERE
        o.live_id = $live_id
        AND o.type = 10
        AND o.`status` = 1
        AND o.is_shill = 0
        AND o.pay_price > 0.01
        AND u.is_test_pay = 0 ";


        if ($is_watch) {
            $sql .= ' AND jrzb.online_time is not NULL ';
        } else {
            $sql .= ' AND jrzb.online_time IS NULL ';
        }

        $sql .= 'GROUP BY o.id  ORDER BY o.id DESC';
        $this->insertLog($params);
        return DB::select($sql);
    }

    public function weiJinZhiBoFree($params,$admin_id){
        $params['mothed'] = 3;
        $params['admin_id'] = $admin_id;

        //直播间id   推荐人手机号  收听时间范围 是否观看
        if ($params['is_watch'] === 0){
            $exists_str = ' NOT EXISTS ';
        }else{
            $exists_str = ' EXISTS ';
        }

        $sql = 'SELECT
u.phone,u.nickname,s.created_at
from nlsg_subscribe as s
join nlsg_user as u on s.user_id = u.id
where
s.id BETWEEN '.$params['begin_id'].' and '.$params['end_id'].' and
s.relation_id = '.$params['live_id'].' and s.type = 3 and s.`status` = 1
AND u.is_test_pay = 0 and s.twitter_id =  '.$params['twitter_id'].'
and '.$exists_str.' (
SELECT id from nlsg_live_online_user as lou where
lou.live_id =  '.$params['live_id'].'  and lou.user_id = s.user_id
and lou.online_time  BETWEEN \' '.$params['begin_time'].' \' AND \' '.$params['end_time'].' \'
)';
        $this->insertLog($params);
        return DB::select($sql);

    }

    public function qiYeWeiXin($params,$admin_id) {

        $params['mothed'] = 4;
        $params['admin_id'] = $admin_id;

        $begin_time = $params['begin_time'] ?? '';
        $end_time   = $params['end_time'] ?? '';
        $live_id    = (int)($params['live_id'] ?? 0);
        $is_bind    = $params['is_bind'] ?? 0;

        if (empty($begin_time) || empty($end_time)) {
            return [
                'code' => false,
                'msg'  => '参数错误,请选择开始时间',
            ];
        }


        $sql = "SELECT
o.id order_id,
CONCAT( '`', o.ordernum ) ordernum,
CONCAT( '`', pay.transaction_id ) transaction_id,
o.live_id,
o.twitter_id,
o.pay_price,
o.pay_time ,
u.phone,
u.nickname ,
u.unionid,
u.created_at,
qw.name as qname,
wn.qw_name,
FROM_UNIXTIME( qw.follow_user_createtime, '%Y-%m-%d %H:%i:%s' ) follow_user_createtime,
qw.follow_user_userid
FROM
	nlsg_order AS o
	LEFT JOIN nlsg_user u ON u.id = o.user_id
	LEFT JOIN nlsg_user_wechat AS qw ON qw.unionid = u.unionid
	AND qw.unionid <> ''
	LEFT JOIN nlsg_user_wechat_name wn ON wn.follow_user_userid = qw.follow_user_userid
	LEFT JOIN nlsg_pay_record pay ON pay.ordernum = o.ordernum
WHERE
	o.pay_time > '$begin_time'  AND o.pay_time < '$end_time' ";

        if (!empty($live_id)) {
            $sql .= " AND o.live_id = $live_id ";
        }

        $sql .= " AND o.type = 10 AND o.`status` = 1 AND o.is_shill = 0 AND o.pay_price > 0.01
	AND u.is_test_pay = 0
	AND u.phone NOT IN ( 13311111111 ) ";

        if ($is_bind) {
            $sql .= " AND (qw.follow_user_userid is not null and
            qw.follow_user_userid not in ('JiaZhengZe','DongRuiXia','SunYiHao','XuHongRu',
            'ZhangJing','LiuDanHua','ShenShuJing','ZhangShiHao','ZhangQi01')) ";
        } else {
            $sql .= ' AND qw.follow_user_userid IS NULL ';
        }


        $sql .= " GROUP BY o.id ORDER BY o.id DESC";
        $this->insertLog($params);
        return DB::select($sql);

    }

}
