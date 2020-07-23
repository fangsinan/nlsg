<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ActiveGroupGlModel extends Base {

    protected $table = 'nlsg_active_group_list';

    protected function getListDataFromDb() {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        $list = DB::table('nlsg_active_group_list')
                ->where('status', '=', 1)
                ->where('end_time', '>', $now_date)
                ->orderBy('rank', 'asc')
                ->orderBy('id', 'asc')
                ->select(['id', 'title', 'begin_time', 'end_time',
                    'ad_begin_time', 'pre_begin_time', 'lace_img',
                    'wx_share_title', 'wx_share_img', 'wx_share_desc'])
                ->get();

        $gmlModel = new ActiveGroupGmlModel();
        foreach ($list as $v) {
            $v->goods_id = [];
            //获取活动板块
            $v->module_list = $this->module_list($v->id);

            foreach ($v->module_list as $vv) {
                //获取活动模块商品
                $vv->goods_list = $gmlModel->goods_list($vv->id);
                $temp_res = [];
                foreach ($vv->goods_list as $glv) {
                    if (!isset($temp_res[$glv->goods_type])) {
                        $temp_res[$glv->goods_type] = [];
                    }
                    $temp_res[$glv->goods_type][] = $glv->goods_id;
                }
                $v->goods_id = $temp_res;
            }
        }
        return $list;
    }

    //获取当前未开始活动数据
    public function getList($params = []) {
        $expire_num = 3600;
        $cache_key_name = 'active_group_list';

        $list = Cache::get($cache_key_name);
        if ($list === '0') {
            return new class{};
        } else {
            if (empty($list)) {
                $list = $this->getListDataFromDb();
                if (empty($list)) {
                    $list = '0';
                }
                Cache::add($cache_key_name, $list, $expire_num);
            }
            if ($list === '0') {
                 return new class{};
            }
            //如果指定id 就直传一个
            if ($params['id'] ?? false) {
                $res = [];
                foreach ($list as $v) {
                    if ($v->id === $params['id']) {
                        if (($params['simple'] ?? 0) == 1) {
                            unset($v['module_list'], $v['goods_id']);
                        }
                        $res = $v;
                    }
                }

                return $res;
            } else {

                //没有指定id  返回全部
                //如果指定商品类型和id  则只返回包含该id的活动
                if (($params['goods_id'] ?? false) && ($params['goods_type'] ?? false)) {
                    foreach ($list as $k => $v) {
                        if (isset($v->goods_id[$params['goods_type']])) {
                            if (!in_array($params['goods_id'], $v->goods_id[$params['goods_type']])) {
                                unset($list[$k]);
                            } else {
                                if (($params['simple'] ?? 0) == 1) {
                                    unset($list[$k]->module_list, $list[$k]->goods_id);
                                }
                            }
                        } else {
                            unset($list[$k]);
                        }
                    }
                }
                return $list;
            }
        }
    }

    //关联活动模块表
    public function module_list($id) {
        $res = DB::table('nlsg_active_group_module_list')
                ->where('aid', '=', $id)
                ->where('status', '=', 1)
                ->orderBy('rank', 'asc')
                ->orderBy('id', 'desc')
                ->select(['id', 'title'])
                ->get();
        return $res;
    }

}
