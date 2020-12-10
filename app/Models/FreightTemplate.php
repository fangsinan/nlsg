<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of FreightTemplate
 *
 * @author wangxh
 */
class FreightTemplate extends Base
{

    protected $table = 'nlsg_freight_template';

    public function listOfShop($flag)
    {
        $cache_key_name = 'freight_template_list';
        $expire_num = CacheTools::getExpire('freight_template_list');
        $now = date('Y-m-d H:i:s');

        $res = Cache::get($cache_key_name);
        if (empty($res)) {
            $res = self::whereIn('type', [2, 3])
                ->where('status', '=', 1)
                ->select(['id', 'type', 'name',
                    'admin_name', 'admin_phone', 'admin_phone as phone',
                    'province', 'city', 'area', 'details',
                    'start_time', 'end_time'])
                ->get();
            Cache::put($cache_key_name, $res, $expire_num);
        }

        $list = [];

        foreach ($res as $v) {
            if ($v->type == $flag && $v->start_time <= $now && $v->end_time >= $now) {
                $v->province_name = MallAddress::getNameById($v->province);
                $v->city_name = MallAddress::getNameById($v->city);
                $v->area_name = MallAddress::getNameById($v->area);
                unset($v->type, $v->start_time, $v->end_time);
                $list[] = $v;
            }
        }

        return $list;
    }

    public function f_details()
    {
        return $this->hasMany('App\Models\FreightTemplateDetails', 'pid', 'id')
            ->select(['id', 'name', 'pid', 'type', 'start_price', 'count_type',
                'count_start_line', 'skip_num', 'skip_price', 'add_price'])
            ->where('status', '=', 1)
            ->orderBy('type', 'asc');
    }

    public static function getFreightMoney($info, $address_info)
    {
        $freight_id = MallGoods::find($info['goods_id'])->freight_id;

        if ($freight_id) {
            $model = new self();
            $freight_data = $model->getFreightData($freight_id);
            $freight_money = ConfigModel::getData(7);
            $add_price = $freight_data['f_details'][0]['add_price'] ?? 0;

            //计算运费
            foreach ($freight_data['f_details'] as $v) {
                $temp_money = 0;
                if ($v['type'] == 2) {
                    //校验收货地址是否在该分组中
                    $check_in = self::checkAreaIsIn($address_info, $v['d_list']);
                    if ($check_in) {
                        $temp_money = self::computeMoney($info, $v);
                        $add_price = $v['add_price'] ?? $add_price;
                    }
                } else {
                    //计算默认价格
                    $temp_money = self::computeMoney($info, $v);
                }

                if ($freight_money < $temp_money) {
                    $freight_money = $temp_money;
                }
            }
            return ['price' => $freight_money, 'add_price' => $add_price];
            //return $freight_money;
        } else {
            return ['price' => ConfigModel::getData(7), 'add_price' => 0];
            //return ConfigModel::getData(7);
        }
    }

    //计算运费价格
    public static function computeMoney($info, $v)
    {
        //计数类型 1:件数  2:重量  3:体积
        $all_skip_price = 0;
        switch (intval($v['count_type'])) {
            case 1:
                if ($info['num'] > $v['count_start_line']) {
                    $skip_count = $info['num'] - $v['count_start_line'];
                    $skip_times = intval($skip_count / $v['skip_num']);
                    $all_skip_price = GetPriceTools::PriceCalc('*', $skip_times, $v['skip_price']);
                }
                break;
            case 2:
                $temp_weight = GetPriceTools::PriceCalc('*', $info['num'], $info['weight']);
                if ($temp_weight > $v['count_start_line']) {
                    $skip_count = $temp_weight - $v['count_start_line'];
                    $skip_times = intval($skip_count / $v['skip_num']);
                    $all_skip_price = GetPriceTools::PriceCalc('*', $skip_times, $v['skip_price']);
                }
                break;
            case 3:
                $temp_volume = GetPriceTools::PriceCalc('*', $info['num'], $info['volume']);
                if ($temp_volume > $v['count_start_line']) {
                    $skip_count = $temp_volume - $v['count_start_line'];
                    $skip_times = intval($skip_count / $v['skip_num']);
                    $all_skip_price = GetPriceTools::PriceCalc('*', $skip_times, $v['skip_price']);
                }
                break;
        }
        return GetPriceTools::PriceCalc('+', $v['start_price'], $all_skip_price);
    }

    public static function checkAreaIsIn($address, $list)
    {
        $list = array_column($list, 'area_id');
        $area_id = intval(($address['area'] == 0) ? $address['city'] : $address['area']);

        $area_scope_begin = intval($area_id / 10000) * 10000;
        $area_scope_end = $area_scope_begin + 9999;

        foreach ($list as $k => $v) {
            if (!($v >= $area_scope_begin && $v <= $area_scope_end)) {
                unset($list[$k]);
            }
        }

        $in = false;

        foreach ($list as $v) {
            if ($area_id == $v) {
                $in = true;
                break;
            }
        }
        if ($in === false) {
            $area_id = intval($area_id / 100) * 100;
            foreach ($list as $v) {
                if ($area_id == $v) {
                    $in = true;
                    break;
                }
            }
        }
        if ($in === false) {
            foreach ($list as $v) {
                if ($area_scope_begin == $v) {
                    $in = true;
                    break;
                }
            }
        }

        return $in;
    }

    //获取运费模板详细配置
    public function getFreightData($id)
    {
        $cache_key_name = 'freight_template'; //哈希组名
        $cache_name = 'id_' . $id;
        $expire_num = CacheTools::getExpire('freight_template');
        $list = Cache::tags($cache_key_name)->get($cache_name);
        if (empty($list)) {
            $list = $this->getFreightDataFromDb($id);
            Cache::tags($cache_key_name)->put($cache_name, $list, $expire_num);
        }
        return $list;
    }

    public function getFreightDataFromDb($id)
    {
        $res = self::where('type', '=', 1)
            ->with(['f_details', 'f_details.d_list'])
            ->select(['id', 'name'])
            ->find($id);
        if ($res) {
            return $res->toArray();
        } else {
            return [];
        }
    }

}
