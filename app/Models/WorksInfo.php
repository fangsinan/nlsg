<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;
use Libraries\ImClient;

class WorksInfo extends Base
{
    protected $table = 'nlsg_works_info';

    protected $fillable = [
         'pid', 'column_id','type', 'title','rank','view_num', 'section', 'introduce', 'url', 'status','video_id','free_trial','timing_online','duration', 'online_time', 'timing_time','share_img','like_num','old_share_img'
    ];


    // $type  1 单课程  2 多课程  3讲座 4训练营
    public function getInfo($works_id, $is_sub = 0, $user_id = 0, $type = 1, $order = 'asc', $page_per_page = 50, $page = 0, $size = 0,$column_data = [],$os_type=1,$version='5.0.0')
    {

        $is_free = $column_data['is_free']??0;
        $where = ['status' => 4];
        if ($type == 1) {
            $where['pid'] = $works_id;
        } else if ($type == 2) {
            $where['outline_id'] = $works_id;
        } else if ($type == 3) {
            $where['column_id'] = $works_id;
            $where['type'] = 1; //纯视频类型
        }else if ($type == 4) { //训练营
            $where['column_id'] = $works_id;
            $where['type'] = 1; //纯视频类型
        }
        $query = WorksInfo::select([
            'id','pid', 'type', 'title', 'section', 'introduce', 'url', 'callback_url1', 'callback_url1', 'callback_url2',
            'callback_url3', 'view_num', 'duration', 'free_trial','rank','share_img','like_num','old_share_img'
        ])->where($where)->orderBy('rank',$order)->orderBy('id', $order);
        //->paginate($page_per_page)->toArray();

        if ($page) {
            $works_data = $query->limit($size)->offset(($page - 1) * $size)->get()->toArray();
        } else {
            $works_data = $query->limit($page_per_page)->get()->toArray();
        }

        //$works_data = $works_data_size['data'];
        foreach ($works_data as $key => $val) {
            //训练营H5  不返回视频地址
            if($type !== 4 && $os_type !== 3){
                //处理url  关注或试听
                $works_data[$key]['href_url'] = '';
                if ($is_sub == 1 || $val['free_trial'] == 1 || $is_free == 1) {
                    $works_data[$key]['href_url'] = self::GetWorksUrl($val);
                }
            }

            unset($works_data[$key]['url']);
            unset($works_data[$key]['callback_url3']);
            unset($works_data[$key]['callback_url2']);
            unset($works_data[$key]['callback_url1']);


            //类型是训练营 根据版本号 获取新旧训练营图 5.4.3
            if ($type == 4 && version_compare($version, '5.0.4', '<')) {
                $works_data[$key]['share_img'] = $val['old_share_img'];
            }
            unset($works_data[$key]['old_share_img']);
            $works_data[$key]['time_leng'] = (string)0;
            $works_data[$key]['time_number'] = (string)0;
            $works_data[$key]['time_is_end'] = 0;

            if ($user_id) {

                //训练营共用章节id
                if(empty($column_data['id'])){
                    $relation_id = $works_id;
                }else{
                    $relation_id = $column_data['id'];
                }
                //单章节 学习记录 百分比
                $his_data = History::select('time_leng', 'time_number','is_end')->where([
                    //'relation_type' => 2,
                    'relation_id' => $relation_id,
                    'info_id' => $val['id'],
                    'user_id' => $user_id,
                    // 'is_del' => 0,
                ])->orderBy('updated_at', 'desc')->first();
                if ($his_data) {
                    $works_data[$key]['time_leng'] = $his_data->time_leng;
                    $works_data[$key]['time_number'] = $his_data->time_number;
                    $works_data[$key]['time_is_end'] = $his_data->is_end;
                }

                // 章节是否点赞
                if($type ==4){
                    $works_data[$key]['info_is_like'] = ContentLike::isLike([5],$relation_id,$user_id,$val['id']);
                }
            }
        }

        return $works_data;
    }

    static function GetWorksUrl($WorkArr)
    {
        if (!empty($WorkArr['callback_url3'])) {

            return self::UrlKey($WorkArr['callback_url3'],$WorkArr['duration']);
        }
        if (!empty($WorkArr['callback_url2'])) {
            // return $WorkArr['callback_url2'];
            return self::UrlKey($WorkArr['callback_url2'],$WorkArr['duration']);
        }
        if (!empty($WorkArr['callback_url1'])) {
            // return $WorkArr['callback_url1'];
            return self::UrlKey($WorkArr['callback_url1'],$WorkArr['duration']);
        }
        return self::UrlKey($WorkArr['url'],$WorkArr['duration']);
        // return $WorkArr['url'];
    }

