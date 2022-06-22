<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\CampPrize;
use App\Models\Collection;
use App\Models\Column;
use App\Models\ColumnEndShow;
use App\Models\ColumnWeekReward;
use App\Models\ContentLike;
use App\Models\History;
use App\Models\OfflineProducts;
use App\Models\Poster;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\WorksInfo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
     /**
     *  {get} api/v5/collection/list  我的--收藏列表
     * @apiVersion 5.0.0
     * @apiGroup user
     *
     * @apiParam {string} user_id 用户id
     * @apiParam {string} type  默认1  110->1 专栏  120->2课程  130->7讲座讲座  140->8训练营  150->3商品  160->4集合(161-4 大咖讲书)
     */
    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->error(1000,$validator->getMessageBag()->first(),(object)[]);
        }
        $user_id = $this->user['id'] ?? 0;
        $input_type = $request->input('type', 1);
        $version = $request->input('version', 0);
        $os_type = $request->input('os_type', 0);
        
        if ($user_id == 0) {
            return $this->success();
        }

        // 110->1 专栏  120->2课程  130->7讲座讲座  140->8训练营  150->3商品  160->4集合(161-4 大咖讲书)
        $types = FuncType($input_type);

        $infoObj = new WorksInfo();
        // 根据版本号 单独处理训练营收藏
        if ( (!empty($version) && version_compare($version, "5.0.7") > 0)
            && $input_type == 8 ){
                $where = [
                    'user_id' => $user_id,
                    'type' => $types['col_type'],
                ];

                // 根据收藏时间排序
                // $collection_maxid = Collection::select("fid",DB::raw('max(id) as id'))->where($where)->where("fid",">","0")->groupBy("fid")->paginate($this->page_per_page)->toArray();
                $collection_maxid = Collection::select("id","relation_id","fid")->where($where)->orderBy('id','desc')->paginate(50)->toArray();
                // $collection_ids = array_column($collection_maxid['data'], 'id');

                // // 获取最新的fid的收藏数据   根据最新的收藏数据查询期数
                // $col = Collection::select("*")->whereIn("id",$collection_ids)->orderBy('id','desc')->get()->toArray();
                $res = [];
                $parent_column_ids = [];
                // foreach($collection_maxid as $val){
                foreach($collection_maxid['data'] as $val){


                    // 初始化数据格式
                    $res_one = [
                        "parent_column" => [],
                        "column"        => [],
                        "info"          => [],
                    ];

                    // 校验父类是否收藏
                    $filed = ["id","name as title","subtitle","cover_pic","subscribe_num"];
                    if($val['fid'] == 0){
                        $fid = $val['relation_id'];
                    }else{
                        $fid = $val['fid'];
                    }
                    if( in_array( $fid,$parent_column_ids) ){
                        continue;
                    }
                    $col_fid = Collection::where(["relation_id"=>$fid,"user_id"=>$user_id,"type"=>8,"info_id"=>0,"fid"=>0])->value("id");
                    $parent_column = [];
                    if(!empty($col_fid)){
                        $parent_column = Column::select($filed)->where("type",4)->find($fid) ?? [];
                    }
                    $parent_column_ids[] = $fid;


                    // 获取该父类下信息
                    $parent_collection = Collection::select("*")->where($where)->where("fid",$fid)->get()->toArray();
                    $info_ids = array_column($parent_collection,'info_id');
                    $column_ids = array_column($parent_collection,'relation_id');
                    $column_id = $column_ids[0]??0;
                    // 处理期数id
                    $is_column  = Column::select($filed)->where("type",3)->find($column_id) ?? [];


                    $is_coll_column = Collection::where(["relation_id"=>$column_id,"user_id"=>$user_id,"type"=>8,"info_id"=>0])->value("id");
                    if(!empty($is_coll_column)){
                        $column = $is_column;
                    }else{
                        $column=[];
                    }
                    $is_sub = Subscribe::isSubscribe($user_id,$val['relation_id'],7);

                    // 处理章节
                    $list = $infoObj->getInfoFromID($column_id,$info_ids,$is_sub,$user_id,140,$os_type,$version);
                    if(!empty($list)){
                        $res_one['info']['id']    = empty($is_column) ?0:$is_column['id'];
                        $res_one['info']['title'] = empty($is_column) ?"":$is_column['title'];
                        $res_one['info']['list']  = $list;
                    }else{
                        $res_one['info'] = (object)[];
                    }
                    $res_one['parent_column']   = empty($parent_column) ?(object)[]:$parent_column;
                    $res_one['column'] = (object)[];
                    if(!empty($column) && $column['info_id'] == 0){
                        $res_one['column'] = $column;
                    }

                    $res[] = $res_one;

                }
                return $this->success($res);
        }

        

        //  1专栏  2课程  3商品  4书单 5百科 6听书 7讲座  8训练营
        $collection = Collection::where([
            'user_id' => $user_id,
            'type' => $types['col_type'],
        ])->paginate($this->page_per_page)->toArray();
        $relation_id = array_column($collection['data'], 'relation_id');

        if (empty($relation_id)) {
            return $this->success();
        }
        $list = Collection::getCollection($types['col_type'], $relation_id, $user_id);
        if ($list == false) {
            $list = [];
        }

        foreach ($collection['data'] as &$value) {
            foreach ($list as &$list_value) {
                if ($value['relation_id'] == $list_value['id']) {
                    $list_value['collection_time'] = $value['created_at'];
                    $list_value['info_id'] = $value['info_id'];

                }
            }
        }
        return $this->success($list);
    }

}
