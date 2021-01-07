<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class WorksInfo extends Base
{
    protected $table = 'nlsg_works_info';
    public $timestamps = false;

    protected $fillable = [
         'pid', 'type', 'title','rank', 'section', 'introduce', 'url', 'status','video_id','free_trial','timing_online','duration'
    ];


    public function getDateFormat()
    {
        return time();
    }

    // $type  1 单课程  2 多课程  3讲座
    public function getInfo($works_id, $is_sub = 0, $user_id = 0, $type = 1, $order = 'asc', $page_per_page = 50, $page = 0, $size = 0,$is_free = 0)
    {
        $where = ['status' => 4];
        if ($type == 1) {
            $where['pid'] = $works_id;
        } else if ($type == 2) {
            $where['outline_id'] = $works_id;
        } else if ($type == 3) {
            $where['column_id'] = $works_id;
            $where['type'] = 1; //纯视频类型
        }
        $query = WorksInfo::select([
            'id','pid', 'type', 'title', 'section', 'introduce', 'url', 'callback_url1', 'callback_url1', 'callback_url2',
            'callback_url3', 'view_num', 'duration', 'free_trial'
        ])->where($where)->orderBy('id', $order);
        //->paginate($page_per_page)->toArray();

        if ($page && $size) {
            $works_data = $query->limit($size)->offset(($page - 1) * $size)->get()->toArray();
        } else {
            $works_data = $query->limit($page_per_page)->get()->toArray();
        }

        //$works_data = $works_data_size['data'];
        foreach ($works_data as $key => $val) {
            //处理url  关注或试听
            $works_data[$key]['href_url'] = '';
            if ($is_sub == 1 || $val['free_trial'] == 1 || $is_free == 1) {
                $works_data[$key]['href_url'] = $works_data[$key]['url'];

            } else {
                unset($works_data[$key]['callback_url3']);
                unset($works_data[$key]['callback_url2']);
                unset($works_data[$key]['callback_url1']);
            }
            unset($works_data[$key]['url']);


            $works_data[$key]['time_leng'] = 0;
            $works_data[$key]['time_number'] = 0;
            if ($user_id) {
                //单章节 学习记录 百分比
                $his_data = History::select('time_leng', 'time_number')->where([
                    //'relation_type' => 2,
                    'info_id' => $val['id'],
                    'user_id' => $user_id,
                    'is_del' => 0,
                ])->orderBy('updated_at', 'desc')->first();
                if ($his_data) {
                    $works_data[$key]['time_leng'] = $his_data->time_leng;
                    $works_data[$key]['time_number'] = $his_data->time_number;
                }
            }
        }

        return $works_data;
    }

    static function GetWorksUrl($WorkArr)
    {
        if (!empty($WorkArr['callback_url3'])) {
            return $WorkArr['callback_url3'];
        }
        if (!empty($WorkArr['callback_url2'])) {
            return $WorkArr['callback_url2'];
        }
        if (!empty($WorkArr['callback_url1'])) {
            return $WorkArr['callback_url1'];
        }
        return $WorkArr['url'];
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
            ->select(['id', 'relation_type', 'relation_id', 'info_id', 'user_id', 'time_leng', 'time_number']);
    }

    //用于获取章节上下曲信息
    public function neighbor($params, $user)
    {
        $now_date = date('Y-m-d H:i:s');
        $works_id = $params['works_id'] ?? 0;
        $works_info_id = $params['works_info_id'] ?? 0;
        $ob = $params['ob'] ?? '';
        if($ob == ''){  //默认
            $ob = 'asc';
            if($works_id == 566){
                $ob = 'desc';
            }
        }

        //1 专栏  2作品 3直播  4会员 5线下产品  6讲座
        $type = $params['type'] ?? 0;
        if($type == 1 || $type == 6){
            $column_id = $params['column_id'] ?? 0;
            if (empty($column_id)) {
                return ['code' => false, 'msg' => 'id不存在'];
            }
//            $column_data = Works::select('id')->where(['column_id'=>$column_id])->first();
//            $works_id = $column_data['id'];


        }else{
            if (empty($works_id) || empty($works_info_id)) {
                return ['code' => false, 'msg' => '课程id不存在'];
            }
            $sub_relation_id = $works_id;
        }


        if($type == 6){
            $query = self::where(['column_id' => $column_id,'type'=>1])
                ->select(['id as works_info_id', 'pid as works_id', 'title', 'duration', 'free_trial', 'url',
                    'introduce', 'section','size','type', 'view_num', 'callback_url1', 'callback_url2', 'callback_url3']);
            $works_id = $column_id;  // 讲座直接关联info表
        }else{
            $query = self::where('pid', '=', $works_id)
                ->select(['id as works_info_id', 'pid as works_id', 'title', 'duration', 'free_trial', 'url',
                    'introduce', 'section', 'size','type', 'view_num', 'callback_url1', 'callback_url2', 'callback_url3']);
        }




        $query->with(['infoHistory' => function ($query) use ($works_id, $user) {
            $query->where('relation_id', '=', $works_id)->where('user_id', '=', $user['id'])->where('is_del', 0);
        }]);

        if ($ob == 'desc') {
            $query->orderBy('id', 'desc');
        } else {
            $query->orderBy('id', 'asc');
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



        if($type == 1 || $type == 6){

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
                ->select(['w.id', 'w.price', 'w.original_price' ,  'w.is_free', 'w.status','w.cover_pic as cover_img','w.comment_num','w.collection_num',
                    DB::raw('if(s.id > 0,1,0) as is_sub')])
                ->first();

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
                ->select(['w.id', 'w.price', 'w.original_price', 'w.is_pay', 'w.type', 'w.is_free', 'w.status','w.cover_img','w.comment_num','w.collection_num','w.is_audio_book',
                    DB::raw('if(s.id > 0,1,0) as is_sub')])
                ->first();
        }
        if($user['level'] > 2){
            $works_info->is_sub = 1;
        }

        $is_show_url = true;
        if ($works_info->is_free == 0 && $works_info->is_sub == 0) {
            $is_show_url = false;
        }
        $works_info->is_collection = 0;

        $collection_type = $type;
        if($type == 6){
            $collection_type = 7; //type 与收藏表类型有出入
        }



        //  收藏按总id走
        $collectionObj = Collection::select()->where([
            'user_id' => $user['id'],
            //'info_id' => $works_info_id,
            'relation_id' => $works_id,
        ]);
        if($type == 1 || $type == 6){
            $collection = $collectionObj->whereIn('type',[1,7])->get();
        }else if($type == 2){
            $collection = $collectionObj->whereIn('type',[2,6])->get();
        }else{
            $collection = [];
        }
        if($collection){
            $works_info->is_collection = 1;
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
        $SecretId  = "AKIDrcCpIdlpgLo4A4LMj7MPFtKfolWeNHnC";
        $SECRET_KEY= "MWXLwKVXMzPcrwrcDcrulPsAF7nIpCNM";
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
                                   $map['url'] = $v['url']; //原视频
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

}