    public function three2one($works, $is_show_url)
    {

//        switch ($works) {
//            case (!empty($works['callback_url3'])):
//                $works['href_url'] = $works['callback_url3'];
//                break;
//            case (!empty($works['callback_url2'])):
//                $works['href_url'] = $works['callback_url2'];
//                break;
//            case (!empty($works['callback_url1'])):
//                $works['href_url'] = $works['callback_url1'];
//                break;
//            default:
//                $works['href_url'] = $works['url'];
//        }
        $works['href_url'] = $works['url'];
        unset($works['callback_url1'], $works['callback_url2'], $works['callback_url3'], $works['url']);
        if ($is_show_url == false && $works['free_trial'] == 0) {
            $works['href_url'] = '';
        }
        return $works;
    }

    public function works()
    {
        return $this->belongsTo(Works::class, 'pid', 'id');
    }


    public function infoHistory()
    {
        return $this->hasOne(History::class, 'info_id', 'works_info_id')
            ->select(['id', 'relation_type', 'relation_id', 'info_id', 'user_id', 'time_leng', 'time_number','is_end']);
    }

    //用于获取章节上下曲信息
    public function neighbor($params, $user)
    {
        $now_date = date('Y-m-d H:i:s');
        $works_id = $params['works_id'] ?? 0;
        $works_info_id = $params['works_info_id'] ?? 0;
        $ob = $params['ob'] ?? '';
        //1 专栏  2作品 3直播  4会员 5线下产品  6讲座   7训练营
        $type = $params['type'] ?? 0;


        if($ob == ''){  //默认
            $ob = 'asc';
            if($type == 2 && $works_id == 566){
                $ob = 'desc';
            }
        }

        //历史记录类型
        switch($type){
            case 1: $his_type = 1;    break;
            case 2: $his_type = 4;    break;
            case 6: $his_type = 2;    break;
            case 7: $his_type = 5;    break;
            default :$his_type=0;   break;
        }


        if($type == 1 || $type == 6 || $type == 7){

            $column_id = $params['column_id'] ?? 0;
            if (empty($column_id)) {
                return ['code' => false, 'msg' => 'id不存在'];
            }
            $columnDatta = Column::find($column_id);
//            $column_data = Works::select('id')->where(['column_id'=>$column_id])->first();
//            $works_id = $column_data['id'];
            //仅限于训练营  因为多期训练营共用同一章节
            if( $columnDatta['info_column_id'] > 0){
                $get_info_id = $columnDatta['info_column_id'];
            }else{
                $get_info_id = $column_id;
            }
            $getBanner_id = $column_id;

        }else{
            if (empty($works_id) || empty($works_info_id)) {
                return ['code' => false, 'msg' => '课程id不存在'];
            }
            $sub_relation_id = $works_id;
            $getBanner_id = $works_id;
        }


        if($type == 6 || $type == 7){
            $query = self::where(['column_id' => $get_info_id,'type'=>1,'status'=>4])
                ->select(['id as works_info_id', 'pid as works_id', 'title', 'duration', 'free_trial', 'url',
                    'introduce', 'section','size','type', 'view_num', 'callback_url1', 'callback_url2', 'callback_url3', 'share_img','like_num','old_share_img','book_video_url']);
            $works_id = $column_id;  // 讲座直接关联info表
        }else{
            $query = self::where(['pid'=>$works_id,'status'=>4])
                ->select(['id as works_info_id', 'pid as works_id', 'title', 'duration', 'free_trial', 'url',
                    'introduce', 'section', 'size','type', 'view_num', 'callback_url1', 'callback_url2', 'callback_url3','like_num','share_img','old_share_img','book_video_url']);
        }




        $query->with(['infoHistory' => function ($query) use ($works_id, $user, $his_type) {
            $query->where('user_id', '=', $user['id'])->where('relation_id', '=', $works_id)->where('relation_type','=',$his_type);
        }]);

        if ($ob == 'desc') {
            //$query->orderBy('id', 'desc');
            $query->orderBy('rank','desc')
                    ->orderBy('id', 'desc');
        } else {
            $query->orderBy('rank','asc')
                    ->orderBy('id', 'asc');
        }

//        if($type == 7 && $columnDatta['show_info_num'] > 0 ){
        if( $type == 7 ){
            $query->limit($columnDatta['show_info_num']);
        }

        $info_list = $query->get();

        if ($info_list->isEmpty()) {
            return ['code' => false, 'msg' => '课程不存在'];
        }
        $info_list = $info_list->toArray();

        $info_key = -1;
        foreach ($info_list as $k => $v) {
            if (empty($v['info_history'])) {
                $info_list[$k]['info_history'] = new class {
                };
            }
            if ($v['works_info_id'] == $works_info_id) {
                $info_key = $k;
            }
            $info_list[$k]['url'] = self::GetWorksUrl($v);

        }
        if ($info_key == -1) {
            return ['code' => false, 'msg' => '章节不存在'];
        }
        $info_key = $info_key + count($info_list);

        $info_list = array_merge($info_list, $info_list, $info_list);



        if($type == 1 || $type == 6 || $type == 7){

            $works_info = DB::table('nlsg_column as w')
                ->leftJoin('nlsg_subscribe as s', function ($join) use ($user,$now_date,$type) {
                    $join->on('s.relation_id', '=', 'w.id')
                        ->whereRaw('s.user_id = ' . $user['id'])
                        ->where('s.type', '=', $type)
                        ->where('s.start_time','<',$now_date)
                        ->where('s.end_time','>',$now_date)
                        ->where('s.status', '=', 1)
                        ->where('s.is_del', '=', 0);
                })
                ->where('w.id', '=', $column_id)
                ->select(['title','subtitle', 'subscribe_num', 'w.user_id', 'w.id', 'w.price', 'w.original_price' ,  'w.is_free', 'w.status','w.cover_pic as cover_img','w.comment_num','w.collection_num',
                    DB::raw('if(s.id > 0,1,0) as is_sub')])
                ->first();
            $sub_type = 6;
            if($type == 7){
                $sub_type = 7;
            }

        }else{

            $works_info = DB::table('nlsg_works as w')
            ->leftJoin('nlsg_subscribe as s', function ($join) use ($user,$now_date) {
                $join->on('s.relation_id', '=', 'w.id')
                    ->whereRaw('s.user_id = ' . $user['id'])
                    ->where('s.type', '=', 2)
                    ->where('s.start_time','<',$now_date)
                    ->where('s.end_time','>',$now_date)
                    ->where('s.status', '=', 1)
                    ->where('s.is_del', '=', 0);
            })
            ->where('w.id', '=', $works_id)
            ->select(['title','subtitle', 'subscribe_num', 'w.user_id', 'w.id','w.content', 'w.price', 'w.original_price', 'w.is_pay', 'w.type', 'w.is_free', 'w.status','w.cover_img','w.comment_num','w.collection_num','w.is_audio_book',
                DB::raw('if(s.id > 0,1,0) as is_sub')])
            ->first();

            $sub_type = 2;
        }
//        if($user['level'] > 2){
//            $works_info->is_sub = 1;
//        }
        //是否大咖讲书
        $works_info->is_teacherBook = self::IsTeacherBook($works_id);
        // if($works_info->is_teacherBook == 1){  // 大咖讲书是组合售卖
        //     $works_info->id = 40;
        //     $sub_type = 8;
        // }
        $works_info->is_sub = Subscribe::isSubscribe($user['id'],$works_info->id,$sub_type);

        //是否关注作者
        $follow = UserFollow::where(['from_uid'=>$user['id'],'to_uid'=>$works_info->user_id])->first();
        $works_info->is_follow = $follow ? 1 :0;
        //作者头像
        $works_user = User::select('id','headimg','nickname')->find($works_info->user_id);
        $works_info->teacher_headimg = $works_user['headimg'];
        $works_info->teacher_nickname = $works_user['nickname'];

        $is_show_url = true;
        if ($works_info->is_free == 0 && $works_info->is_sub == 0) {
            $is_show_url = false;
        }
        // $works_info->is_collection = 0;
        // $collection_type = $type;
        // if($type == 6){
        //     $collection_type = 7; //type 与收藏表类型有出入
        // }



        //  收藏按总id走
        // $collectionObj = Collection::select()->where([
        //     'user_id' => $user['id'],
        //     //'info_id' => $works_info_id,
        //     'relation_id' => $works_id,
        // ]);
        // if($type == 1 || $type == 6 || $type == 7){
        //     $collection = $collectionObj->whereIn('type',[1,7,8])->get();
        // }else if($type == 2){
        //     $collection = $collectionObj->whereIn('type',[2,6])->get();
        // }else{
        //     $collection = [];
        // }
        // if($collection){
        //     $works_info->is_collection = 1;
        // }
        // 1 专栏  2作品  6讲座  7训练营
        $collection_type = [0];
        $like_type = [0];
        if($type == 1 || $type == 6 || $type == 7){
            $collection_type = [1,7,8];
            $like_type = [1,4,5];
        }else if($type == 2){
            $collection_type = [2,6];
            $like_type = [2];
        }

        $info_list[$info_key]['is_collection'] = Collection::isCollection($collection_type,$works_id,$works_info_id,$user['id']);
        $works_info->is_collection = $info_list[$info_key]['is_collection']; //兼容V4
        $info_list[$info_key]['is_like'] =ContentLike::isLike($like_type,$works_id,$user['id'],$works_info_id);
        $info_list[$info_key]['column_banner'] = Column::getCampBanner($getBanner_id,$user,$params);

        //  统计章节的评论数   由于训练营是共用章节 所以需要单独统计评论表和回复表
        $CommentIds = Comment::where(['type'=>6,'relation_id'=>$works_id,'info_id'=>$works_info_id,'status'=>1])->pluck("id")->toArray() ?? [];
        $replay_num = CommentReply::whereIn('comment_id',$CommentIds)->count();
        $info_list[$info_key]['comment_num'] = (int)(count($CommentIds) + $replay_num);

        //根据版本号 获取新旧训练营图 5.4.3
        if ( version_compare($params['version']??'0', '5.0.4', '<')) {
            $info_list[$info_key]['share_img'] = $info_list[$info_key]['old_share_img'];
        }

        $list['previous'] = $this->three2one($info_list[$info_key - 1], $is_show_url);
        $list['current'] = $this->three2one($info_list[$info_key], $is_show_url);
        $list['next'] = $this->three2one($info_list[$info_key + 1], $is_show_url);

        $user_info = [
            'uid' => $user['id'],
            'level' => $user['level'],
            'expire_time' => $user['expire_time'],
            'vip' => $user['new_vip'],
        ];

        return [
            'list' => $list,
            'user_info' => $user_info,
            'works' => $works_info,
        ];

    }

