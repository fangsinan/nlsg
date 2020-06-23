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
    //$type   1：专栏  2：课程 3 :商品
    static function CollectionData($user_id=0,$target_id=0,$type=0, $info_id=0){

        //处理专栏的关注信息
        if( !in_array($type,[1,2,3]) ){
            return 0;
        }
        $where = ['type' => $type, 'user_id' => $user_id,'relation_id'=>$target_id, 'info_id'=>$info_id];
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
