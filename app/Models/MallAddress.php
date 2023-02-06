<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Description of MallAddress
 *
 * @author wangxh
 */
class MallAddress extends Base
{

    protected $table = 'nlsg_mall_address';


    public function area_province() {
        return $this->belongsTo(Area::class, 'province', 'id');
    }

    public function area_city() {
        return $this->belongsTo(Area::class, 'city', 'id');
    }

    public function area_area() {
        return $this->belongsTo(Area::class, 'area', 'id');
    }


    public function create($params, $user_id) {
        $province = $params['province'] ?? 0;
        $city     = $params['city'] ?? 0;
        $area     = $params['area'] ?? 0;
        $details  = $params['details'] ?? '';
        $name     = $params['name'] ?? '';
        $phone    = $params['phone'] ?? '';


        if (substr($city, -2) !== '00' && empty($area)) {
            $area = $city;
            $city = $province;
        }


        if (empty($province) || empty($city) ||
            empty($details) || empty($name) ||
            empty($phone)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if (
            substr($province, 0, 2) !== substr($city, 0, 2)
            ||
            substr($province, 0, 2) !== substr($area, 0, 2)
        ) {
            return ['code' => false, 'msg' => '地址格式错误,请重新选择'];
        }

        //判断字符串内是否有'省'
        if (strpos(mb_substr($details, 0, 5), '省') !== false) {
            return ['code' => false, 'msg' => '详细地址请勿包含省市区'];
        }

        if (strlen($phone) > 11) {
            return ['code' => false, 'msg' => '手机号格式错误'];
        }

        if (!empty(($params['id'] ?? 0))) {
            $address = self::where('user_id', '=', $user_id)
                ->find($params['id']);
            if (!$address) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $address = new self();
        }

        DB::beginTransaction();

        if (($params['is_default'] ?? 0) == 1) {
            if (!empty($params['id'] ?? 0)) {
                $update_res = self::where('user_id', '=', $user_id)
                    ->where('id', '<>', $params['id'])
                    ->where('is_default', '=', 1)
                    ->update(['is_default' => 0]);
            } else {
                $update_res = self::where('user_id', '=', $user_id)
                    ->where('is_default', '=', 1)
                    ->update(['is_default' => 0]);
            }

            if ($update_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败'];
            }
        }

        $address->name       = $name;
        $address->phone      = $phone;
        $address->details    = $details;
        $address->is_default = $params['is_default'] ?? 0;
        $address->is_del     = 0;
        $address->province   = $province;
        $address->city       = $city;
        $address->area       = $area;
        $address->user_id    = $user_id;
        $res                 = $address->save();

        if ($res) {
            DB::commit();

            //查询是否有erp推送订单没有更正地址
            $order_id_list = Order::query()
                ->where('user_id', '=', $user_id)
                ->where('type', '=', 14)
                ->where('status', '=', 1)
                ->where('textbook_id', '<>', 0)
                ->where('address_id', '=', 0)
                ->where('created_at','>','2022-04-01 00:00:00')
                ->pluck('id')
                ->toArray();

            if (!empty($order_id_list)) {
//                $erp_order_list = [];
                foreach ($order_id_list as $order_id) {
//                    $erp_order_list[] = [
//                        'order_id' => $order_id,
//                        'flag'     => 1,
//                    ];
                    OrderErpList::query()
                        ->firstOrCreate([
                            'order_id' => $order_id,
                            'flag'=>1
                        ]);
                }
//                OrderErpList::query()->insert($erp_order_list);

                Order::query()
                    ->whereIn('id', $order_id_list)
                    ->update(['address_id' => $address->id]);
            }


            //如果订单没有返回物流单号表示还未发货,如果修改需要重新推送
            $order_id_list = Order::query()
                ->where('user_id', '=', $user_id)
                ->where('type', '=', 14)
                ->where('status', '=', 1)
                ->where('textbook_id', '<>', 0)
                ->where('express_info_id', '=', 0)
                ->where('address_id', '=', $address->id)
                ->pluck('id')
                ->toArray();
            if (!empty($order_id_list)){
                foreach ($order_id_list as $order_id) {
                    OrderErpList::query()
                        ->firstOrCreate([
                            'order_id' => $order_id,
                            'flag'=>1
                        ]);
                }
            }


            return ['code' => true, 'msg' => '成功', 'address_id' => $address->id];
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public static function getNameById($id) {
        $res = Area::find($id);
        return $res->name ?? '';
    }

    public function getList($user_id, $id = 0, $limit = 0) {

        $query = self::where('user_id', '=', $user_id);

        if ($id) {
            $query->where('id', '=', $id);
        }

        if ($limit) {
            $query->limit($limit);
        }

        $query->where('is_del', '=', 0)
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc')
            ->select(['id', 'name', 'phone', 'details',
                'is_default', 'province', 'city', 'area']);

        $res = $query->get();

        foreach ($res as $v) {
            $v->province_name = self::getNameById($v->province);
            $v->city_name     = self::getNameById($v->city);
            $v->area_name     = self::getNameById($v->area);
        }

        return $res;
    }

    public function statusChange($id, $flag, $user_id) {

        if (!is_array($id)) {
            $id = explode(',', $id);
        }

        $temp = self::where('user_id', '=', $user_id)
            ->whereIn('id', $id)
            ->where('is_del', '=', 0)
            ->count();

        if (count($id) !== $temp) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        DB::beginTransaction();

        switch ($flag) {
            case 'default':
                $update_res = self::where('user_id', '=', $user_id)
                    ->where('is_default', '=', 1)
                    ->update(['is_default' => 0]);
                if ($update_res === false) {
                    DB::rollBack();
                    return ['code' => false, 'msg' => '失败'];
                }
                $update_data = [
                    'is_default' => 1
                ];
                break;
            case 'nomal':
                $update_data = [
                    'is_default' => 0
                ];
                break;
            case 'del':
                $update_data = [
                    'is_del' => 1
                ];
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }

        $res = self::where('user_id', '=', $user_id)
            ->whereIn('id', $id)
            ->where('is_del', '=', 0)
            ->update($update_data);
        if ($res) {
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }
    }

}