    /**
     * 转换音视频
     */
    public static function  covertVideo()
    {
        // $SecretId  = "AKIDrcCpIdlpgLo4A4LMj7MPFtKfolWeNHnC";
        // $SECRET_KEY= "MWXLwKVXMzPcrwrcDcrulPsAF7nIpCNM";
        $SecretId=config('env.TENCENT_SECRETID');
        $SECRET_KEY=config('env.TENCENT_SECRETKEY');
        $ids = WorksInfo::select('id','title','type','video_id','url','callback_url1','callback_url2','callback_url3')
                ->where('video_adopt', 0)
                ->where('video_id','!=', '')
                ->pluck('video_id')
                ->toArray();
        if ($ids){
            foreach ($ids as $v) {
                $video_id = $v;
                //加密
                $rand   = rand (100, 10000000); //9031868223070871051
                $time   = time ();
                $Region = "gz";
                $Region = "ap-guangzhou";
                $data_key = [
                    'Action' => 'GetVideoInfo',
                    'fileId' => $video_id,
                    'infoFilter.0' => 'transcodeInfo',
                    'Region' => $Region,
                    'SecretId' => $SecretId,
                    'Timestamp' => $time,
                    'Nonce' => $rand,
                    'SignatureMethod' => 'HmacSHA256',
                ];
                ksort ($data_key); //排序
               // 计算签名
               $srcStr    = "POSTvod.api.qcloud.com/v2/index.php?" . http_build_query ($data_key);
               $signature = base64_encode (hash_hmac ('sha256', $srcStr, $SECRET_KEY, true)); //SHA1  sha256
               $data_key['Signature'] = $signature;
               ksort ($data_key); //排序

               //拉取转码成功信息
               $url = "https://vod.api.qcloud.com/v2/index.php"; //?Action=PullEvent&COMMON_PARAMS
               $info = self::curlPost ($url, $data_key);  //post
               if ( !empty($info) ) {
                   $info = json_decode ($info, true);
                   if ( isset($info['code']) && isset($info['codeDesc']) && $info['code'] == 0 && $info['codeDesc'] == 'Success' ) {
                       $map = [];
                       //获取所有视频参数
                       foreach($info['transcodeInfo']['transcodeList'] as $k=>$v){
                           //音频
                           if(isset($info['transcodeInfo']['transcodeList'][0]['container']) &&
                               stristr($info['transcodeInfo']['transcodeList'][0]['url'],".mp3")){
                               $type=2;
                           }else{
                               if (stristr ($v['url'], ".f10.mp4") ) {
                                   $map['callback_url1'] = $v['url'];
                                   $map['attribute_url1'] = $v['width']."#".$v['height'];
                               } elseif (stristr ($v['url'], ".f20.mp4") ) {
                                   $map['callback_url2'] = $v['url'];
                                   $map['attribute_url2'] = $v['width']."#".$v['height'];
                               } elseif (stristr ($v['url'], ".f30.mp4") ) {
                                   $map['callback_url3'] = $v['url'];
                                   $map['attribute_url3'] = $v['width']."#".$v['height'];
                               }else{
                                   $map['attribute_url'] = $v['width']."#".$v['height']; //原视频
//                                   $map['url'] = $v['url']; //原视频
                               }
                               $type=1;
                           }

                       }
                       if ( (!empty($map) && (!empty($map['callback_url1']) || !empty($map['callback_url2']) || !empty($map['callback_url3']))) || $type==2) {
                           $map['video_adopt'] = 1;

                           $seconds = $v['duration'];
                           $second = $seconds % 60;
                           $minit = floor($seconds / 60);

                           $m_num=mb_strlen($minit, 'utf-8');
                           $s_num=mb_strlen($second, 'utf-8');
                           if($m_num==1){
                               $minit='0'.$minit;
                           }
                           if($s_num==1){
                               $second='0'.$second;
                           }

                           $map['duration'] = $minit . ':' . $second;

                           gmstrftime("%H:%M:%S",$time); //转换视频时间

                           $map['size']=round($v['size']/(1024*1024), 2);  //大小

                           //处理防止腾讯传回参数不符合 检查转换链接是否包含视频id
                           if((isset($map['callback_url3']) && stristr ($map['callback_url3'], "$video_id")) || $type==2) {
                               WorksInfo::where('video_id', $video_id)->update($map);
                               echo 'OK';
                           }
                       }

                   } else {
                       echo 'fail';
                   }

               } else {
                   echo 'fail';
               }
            }
        }
    }




