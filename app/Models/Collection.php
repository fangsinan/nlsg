<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Collection extends Authenticatable
{
    protected $table = 'nlsg_collection';
    protected $fillable = ['type','user_id','column_id','works_id', 'worksinfo_id','goods_id','sku_number',];

    //收藏
    //$type   1：课程  2：专栏 3 :商品
    static function CollectionData($user_id=0,$target_id=0,$type=0 ){
        $where = ['type' => $type, 'user_id' => $user_id,];
        //处理专栏的关注信息
        if($type == 1){
            $where['column_id'] = $target_id;
        }else if($type == 2){
            $where['works_id'] = $target_id;
        }else if($type == 3){
            $where['goods_id'] = $target_id;
        }else{
            //type 类型错误直接返回0
            return 0;
        }
        $data = Collection::where($where)->first();
        if($data){
            //直接物理删除
            return Collection::destroy($data['id']);
        }else{
            //创建
            return Collection::create($where);
        }
    }
}
