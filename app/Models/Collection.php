<?php

namespace App\Models;

class Collection extends Base
{
    protected $table = 'nlsg_collection';
    protected $fillable = ['type', 'user_id', 'relation_id', 'info_id', 'type','fid'];


    static public function isCollection($type=[],$relation_id,$info_id,$user_id)
    {
        //  收藏按总id走
        $is_collection= 0;
        
        $collectionObj = Collection::select("id")->where([
            'user_id' => $user_id,
            'info_id' => $info_id,
            'relation_id' => $relation_id,
        ]);
        $collection = $collectionObj->whereIn('type',$type)->get()->toArray();
        if(!empty($collection)){
            $is_collection = 1;
        }
        return $is_collection;
        

    }
    //收藏 操作
    //$type   1：专栏  2：课程 3 :商品
    static function CollectionData($user_id = 0, $target_id = 0, $type = 0, $info_id = 0)
    {
        //处理专栏的关注信息
        if (!in_array($type, [1, 2, 3, 4, 5, 6, 7, 8])) {
            return 0;
        }
        $where = ['type' => $type, 'user_id' => $user_id, 'relation_id' => $target_id,'info_id'=>$info_id];
        $data = Collection::where($where)->first();
        //用户商品收藏缓存清理

        if ($data) {
            //直接物理删除
            if ($type == 1 || $type == 7 || $type == 8) {
                Column::where(['id' => $target_id])->decrement('collection_num');
            } else if ($type == 2 || $type == 6) {
                Works::where(['id' => $target_id])->decrement('collection_num');
            } else if ($type == 4) {
                Lists::where(['id' => $target_id])->decrement('collection_num');
            } else if ($type == 5) {
                Wiki::where(['id' => $target_id])->decrement('collection_num');
            }
            return Collection::destroy($data['id']);
        } else {
            //创建
            //1专栏  2课程  3商品  4书单 5百科 6听书 7讲座
            if ($type == 1 || $type == 7 || $type == 8) {
                Column::where(['id' => $target_id])->increment('collection_num');
            } else if ($type == 2 || $type == 6) {
                Works::where(['id' => $target_id])->increment('collection_num');
            } else if ($type == 4) {
                Lists::where(['id' => $target_id])->increment('collection_num');
            } else if ($type == 5) {
                Wiki::where(['id' => $target_id])->increment('collection_num');
            }

            if($type == 8){ // 训练营添加父类id
                $fid=Column::select("classify_column_id")->where(['id' => $target_id])->first();
                $where['fid'] = $fid->classify_column_id??0;
            }

            $where['info_id'] = $info_id;
            return Collection::create($where);
        }
    }

    static function getCollection($type, $ids, $user_id)
    {
        switch ($type) {
            case 1:   //专栏
            case 8:   //专栏
                $model = new Column();
                $result = $model->getIndexColumn($ids);
                break;
            case 2: //课程
                $model = new Works();
                $result = $model->getIndexWorks($ids, '', $user_id);
                break;
            case 3:
                $model = new MallGoods();
                $result = $model->getIndexGoods($ids);
                break;
            case 4:   //书单
                $model = new Lists();
                $result = $model->getIndexListWorks($ids, [3]);
                break;
            case 5:  //百科
                $model = new Wiki();
                $result = $model->getIndexWiki($ids);
                break;
            case 6: //听书
                //听书
                $model = new Works();
                $result = $model->getIndexWorks($ids, 1, $user_id);
                break;
            case 7:   //讲座
                $model = new Column();
                $result = $model->getIndexColumn($ids);
                break;

        }
        return $result;
    }

    public static function getGoodsColByUid($user_id = 0)
    {
//        $cache_key_name = 'user_goods_col_' . $user_id;
//        $expire_num = CacheTools::getExpire('goods_col');
//        $expire_num = 600;
//        $res = Cache::get($cache_key_name);

//        if (empty($res)) {
        if (empty($user_id)) {
            $res = [];
        } else {
            $res = Collection::where('user_id', '=', $user_id)
                ->where('type', '=', 3)
                ->select(['relation_id as goods_id'])
                ->get();
            if ($res->isEmpty()) {
                $res = [];
            } else {
                $res = $res->toArray();
                $res = array_column($res, 'goods_id');
            }
        }
//            Cache::put($cache_key_name, $res, $expire_num);
//        }

        return $res;
    }
}