    /**
     * 获取音视频详情
     * $ids   视频的video集合
     */
    public static function  editVideo($ids=[])
    {
        //新账号
        $SecretId=config('env.TENCENT_SECRETID');
        $SECRET_KEY=config('env.TENCENT_SECRETKEY');
        $res = [];
        $msg = 'fail';

        if ($ids){
            foreach ($ids as $v) {
                $video_id = $v;
                //加密
                $rand   = rand (100, 10000000); //9031868223070871051
                $time   = time ();
                $Region = "gz";
                $Region = "ap-guangzhou";
                $data_key = [
                    'Action' => 'GetVideoInfo',
                    'fileId' => $video_id,
                    'infoFilter.0' => 'transcodeInfo',
                    'infoFilter.1' => 'basicInfo',
                    'Region' => $Region,
                    'SecretId' => $SecretId,
                    'Timestamp' => $time,
                    'Nonce' => $rand,
                    'SignatureMethod' => 'HmacSHA256',
                ];

                ksort ($data_key); //排序
                // 计算签名
                $srcStr    = "POSTvod.api.qcloud.com/v2/index.php?" . http_build_query ($data_key);
                $signature = base64_encode (hash_hmac ('sha256', $srcStr, $SECRET_KEY, true)); //SHA1  sha256
                $data_key['Signature'] = $signature;
                ksort ($data_key); //排序

                //拉取转码成功信息
                $url = "https://vod.api.qcloud.com/v2/index.php"; //?Action=PullEvent&COMMON_PARAMS
                $info = self::curlPost ($url, $data_key);  //post

                if ( !empty($info) ) {
                    $info = json_decode ($info, true);

                    if ( isset($info['code']) && isset($info['codeDesc']) && $info['code'] == 0 && $info['codeDesc'] == 'Success' ) {

                        //获取所有视频参数
                        foreach($info['transcodeInfo']['transcodeList'] as $k=>$v){
                            //音频
                            if(isset($info['transcodeInfo']['transcodeList'][0]['container']) &&
                                stristr($info['transcodeInfo']['transcodeList'][0]['url'],".mp3")){
                                $type=2;
                            }else{
                                //definition 模板id 会总变化
                                if (stristr ($v['url'], ".f10.mp4")  || $v['definition'] == '100030') {
                                    $map['callback_url1'] = $v['url'];
                                    $map['attribute_url1'] = $v['width']."#".$v['height'];
                                } elseif (stristr ($v['url'], ".f20.mp4") ) {
                                    $map['callback_url2'] = $v['url'];
                                    $map['attribute_url2'] = $v['width']."#".$v['height'];
                                } elseif (stristr ($v['url'], ".f30.mp4") ) {
                                    $map['callback_url3'] = $v['url'];
                                    $map['attribute_url3'] = $v['width']."#".$v['height'];
                                }else{
                                    if( $v['definition'] == '0'){ //原视频
                                        $map['attribute_url'] = $v['width']."#".$v['height']; //原视频
                                    }
//                                   $map['url'] = $v['url']; //原视频
                                }
                                $type=1;
                            }

                        }

                        if ( (!empty($map) && (!empty($map['callback_url1']) || !empty($map['callback_url2']) || !empty($map['callback_url3']))) || $type==2) {
                            $map['video_adopt'] = 1;

                            $seconds = $v['duration'];
                            $second = $seconds % 60;
                            $minit = floor($seconds / 60);

                            $m_num=mb_strlen($minit, 'utf-8');
                            $s_num=mb_strlen($second, 'utf-8');
                            if($m_num==1){
                                $minit='0'.$minit;
                            }
                            if($s_num==1){
                                $second='0'.$second;
                            }

                            $map['duration'] = $minit . ':' . $second;

                            gmstrftime("%H:%M:%S",$time); //转换视频时间

                            $map['size']=round($v['size']/(1024*1024), 2);  //大小
                            $map['video_id'] = $video_id;
                            $map['cover_img'] = $info['basicInfo']["coverUrl"];   //封面
                            $msg =  'OK';
                            //处理防止腾讯传回参数不符合 检查转换链接是否包含视频id
//                            if((isset($map['callback_url3']) && stristr ($map['callback_url3'], "$video_id")) || $type==2) {
////                                WorksInfo::where('video_id', $video_id)->update($map);
//                                $msg =  'OK';
//                            }
                        }

                    } else {
                        $msg =  'fail';
                    }

                } else {
                    $msg =  'fail';
                }
                $res[] = $map;
            }
        }


        return ['msg'=>$msg,'data'=>$res];
    }


