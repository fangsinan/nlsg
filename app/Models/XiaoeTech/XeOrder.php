<?php


namespace App\Models\XiaoeTech;


use App\Models\Base;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class XeOrder extends Base
{
    const DB_TABLE = 'nlsg_xe_order';

    protected $table = 'nlsg_xe_order';

    public function xeUserInfo(): HasOne
    {
        return $this->hasOne(XeUser::class, 'xe_user_id', 'xe_user_id');
    }

    public function userInfo(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function orderGoodsInfo(): HasMany
    {
        return $this->hasMany(XeOrderGoods::class, 'order_id', 'order_id');
    }

    public function distributeInfo(): HasOne
    {
        return $this->hasOne(XeOrderDistribute::class, 'order_id', 'order_id');
    }

    public function payType($k, $f = 0)
    {
        $arr = [
            '-1' => '无(0元订单) ',
            '1'  => '线上微信',
            '2'  => '线上支付宝',
            '3'  => '线下支付',
            '4'  => '百度收银台',
            '8'  => '虚拟币',
        ];

        if ($f) {
            return $arr;
        }

        if (is_numeric($k)) {
            return $arr[$k] ?? '-';
        } else {
            return array_search($k, $arr) ?: '-';
        }
    }

    public function orderType($k, $f = 0)
    {
        $arr = [
            '1' => '交易订单',
            '2' => '导入订单',
            '3' => '渠道采购订单',
            '4' => '兑换订单',
        ];
        if ($f) {
            return $arr;
        }

        if (is_numeric($k)) {
            return $arr[$k] ?? '-';
        } else {
            return array_search($k, $arr) ?: '-';
        }


    }

    public function payState($k, $f = 0)
    {
        $arr = [
            '0' => '未支付',
            '1' => '支付成功',
            '2' => '支付关闭',
        ];
        if ($f) {
            return $arr;
        }
        if (is_numeric($k)) {
            return $arr[$k] ?? '-';
        } else {
            return array_search($k, $arr) ?: '-';
        }
    }

    public function orderState($k, $f = 0)
    {
        $arr = [
            '0' => '待付款',
            '1' => '待成交',
            '2' => '待发货',
            '3' => '已发货',
            '4' => '已完成',
            '5' => '已关闭',
        ];
        if ($f) {
            return $arr;
        }
        if (is_numeric($k)) {
            return $arr[$k] ?? '-';
        } else {
            return array_search($k, $arr) ?: '-';
        }
    }

    public function wx_app_type($k, $f = 0)
    {
        $arr = [
            '0'  => '小程序',
            '1'  => '微信(公众号)',
            '2'  => 'qq',
            '3'  => '支付宝',
            '4'  => '安卓app 5-浏览器/手机号',
            '6'  => 'iOS Android App工具',
            '10' => '开放平台',
            '11' => 'PC',
            '12' => '小鹅通app',
            '13' => '线下订单',
            '14' => '小鹅通助手app',
            '15' => 'APP内嵌SDK',
            '16' => '微博端浏览器',
            '20' => '抖音小程序',
            '21' => 'App内嵌h5',
            '22' => '百度小程序',
            '30' => '管理台导入订单',
            '31' => '积分兑换',
            '32' => '企业微信',
            '33' => '视频号小程序',
            '34' => '鹅直播小程序',
            '35' => '视频号小程序(自定义交易组件)订单',
            '36' => '视频号分销小程序',
            '88' => '抖音小程序订单',
        ];
        if ($f) {
            return $arr;
        }
        if (is_numeric($k)) {
            return $arr[$k] ?? '-';
        } else {
            return array_search($k, $arr) ?: '-';
        }

    }

    public function ke_fu()
    {
        $expire_num     = 120;
        $cache_key_name = 'xet_select_ke_fu';

        $list = Cache::get($cache_key_name);

        if (empty($list)) {
            $list = DB::table('crm_live_waiter_wechat as ww')
                ->join('crm_admin_user as au', 'ww.admin_id', '=', 'au.id')
                ->select(['ww.admin_id', 'au.name'])
                ->groupBy('ww.admin_id')
                ->get();

            if ($list->isEmpty()) {
                $list = [];
            } else {
                $list = $list->toArray();
            }
            Cache::put($cache_key_name, $list, $expire_num);
        }

        return $list;

    }

    public function goods()
    {
        $expire_num     = 120;
        $cache_key_name = 'xet_select_goods';

        $list = Cache::get($cache_key_name);

        if (empty($list)) {
            $list = XeOrderGoods::query()
                ->select(['sku_id', 'goods_name'])
                ->groupBy('sku_id')
                ->get();

            if ($list->isEmpty()) {
                $list = [];
            } else {
                $list = $list->toArray();
            }
            Cache::put($cache_key_name, $list, $expire_num);
        }

        return $list;

    }

}
