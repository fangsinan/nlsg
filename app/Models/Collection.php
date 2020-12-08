<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Collection extends Base
{
    protected $table = 'nlsg_collection';
    protected $fillable = ['type','user_id','relation_id','info_id','type'];

    //收藏 操作
    //$type   1：专栏  2：课程 3 :商品
    static function CollectionData($user_id=0,$target_id=0,$type=0, $info_id=0){

        //处理专栏的关注信息
        if( !in_array($type,[1,2,3,4,5,6,7]) ){
            return 0;
        }
        $where = ['type' => $type, 'user_id' => $user_id,'relation_id'=>$target_id, 'info_id'=>$info_id];
        $data = Collection::where($where)->first();
        if($data){
            //直接物理删除
            if($type == 1 || $type ==7){
                Column::where(['id' => $target_id])->decrement('collection_num');
            }else if($type == 2 || $type ==6){
                Works::where(['id' => $target_id])->decrement('collection_num');
            }else if($type == 4){
                Lists::where(['id' => $target_id])->decrement('collection_num');
            }else if($type == 5){
                Wiki::where(['id' => $target_id])->decrement('collection_num');
            }
            return Collection::destroy($data['id']);
        }else{
            //创建
            //1专栏  2课程  3商品  4书单 5百科 6听书 7讲座
            if($type == 1 || $type ==7){
                Column::where(['id' => $target_id])->increment('collection_num');
            }else if($type == 2 || $type ==6){
                Works::where(['id' => $target_id])->increment('collection_num');
            }else if($type == 4){
                Lists::where(['id' => $target_id])->increment('collection_num');
            }else if($type == 5){
                Wiki::where(['id' => $target_id])->increment('collection_num');
            }


            return Collection::create($where);
        }
    }

    static function getCollection($type,$ids,$user_id){
        switch ($type) {
            case 1:   //专栏
                $model = new Column();
                $result = $model->getIndexColumn($ids);
                break;
            case 2: //课程
                $model = new Works();
                $result = $model->getIndexWorks($ids,'',$user_id);
                break;
            case 3:
                $model = new MallGoods();
                $result  = $model->getIndexGoods($ids);
                break;
            case 4:   //书单
                $model = new Lists();
                $result = $model->getIndexListWorks($ids, 3);
                break;
            case 5:  //百科
                $model  = new Wiki();
                $result = $model->getIndexWiki($ids);
                break;
            case 6: //听书
                //听书
                $model = new Works();
                $result = $model->getIndexWorks($ids, 1,$user_id);
                break;

        }
        return $result;
    }


}