    /**
     * @curl抓取页面数据
     * @param $url 访问地址
     * @param null $isPost 是否post
     * @param null $data post数据
     * @return array
     */
    public static function curlPost($url, $data = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //显示获取的头部信息必须为true否则无法看到cookie
        //curl_setopt($curl, CURLOPT_HEADER, true);
//        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);// 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);// 使用自动跳转
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);// 发送一个常规的Post请求
            if (is_array($data)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));// Post提交的数据包
            } else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);// Post提交的数据包 可以是json数据
            }
        }
        curl_setopt($curl, CURLOPT_COOKIESESSION, true); // 读取上面所储存的Cookie信息
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        //curl_setopt($curl, CURLOPT_TIMEOUT, 30);// 设置超时限制防止死循环
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        $tmpInfo = curl_exec($curl);
        curl_close($curl);
        if (empty($tmpInfo)) {
            return false;
        }
        return $tmpInfo;
    }

    public function worksInfoHistory(){
        return $this->hasMany(History::class,'info_id','id');
    }

    // 当前课程是否属于大咖讲书
    public static function IsTeacherBook($works_id=0) {
        if($works_id == 0){
            return 0;
        }
        $id = Lists::select('id')->where(['type'=>10])->first();
        $is_data = ListsWork::select('id')->where([
                'type'      => 1,
                'lists_id'  => $id['id']??0,
                'works_id'  => $works_id,
                'state'     => 1,
            ])->first();
        return  empty($is_data) ?0 :1;
    }



        // 根据id 获取章节数 $type  1 单课程  2 多课程  3讲座 140训练营
        public function getInfoFromID($column_id,$infoIds, $is_sub = 0, $user_id = 0, $input_type = 1, $os_type=1,$version='5.0.0')
        {
            if(empty($infoIds)){
                return [];
            }
            $is_free = 0;
            $where = ['status' => 4];
            $works_data = WorksInfo::select([
                'id','pid','column_id', 'type', 'title', 'section', 'introduce', 'url', 'callback_url1', 'callback_url1', 'callback_url2',
                'callback_url3', 'view_num', 'duration', 'free_trial','rank','share_img','like_num','old_share_img'
            ])->where($where)->whereIn("id",$infoIds)
            ->orderByRaw('FIELD(id,'.implode(',', $infoIds).')')->get()->toArray();

            foreach ($works_data as $key => $val) {
                //训练营H5  不返回视频地址
                if($input_type == 140 && $os_type == 3){
                    unset($works_data[$key]['callback_url3']);
                    unset($works_data[$key]['callback_url2']);
                    unset($works_data[$key]['callback_url1']);
                }else{
                    //处理url  关注或试听
                    $works_data[$key]['href_url'] = '';
                    if ($is_sub == 1 || $val['free_trial'] == 1 || $is_free == 1) {
                        $works_data[$key]['href_url'] = self::GetWorksUrl($val);
                    } else {
                        unset($works_data[$key]['callback_url3']);
                        unset($works_data[$key]['callback_url2']);
                        unset($works_data[$key]['callback_url1']);
                    }
                }
                unset($works_data[$key]['url']);

                //类型是训练营 根据版本号 获取新旧训练营图 5.4.3
                if ($input_type == 140 && version_compare($version, '5.0.4', '<')) {
                    $works_data[$key]['share_img'] = $val['old_share_img'];
                }
                unset($works_data[$key]['old_share_img']);
                $works_data[$key]['time_leng'] = (string)0;
                $works_data[$key]['time_number'] = (string)0;
                $works_data[$key]['time_is_end'] = 0;

                if ($user_id) {
                    //训练营共用章节id
                    if( in_array($input_type ,[130,140])){
                        $relation_id = $val['column_id'];
                    }else{
                        $relation_id = $val['pid'];
                    }
                    $fun_types = FuncType($input_type);


                    //单章节 学习记录 百分比
                    // relation_type = 1专栏   2讲座   3听书  4精品课程   5训练营
                    $his_data = History::select('time_leng', 'time_number','is_end')->where([
                        'relation_type' => $fun_types["his_type"],
                        'relation_id' => $column_id,
                        'info_id' => $val['id'],
                        'user_id' => $user_id,
                    ])->orderBy('updated_at', 'desc')->first();

                    if ($his_data) {
                        $works_data[$key]['time_leng'] = $his_data->time_leng;
                        $works_data[$key]['time_number'] = $his_data->time_number;
                        $works_data[$key]['time_is_end'] = $his_data->is_end;
                    }
                    // 章节是否点赞
                    if($input_type == 140){
                        $works_data[$key]['info_is_like'] = ContentLike::isLike([5],$relation_id,$user_id,$val['id']);
                    }
                }
            }

            return $works_data;
        }






    /**
     * 转换音视频
     */
    public static function  covertVideo1()
    {
        $SecretId=config('env.TENCENT_SECRETID');
        $SECRET_KEY=config('env.TENCENT_SECRETKEY');
        $ids = DB::table("nlsg_short_video")->select("*")
            ->where('video_adopt', 0)
            ->where('video_id','!=', '')
            ->limit(100)
            ->pluck('video_id')
            ->toArray();


        if ($ids){
            foreach ($ids as $v) {

                $video_id = $v;
                //加密
                $rand   = rand (100, 10000000); //9031868223070871051
                $time   = time ();
                $Region = "gz";
                $Region = "ap-guangzhou";
                $data_key = [
                    'Action' => 'GetVideoInfo',
                    'fileId' => $video_id,
                    'infoFilter.0' => 'basicInfo',
                    'infoFilter.1' => 'transcodeInfo',
                    'Region' => $Region,
                    'SecretId' => $SecretId,
                    'Timestamp' => $time,
                    'Nonce' => $rand,
                    'SignatureMethod' => 'HmacSHA256',
                ];
                ksort ($data_key); //排序
                // 计算签名
                $srcStr    = "POSTvod.api.qcloud.com/v2/index.php?" . http_build_query ($data_key);
                $signature = base64_encode (hash_hmac ('sha256', $srcStr, $SECRET_KEY, true)); //SHA1  sha256
                $data_key['Signature'] = $signature;
                ksort ($data_key); //排序

                //拉取转码成功信息
                $url = "https://vod.api.qcloud.com/v2/index.php"; //?Action=PullEvent&COMMON_PARAMS
                $info = self::curlPost ($url, $data_key);  //post
                if ( !empty($info) ) {
                    $info = json_decode ($info, true);

                    if ( isset($info['code']) && isset($info['codeDesc']) && $info['code'] == 0 && $info['codeDesc'] == 'Success' ) {
                        $map = [];
                        //获取所有视频参数

                        foreach($info['transcodeInfo']['transcodeList'] as $k=>$v){
                            //音频
                            if(isset($info['transcodeInfo']['transcodeList'][0]['container']) &&
                                stristr($info['transcodeInfo']['transcodeList'][0]['url'],".mp3")){
                                $type=2;
                            }else{
                                if (stristr ($v['url'], ".f10.mp4") ) {
                                    $map['callback_url1'] = $v['url'];
                                    $map['attribute_url1'] = $v['width']."#".$v['height'];
                                } elseif (stristr ($v['url'], ".f20.mp4") ) {
                                    $map['callback_url2'] = $v['url'];
                                    $map['attribute_url2'] = $v['width']."#".$v['height'];
                                } elseif (stristr ($v['url'], ".f30.mp4") ) {
                                    $map['callback_url3'] = $v['url'];
                                    $map['attribute_url3'] = $v['width']."#".$v['height'];
                                }else{
                                    $map['attribute_url'] = $v['width']."#".$v['height']; //原视频
                                    //                                   $map['url'] = $v['url']; //原视频
                                }
                                $type=1;
                            }

                        }
                        $map['url'] = $info['basicInfo']['sourceVideoUrl'];
                        $map['cover_img'] = $info['basicInfo']['coverUrl'];
                        if ( (!empty($map) && ( !empty($map['url']) || !empty($map['callback_url1']) || !empty($map['callback_url2']) || !empty($map['callback_url3']))) || $type==2) {
                            $map['video_adopt'] = 1;


                            $seconds = $v['duration'];
                            $second = $seconds % 60;
                            $minit = floor($seconds / 60);

                            $m_num=mb_strlen($minit, 'utf-8');
                            $s_num=mb_strlen($second, 'utf-8');
                            if($m_num==1){
                                $minit='0'.$minit;
                            }
                            if($s_num==1){
                                $second='0'.$second;
                            }

                            $map['duration'] = $minit . ':' . $second;

                            gmstrftime("%H:%M:%S",$time); //转换视频时间

                            $map['size']=round($info['basicInfo']['size']/(1024*1024), 2);  //大小

                            DB::table("nlsg_short_video")->select("*")
                                ->where('video_id','=', $video_id)
                                ->update($map);
                            echo 'OK';
                        }

                    } else {
                        echo 'fail1';
                    }

                } else {
                    echo 'fail2';
                }
            }
        }
    }

    /**
     * 短视频
     */
    public static function  short_video()
    {
        $uri = 'vod.tencentcloudapi.com';
        $SecretId=config('env.TENCENT_SECRETID');
        $SECRET_KEY=config('env.TENCENT_SECRETKEY');

        $videos = DB::table("nlsg_short_video")->select("*")
            ->where('task_id','=', '')
            ->limit(100)->get()->toArray();

        foreach($videos as $v){
            //加密
            $rand   = rand (100, 10000000); //9031868223070871051
            $time   = time ();
            $Region = "ap-guangzhou";
            $data_key = [
                'Nonce' => $rand,
                'Timestamp' => $time,
                'SecretId' => $SecretId,
                'Version' => '2018-07-17',
                'Language' => 'zh-CN',
                'Region' => '',

                'Action' => 'PullUpload',
                'MediaUrl' => $v->callback_url,
                'MediaName' => $v->title,
                'CoverUrl' => $v->cover_img,
                'ClassId' => 935935,
            ];
            ksort ($data_key); //排序
            $signStr    = "GETvod.tencentcloudapi.com/?";// . http_build_query ($data_key);
            foreach ( $data_key as $key => $value ) {
                $signStr = $signStr . $key . "=" . $value . "&";
            }
            $signStr = substr($signStr, 0, -1);

            $signature = base64_encode(hash_hmac("sha1", $signStr, $SECRET_KEY, true));
            $data_key['Signature'] = $signature;

            //拉取转码成功信息
            $url = "https://vod.tencentcloudapi.com/?".http_build_query ($data_key);
            $info = ImClient::curlGet ($url);  //post
            $info = json_decode($info, true);

            dump($v->id);
            if ( !empty($info) ) {
                DB::table("nlsg_short_video")->Where("id",$v->id)
                    ->update([ "task_id" => $info['Response']['TaskId'], ]);
            }
        }
    }


    /**
     * UrlKey 防盗链
     *
     * @param string $url
     * @param string $duration
     *
     * @return string
     */
    public static function UrlKey(string $url, string $duration="60:00"): string
    {
        $ex_times = explode(":",$duration);
        $time_v = $ex_times[0]*60 + $ex_times[1]+3600; //强制增加1小时观看失效，app老出现黑屏

        $url = str_replace("http://1308168117.vod2.myqcloud.com/",
            "https://vod.cloud.nlsgapp.com/",$url);
        $key ="z0GECzqW2hU8Y7XVvBIh";
        $Dir = str_replace(basename($url),'',parse_url($url,PHP_URL_PATH));
        $time = time()+$time_v;
        $t =dechex($time);
        $rlimit= 5;
        $us= rand(100000,999999);
        $sign = md5($key . $Dir . $t  . $rlimit . $us );
        $query = http_build_query([
            "t" => $t,
            "rlimit" => $rlimit,
            "us" => $us,
            "sign" => $sign,
        ]);
        return $url.'?'.$query;
    }
}
