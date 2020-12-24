<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Description of MallAddress
 *
 * @author wangxh
 */
class MallAddress extends Base
{

    protected $table = 'nlsg_mall_address';

    public function create($params, $user_id)
    {
        $province = $params['province'] ?? 0;
        $city = $params['city'] ?? 0;
        $area = $params['area'] ?? 0;
        $details = $params['details'] ?? '';
        $name = $params['name'] ?? '';
        $phone = $params['phone'] ?? '';

        if (empty($province) || empty($city) ||
            empty($details) || empty($name) ||
            empty($phone)) {
            return ['code' => false, 'msg' => '参数错误'];
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
            if (!empty($params['id']??0)){
                $update_res = self::where('user_id', '=', $user_id)
                    ->where('id','<>',$params['id'])
                    ->where('is_default', '=', 1)
                    ->update(['is_default' => 0]);
            }else{
                $update_res = self::where('user_id', '=', $user_id)
                    ->where('is_default', '=', 1)
                    ->update(['is_default' => 0]);
            }

            if ($update_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败'];
            }
        }

        $address->name = $name;
        $address->phone = $phone;
        $address->details = $details;
        $address->is_default = $params['is_default'] ?? 0;
        $address->is_del = 0;
        $address->province = $province;
        $address->city = $city;
        $address->area = $area;
        $address->user_id = $user_id;
        
        $res = $address->save();

        if ($res) {
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        } else {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public static function getNameById($id)
    {
        $res = Area::find($id);
        return $res->name ?? '';
    }

    public function getList($user_id, $id = 0)
    {

        $query = self::where('user_id', '=', $user_id);

        if ($id) {
            $query->where('id', '=', $id);
        }

        $query->where('is_del', '=', 0)
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc')
            ->select(['id', 'name', 'phone', 'details',
                'is_default', 'province', 'city', 'area']);

        $res = $query->get();

        foreach ($res as $v) {
            $v->province_name = self::getNameById($v->province);
            $v->city_name = self::getNameById($v->city);
            $v->area_name = self::getNameById($v->area);
        }

        return $res;
    }

    public function statusChange($id, $flag, $user_id)
    {

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
