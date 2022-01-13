<?php


namespace App\Models;


class ShortVideoModel extends Base
{
    protected $table = 'nlsg_short_video';

    protected $fillable = [
        'title', 'introduce', 'status', 'share_img', 'cover_img', 'detail_img', 'user_id', 'rank', 'online_time', 'view_num', 'real_view_num', 'video_id',
        'comment_num', 'size', 'duration', 'url', 'like_num', 'share_num', 'attribute_url',
        'video_adopt','callback_url','callback_attribute','video_type',
    ];
    //获取短视频
    function getVideo ($uid,$id=0,$not_id=0,$page=1,$size=3,$is_home=0){

        $re_data = [];
        $flag_data_len = 0;
        $not_ids = [$not_id];
        $i=1;  //循环次数

        $get_flag = true;
        while ($get_flag){

            if($flag_data_len >= $size){
                $get_flag = false;
            }else{
                //视频数量不够
                $getVideo = self::getVideoInfo($id,$not_ids,$size-$flag_data_len,$is_home);
                $flag_data = $getVideo['list'];
                $id = 0;  //二次循环重置id
                if($i ==1 && empty($flag_data)){//首次循环如果为空直接返回
                    break;
                }
                //补充相应的随机视频
                //重新计算not list——id
                $not_ids = array_merge(array_column($flag_data,'id'),$not_ids);

                if(!empty($flag_data)){
                    //合并list
                    $re_data = array_merge($re_data,$flag_data);
                }

                //重新计算 count
                $flag_data_len = count($re_data);

                if(empty($flag_data)){
                    //如果not条件查询数据为空   则重置 $not_ids  则会出现重复视频
                    //计算当前数据库总数
                    //防止出现相邻的重复视频
                    if($getVideo['count'] < $size){
                        //如果总数小于请求数
                        $not_ids = [];
                    }else{
                        $not_ids = [$not_id,$re_data[$flag_data_len-1]['id']];
                    }

                }

            }

            $i++;
        }


        $recomObj = new ShortVideoRecommendModel();

        foreach ($re_data as &$re_value){
            $re_value['w_len'] = 0;
            $re_value['h_len'] = 0;
            if( !empty($re_value['attribute_url'])){
                $size = explode('#',$re_value['attribute_url']);
                $re_value['w_len'] = $size[0];
                $re_value['h_len'] = $size[1];
            }

            //是否点赞
            $isLike = ShortVideoLikeModel::where(['relation_id' => $re_value['id'], 'type' => 1, 'user_id' => $uid, 'status'=>1])->first();
            $re_value['is_like'] = $isLike ? 1 : 0;



            $re_value['user_info'] = User::getTeacherInfo($re_value['user_id']);

            $follow = UserFollow::where(['from_uid'=>$uid,'to_uid'=>$re_value['user_id']])->first();
            $re_value['user_info']['is_follow'] = $follow ? 1 :0;

            //推荐
            $re_value["recomment"] = $recomObj->getRecommend($re_value['id']);

        }





        return $re_data;
    }



    static  function getVideoInfo($id=0,$not_id=[],$size=3,$is_home){

        if(!is_array($not_id) || $size<=0){
            return ['list'=>[],'count'=>0];
        }
        //按照rand、创建时间排序
        $field = ["id","user_id","share_img","cover_img","detail_img","title","introduce","view_num","like_num","comment_num","share_num","duration","url","attribute_url"];

        $where['status'] = 2;
        if(!empty($id)){
            $where["id"] = $id;
        }
        //首页展示横屏
        if($is_home == 1){
            $where["video_type"] = 1;
        }

        $data = self::select($field)->where($where)
            ->whereNotIn('id',$not_id)
//            ->orderBy('rank','desc')->orderBy("like_num","desc")->orderBy("created_at","desc")//->first();
            ->inRandomOrder()->limit($size)->get()->toArray();
//        dd($data);
        //计算总数 count
        $count = self::select($field)->where(['status'=>2])->count();
        return ['list'=>$data,'count'=>$count];
    }



    /*
     * 短视频上传转换
     * */
    public static function  toVideo()
    {

        $ids = ShortVideoModel::where('video_adopt', 1)
            ->where('video_id','!=', '')
            ->pluck('video_id')
            ->toArray();

        $map = WorksInfo::editVideo($ids);

        if($map['msg'] == "OK"){
            foreach ($map['data'] as $key=>$val){

                if (!empty($val['callback_url1'])) {
                    $val['callback_url'] =  $val['callback_url1'];
                    $val['callback_attribute'] =  $val['attribute_url1'];
                    unset($val['callback_url1']);
                    unset($val['attribute_url1']);
                }
                if (!empty($val['callback_url2'])) {
                    $val['callback_url'] =  $val['callback_url2'];
                    $val['callback_attribute'] =  $val['attribute_url2'];
                    unset($val['callback_url2']);
                    unset($val['attribute_url2']);
                }
                if (!empty($val['callback_url3'])) {
                    $val['callback_url'] =  $val['callback_url3'];
                    $val['callback_attribute'] =  $val['attribute_url1'];
                    unset($val['callback_url3']);
                    unset($val['attribute_url1']);
                }

                $val['video_type'] = 1;
                if( !empty($val['callback_attribute']) ){
                    $attr = explode("#",$val['callback_attribute']);
                    if($attr[0] < $attr[1]){
                        $val['video_type'] = 2;
                    }
                }
                $video_id = $val['video_id'];
                ShortVideoModel::where('video_id', $video_id)->update($val);
            }
        }
        echo $map['msg'];
    }







}
