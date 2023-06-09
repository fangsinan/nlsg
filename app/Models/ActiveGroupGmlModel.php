<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ActiveGroupGmlModel extends Base
{

    protected $table = 'nlsg_active_group_module_list';

    //获取活动板块的商品列表
    public function goods_list($id)
    {
        return DB::table('nlsg_active_group_goods_list')
            ->where('mid', '=', $id)
            ->select(['goods_id', 'goods_type'])
            ->get();
    }

    public function goodsList()
    {
        return $this->hasMany('App\Models\ActiveGroupGglModel', 'mid', 'id')
            ->select(['id','aid','mid','goods_type','goods_id']);
    }

}
