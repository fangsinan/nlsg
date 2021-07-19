<?php


namespace App\Servers;


use App\Models\CacheTools;
use App\Models\Column;
use App\Models\ImDoc;
use App\Models\ImDocSendJob;
use App\Models\ImDocSendJobInfo;
use App\Models\imDocSendJobLog;
use App\Models\ImGroup;
use App\Models\ImGroupUser;
use App\Models\Live;
use App\Models\MallCategory;
use App\Models\MallGoods;
use App\Models\Works;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;

class ImDocServers
{
    public function groupList($params,$user_id){
        $group_id_list = ImGroupUser::where('group_account','=',$user_id)
            ->where('exit_type','=',0)->pluck('group_id')->toArray();
        if (empty($group_id_list)){
            return [];
        }


        return ImGroup::whereIn('group_id',$group_id_list)
            ->where('status','=',1)
            ->select(['id','name'])
            ->get();
    }

    public function add($params, $user_id)
    {
        if (!empty($params['id'] ?? 0)) {
            $docModel = ImDoc::where('id', '=', $params['id'])
                ->where('status', '=', 1)
                ->select(['id'])->first();
            if (empty($docModel)) {
                return ['code' => false, 'msg' => 'id错误'];
            }
        } else {
            $docModel = new ImDoc();
        }

        $type = $params['type'] ?? 0;
        if (!in_array($type, [1, 2, 3])) {
            return ['code' => false, 'msg' => '内容类型错误'];
        }

        $type_info = $params['type_info'] ?? 0;
        $obj_id = $params['obj_id'] ?? 0;
        $content = $params['content'] ?? '';
        $cover_img = $params['cover_img'] ?? '';
        $subtitle = $params['subtitle'] ?? '';
        $status = $params['status'] ?? 1;
        $file_url = $params['file_url'] ?? '';
        $for_app = $params['for_app'] ?? 0;
        $second = $params['second'] ?? 0;
        $format = $params['format'] ?? '';

        if (!in_array($status, [1, 2])) {
            return ['code' => false, 'msg' => '状态错误'];
        }

        if ($type_info < ($type * 10) || $type_info > ($type * 10 + 9)) {
            return ['code' => false, 'msg' => '详细类型错误'];
        }


        //1商品 2附件 3文本
        switch (intval($params['type'])) {
            case 1:
                // 11:讲座 12课程 13商品 14会员 15直播 16训练营
                if (empty($obj_id)) {
                    return ['code' => false, 'msg' => '目标id错误'];
                }

                break;
            case 2:
                //21音频 22视频 23图片
                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }
                if (empty($file_url)) {
                    return ['code' => false, 'msg' => '附件地址不能为空'];
                }
                if (empty($format)) {
                    return ['code' => false, 'msg' => '格式后缀名不能为空'];
                }

                break;
            case 3:
                //31文本
                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }

                break;
        }

        if (in_array($params['type_info'],[21,22])){
            if (empty($second)){
                return ['code' => false, 'msg' => '时常不能为空'];
            }
        }

        $docModel->type = $type;
        $docModel->type_info = $type_info;
        $docModel->obj_id = $obj_id;
        $docModel->cover_img = $cover_img;
        $docModel->content = $content;
        $docModel->subtitle = $subtitle;
        $docModel->file_url = $file_url;
        $docModel->status = $status;
        $docModel->user_id = $user_id;
        $docModel->second = $second;
        $docModel->format = $format;

        DB::beginTransaction();

        $res = $docModel->save();
        if ($res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

        if ($for_app == 1) {
            $jobModel = new ImDocSendJob();
            $jobModel->doc_id = $docModel->id;
            $jobModel->status = 2;
            $jobModel->send_type = 3;
            $jobModel->is_done = 4;
            $jobModel->month = date('Y-m');
            $jobModel->day = date('Y-m-d');
            $job_res = $jobModel->save();
            if ($job_res === false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败'];
            }
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];

    }

    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = ImDoc::query();
        if (!empty($params['id'] ?? 0)) {
            $query->where('id', '=', $params['id']);
        }
        if (!empty($params['type_info'] ?? 0)) {
            $query->where('type_info', '=', $params['type_info']);
        }
        $query->where('status', '=', 1)
            ->orderBy('id', 'desc')
            ->select([
                'id', 'type', 'type_info', 'obj_id', 'cover_img', 'content', 'file_url'
            ]);

        return $query->paginate($size);
    }

    public function changeStatus($params, $user_id)
    {
        $id = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        $check = ImDoc::where('id', '=', $id)->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        switch ($flag) {
            case 'del':
                $check->status = 2;
                break;
            default:
                return ['code' => false, 'msg' => '动作错误'];
        }

        $res = $check->save();
        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        } else {
            return ['code' => false, 'msg' => '失败'];
        }
    }

    public function addSendJob($params, $user_id)
    {
        $job_str = '';

        if (!empty($params['id'] ?? 0)) {
            $jobModel = ImDocSendJob::where('id', '=', $params['id'])
                ->whereIn('status', [1, 2])
                ->select(['id'])->first();
            if (empty($jobModel)) {
                return ['code' => false, 'msg' => 'id错误'];
            } else {
                if (in_array($jobModel->is_done, [2, 3])) {
                    return ['code' => false, 'msg' => '发送中和已完成的任务无法编辑'];
                }

                if ($jobModel->user_id != $user_id) {
                    return ['code' => false, 'msg' => '任务只有创建人自己能编辑'];
                }
            }
            $job_str .= '修改:';
        } else {
            $jobModel = new ImDocSendJob();
            $job_str .= '添加:';
        }

        $doc_id = $params['doc_id'] ?? 0;
        $send_type = $params['send_type'] ?? 0;
        $send_at = $params['send_at'] ?? '';
        $info = $params['info'] ?? [];
        $now = time();
        $month = date('Y-m', $now);
        $day = date('Y-m-d', $now);

        $check_doc = ImDoc::where('id', '=', $doc_id)->where('status', '=', 1)->first();
        if (empty($check_doc)) {
            return ['code' => false, 'msg' => '文案不存在'];
        }
        if (!in_array($send_type, [1, 2])) {
            return ['code' => false, 'msg' => '发送时间类型错误'];
        }
        if ($send_type == 2) {
            if (empty($send_at)) {
                return ['code' => false, 'msg' => '定时时间不能为空'];
            } else {
                $temp_line = date('Y-m-d H:i:00', strtotime('+1 minute'));
                if ($send_at <= $temp_line) {
                    return ['code' => false, 'msg' => '发送时间错误' . $temp_line];
                }
            }
            $month = date('Y-m', strtotime($send_at));
            $day = date('Y-m-d', strtotime($send_at));
        } else {
            $send_at = date('Y-m-d H:i:s');
        }

        $add_info_data = [];
        foreach ($info as $v) {
            $temp_type = $v['type'] ?? 0;
            if (!in_array($temp_type, [1, 2, 3])) {
                return ['code' => false, 'msg' => '发送对象类型错误'];
            }
            foreach ($v['list'] as $vv) {
                $temp_info_data = [];
                $temp_info_data['send_obj_type'] = $temp_type;
                $temp_info_data['send_obj_id'] = $vv;
                $add_info_data[] = $temp_info_data;
            }
        }

        if (empty($add_info_data)) {
            return ['code' => false, 'msg' => '发送目标数据不能为空'];
        }

        $jobModel->doc_id = $doc_id;
        $jobModel->status = 1;
        $jobModel->send_type = $send_type;
        $jobModel->send_at = $send_at;
        $jobModel->is_done = 1;
        $jobModel->user_id = $user_id;
        $jobModel->month = $month;
        $jobModel->day = $day;

        $job_str .= "id($doc_id),send_type($send_type),info(" . json_encode($info) . ")";


        DB::beginTransaction();

        $job_ers = $jobModel->save();
        if ($job_ers == false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败' . __LINE__];
        }

        if (!empty($params['id'])) {
            $del_res = ImDocSendJobInfo::where('job_id', '=', $jobModel->id)->delete();
            if ($del_res == false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败' . __LINE__];
            }
        }

        foreach ($add_info_data as &$v) {
            $v['job_id'] = $jobModel->id;
        }

        $add_res = DB::table('nlsg_im_doc_send_job_info')->insert($add_info_data);
        if ($add_res == false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败' . __LINE__];
        }

        $logModel = new imDocSendJobLog();
        $logModel->job_id = $jobModel->id;
        $logModel->user_id = $user_id;
        $logModel->record = $job_str;
        $log_res = $logModel->save();
        if ($log_res == false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败' . __LINE__];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function sendJobList($params)
    {
        $size = $params['size'] ?? 10;

        $query = ImDocSendJob::query()->with(['docInfo', 'jobInfo'])->where('status', '<>', 3);

        $send_obj_type = $params['send_obj_type'] ?? 0;
        $send_obj_id = $params['send_obj_id'] ?? 0;
        $doc_type = $params['doc_type'] ?? 0;
        $doc_type_info = $params['doc_type_info'] ?? 0;
        $is_done = $params['is_done'] ?? 0;

        if (!empty($send_obj_type)) {
            $query->whereHas('jobInfo', function ($q) use ($send_obj_type) {
                $q->where('send_obj_type', '=', $send_obj_type);
            });
        } else {
//            return ['code' => false, 'msg' => '目标类型参数无效'];
        }
        if (!empty($send_obj_id)) {
            $query->whereHas('jobInfo', function ($q) use ($send_obj_id) {
                $q->where('send_obj_id', '=', $send_obj_id);
            });
        } else {
//            return ['code' => false, 'msg' => '目标id参数无效'];
        }
        if (!empty($doc_type)) {
            $query->whereHas('docInfo', function ($q) use ($doc_type) {
                $q->where('type', '=', $doc_type);
            });
        }
        if (!empty($doc_type_info)) {
            $query->whereHas('docInfo', function ($q) use ($doc_type_info) {
                $q->where('type_info', '=', $doc_type_info);
            });
        }
        if (!empty($is_done)) {
            $query->where('is_done', '=', $is_done);
        }


        $query->select([
            'id', 'doc_id', 'created_at', 'status', 'send_type', 'is_done', 'success_at'
        ]);

        return $query->paginate($size);
    }

    //app的文案列表
    public function sendJobListForApp($params)
    {
        $send_obj_type = $params['send_obj_type'] ?? 0;
        $send_obj_id = $params['send_obj_id'] ?? 0;
        $size = $params['size'] ?? 10;
        $page = $params['page'] ?? 1;
        $offset = ($page - 1) * $size;

        if (empty($send_obj_type) || empty($send_obj_id)) {
            return ['code' => false, 'msg' => '目标id参数无效'];
        }

        $job_id_list = ImDocSendJobInfo::query()
            ->where('send_obj_type', '=', $send_obj_type)
            ->where('send_obj_id', '=', $send_obj_id)
            ->pluck('job_id')
            ->toArray();

        $month_list = ImDocSendJob::query()
            ->where(function ($q) use ($job_id_list) {
                $q->whereIn('id', $job_id_list)
                    ->orWhere('send_type', '=', 3);
            })
            ->whereIn('status', [1, 2])
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit($size)
            ->offset($offset)
            ->pluck('month')
            ->toArray();

        if (empty($month_list)) {
            return [];
        }

        $list = [];
        foreach ($month_list as $v) {
            $temp_list = [];
            $temp_list['month'] = $v;
            $temp_list['list'] = ImDocSendJob::query()
                ->with(['docInfo', 'jobInfo'])
                ->where(function ($q) use ($job_id_list) {
                    $q->whereIn('id', $job_id_list)
                        ->orWhere('send_type', '=', 3);
                })
                ->where('month', '=', $v)
                ->where('status', '<>', 3)
                ->select([
                    'id', 'doc_id', 'created_at', 'status', 'send_type', 'is_done', 'success_at'
                ])->get();
            $list[] = $temp_list;
        }
        return $list;
    }

    public function changeJobStatus($params, $user_id)
    {
        if (!empty($params['id'] ?? 0)) {
            $jobModel = ImDocSendJob::where('id', '=', $params['id'])
                ->whereIn('status', [1, 2])
                ->select(['id'])->first();
            if (empty($jobModel)) {
                return ['code' => false, 'msg' => 'id错误'];
            } else {
                if (in_array($jobModel->is_done, [2, 3])) {
                    return ['code' => false, 'msg' => '发送中和已完成的任务无法编辑'];
                }

                if ($jobModel->user_id != $user_id) {
                    return ['code' => false, 'msg' => '任务只有创建人自己能编辑'];
                }
            }

            $job_str = '修改:id($jobModel->id)';

            $flag = $params['flag'] ?? '';
            switch ($flag) {
                case 'on':
                    $jobModel->status = 1;
                    break;
                case 'off':
                    $jobModel->status = 2;
                    break;
                case 'del':
                    $jobModel->status = 3;
                    break;
                default:
                    return ['code' => false, 'msg' => '参数错误'];
            }

            $job_str .= "flag($flag)";

            DB::beginTransaction();

            $res = $jobModel->save();
            if ($res == false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败' . __LINE__];
            }

            $logModel = new imDocSendJobLog();
            $logModel->job_id = $jobModel->id;
            $logModel->user_id = $user_id;
            $logModel->record = $job_str;
            $log_res = $logModel->save();
            if ($log_res == false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败' . __LINE__];
            }

            DB::commit();
            return ['code' => true, 'msg' => '成功'];

        } else {
            return ['code' => false, 'msg' => 'id错误'];
        }
    }

    public function getCategoryProduct($params)
    {
        $category_id = $params['category_id'] ?? 0;//0 为全部
        $type = $params['type'] ?? 0;
        switch ($type) {
            case 1:
                $cate_id_arr = [];
                $cate_data = WorksCategory::find($category_id);
                if ($cate_data['level'] == 1) {
                    $cate_arr = WorksCategory::select('id')->where(['pid' => $cate_data['id'], 'status' => 1
                    ])->get()->toArray();
                    $cate_id_arr = array_column($cate_arr, 'id');
                }
                $where = [
                    'works.status' => 4,
                    'works.type' => 2,
                    'works.is_audio_book' => 0
                ];
                $relationObj = new WorksCategoryRelation();
                $worksObj = new Works();
                $query = DB::table($relationObj->getTable(), ' relation')
                    ->leftJoin($worksObj->getTable() . ' as works', 'works.id', '=', 'relation.work_id')
                    ->select('works.id', 'works.type', 'works.title', 'works.user_id', 'works.cover_img', 'works.price',
                        'works.original_price', 'works.subtitle',
                        'works.works_update_time', 'works.detail_img', 'works.content', 'relation.id as relation_id',
                        'relation.category_id', 'relation.work_id', 'works.column_id',
                        'works.comment_num', 'works.chapter_num', 'works.subscribe_num', 'works.collection_num',
                        'works.is_free');
                if ($cate_id_arr && $category_id != 0) {
                    $query->whereIn('relation.category_id', $cate_id_arr);
                }

                $lists = $query->where($where)
                    ->orderBy('works.created_at', 'desc')
                    ->groupBy('works.id')
                    ->paginate(10)
                    ->toArray();

                break;
            case 2:
                $lists = Column::select('id', 'user_id', 'name', 'title', 'subtitle', 'cover_img', 'price', 'status',
                    'created_at',
                    'info_num')
                    ->where('type', 2)
                    ->where('status', '<>', 3)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;
            case  3:
                $query = MallGoods::query();
                if ($category_id != 0) {
                    $query->where('category_id', $category_id);
                }
                $lists = $query->select('id', 'name', 'subtitle', 'picture', 'status')
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;
            case 4:
                $lists = Live::select('id', 'user_id', 'title', 'price', 'order_num', 'status', 'begin_at', 'cover_img')
                    ->where('is_del', 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;
            case 5:
                $lists = Column::select('id', 'user_id', 'name', 'title', 'subtitle', 'cover_img', 'price', 'status',
                    'created_at',
                    'info_num')
                    ->where('type', 3)
                    ->where('status', '<>', 3)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->toArray();
                break;

        }
        return $lists ?? [];
    }

    public function getCategory()
    {
        $cache_key_name = 'works_category_list';
        $expire_num = CacheTools::getExpire('goods_category_list');
        $works_category = Cache::get($cache_key_name);
        if (empty($res)) {
            $works_category = WorksCategory::select('id', 'name', 'pid', 'level', 'sort')
                ->where(['status' => 1,])
                ->orderBy('sort', 'asc')
                ->get()
                ->toArray();
            Cache::put($cache_key_name, $works_category, $expire_num);
        }

        $mall = new MallCategory();
        $goods_category = $mall->getUsedList();

        return [
            'works' => [
                'type' => 1,
                'name' => '精品课',
                'category' => $works_category
            ],
            'lecture' => [
                'type' => 2,
                'name' => '讲座'
            ],
            'goods' => [
                'type' => 3,
                'name' => '商品',
                'category' => $goods_category
            ],
            'live' => [
                'name' => '直播训练营',
                'category' => [
                    [
                        'id' => '100001',
                        'type' => 4,
                        'name' => '直播'
                    ],
                    [
                        'id' => '100002',
                        'type' => 5,
                        'name' => '训练营'
                    ],
                    [
                        'id' => '100003',
                        'type' => 6,
                        'name' => '幸福360'
                    ],
                ]
            ]
        ];

    }

    public function getMsgRandom()
    {
        $date = date('YmdHi');
        $cache_key_name = 'msg_counter_' . $date;
        $expire_num = 60;
        $counter = Cache::get($cache_key_name);
        if ($counter < 1) {
            Cache::put($cache_key_name, 1, $expire_num);
        }
        $counter = Cache::increment($cache_key_name);
        return $date . sprintf("%010d", $counter);
    }

    public function imgUrl($url){
        $url = ltrim($url,'/');
        if (substr($url,0,4) != 'http'){
            $url = 'http://image.nlsgapp.com/'.$url;
        }
        return $url;
    }

    public function sendGroupDocMsgJob()
    {
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");

        $job_info = ImDocSendJob::query()
            ->where('is_done', '=', 1)
            ->where('status', '=', 1)
            ->where(function ($q) {
                $q->where('send_type', '=', 1)
                    ->orWhere(function ($q) {
                        $q->where('send_type', '=', 2)->where('send_at', '<=', date('Y-m-d H:i:59'));
                    });
            })
            ->with(['jobInfo', 'jobInfo.groupInfo:id,group_id', 'docInfo'])
            ->get();


        if ($job_info->isEmpty()) {
            return ['code' => true, 'msg' => '没有任务'];
        }

        $job_id_list = [];
        $post_data_array = [];

        foreach ($job_info as $v) {
            $job_id_list[] = $v->id;
            foreach ($v->jobInfo as $vv) {
                $key = $vv->groupInfo->group_id . '_' . $v->user_id;
                if (!isset($post_data_array[$key])) {
                    $post_data_array[$key]['user_id'] = $v->user_id;
                    $post_data_array[$key]['group_id'] = $vv->groupInfo->group_id;
                    $post_data_array[$key]['doc_list'] = [];
                }
                $list_key = $v->doc_id;
                if (!isset($post_data_array[$key]['doc_list'][$list_key])) {
                    $post_data_array[$key]['doc_list'][$list_key] = $v->docInfo;
                }
            }
        }

        foreach ($post_data_array as $v) {
            $post_data = [
                "GroupId" => $v['group_id'],
                "From_Account" => (string)$v['user_id'],
                "Random" => $this->getMsgRandom(),
                "MsgBody" => []
            ];


            $temp_msg_type = 0;

            foreach ($v['doc_list'] as $vv) {
                switch ($vv['type_info']) {
                    case 11:
                        if (empty($temp_msg_type)){
                            $temp_msg_type = 7;
                        }
                    case 12:
                        if (empty($temp_msg_type)){
                            $temp_msg_type = 2;
                        }
                    case 13:
                        if (empty($temp_msg_type)){
                            $temp_msg_type = 3;
                        }
                    case 14:
                        if (empty($temp_msg_type)){
                            $temp_msg_type = 6;
                        }
                    case 15:
                        if (empty($temp_msg_type)){
                            $temp_msg_type = 9;
                        }
                    case 16:
                        if (empty($temp_msg_type)){
                            $temp_msg_type = 11;
                        }
                        break;
//                        $post_data['MsgBody'][] = [
//                            "MsgType"=> "TIMCustomElem",
//                            "MsgContent"=> [
//                                "Data"=> "message",
//                                "Desc"=> [
//                                    "goodsID"=>450,
//                                    "cover_pic"=>$this->imgUrl($vv->cover_img),
//                                    "titleName"=>$vv->content,
//                                    "subtitle"=>$vv->subtitle,
//                                    "type"=>$temp_msg_type,
//                                ],
//                                "Ext"=> "url",
//                            ]
//                        ];
//                        break;
                    case 21://音频
                        $post_data['MsgBody'][] = [
                            "MsgType" => "TIMSoundElem",
                            "MsgContent" => [
                                "Url" => $vv->file_url,
                                "Size" => $vv->file_size,
                                "Second" => $vv->second,
                                "Download_Flag" => 2
                            ]
                        ];
                        break;
                    case 22:
                        //视频
                        $post_data['MsgBody'][] = [
                            "MsgType" => "TIMVideoFileElem",
                            "MsgContent" => [
                                "VideoUrl" => $vv->file_url,
                                "VideoSize" => $vv->file_size,
                                "VideoSecond" => $vv->second,
                                "VideoFormat" => $vv->format,
                                "VideoDownloadFlag" => 2,
//                                "ThumbUrl" => "https://0345-1400187352-1256635546.cos.ap-shanghai.myqcloud.com/abcd/a6c170c9c599280cb06e0523d7a1f37b",
//                                "ThumbSize" => 13907,
//                                "ThumbWidth" => 720,
//                                "ThumbHeight" => 1280,
//                                "ThumbFormat" => "JPG",
//                                "ThumbDownloadFlag" => 2
                            ]
                        ];
                        break;
                    case 23:
                        //图片
                        $file_url = explode(',',$vv->file_url);
                        foreach ($file_url as $vvv){
                            $post_data['MsgBody'][] = [
                                "MsgType" => "TIMImageElem",
                                "MsgContent" => [
//                                    "UUID" => "1853095_D61040894AC3DE44CDFFFB3EC7EB720F",
                                    "UUID" => $this->getMsgRandom(),
                                    "ImageFormat" => 1,
                                    "ImageInfoArray" => [
                                        [
                                            "Type" => 1,           //原图
//                                            "Size" => 1853095,
//                                            "Width" => 2448,
//                                            "Height" => 3264,
                                            "URL" => $this->imgUrl($vvv),
                                        ]
                                    ]
                                ]
                            ];
                        }

                        break;
                    case 31:
                        //文本
                        $post_data['MsgBody'][] = [
                            "MsgType" => "TIMTextElem",
                            "MsgContent" => [
                                "Text" => $vv->content,
                            ]
                        ];
                        break;
                }

//                if (empty($post_data['MsgBody'])){
//                    continue;
//                }
//                $res = ImClient::curlPost($url, json_encode($post_data));
//                $res = json_decode($res, true);
//                dd([$res,$post_data]);
//                dd($post_data);
            }
//            dd(json_encode($post_data));
            dd($post_data);
            $res = ImClient::curlPost($url, json_encode($post_data));
            $res = json_decode($res, true);
            dd($res);
        }


//        return [$post_data_array, $job_info];
        return $post_data_array;
        return $job_info;


        $random = $this->getMsgRandom();
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");

        //文案类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 21音频 22视频 23图片 31文本)

        //发送类型( 1专栏 2精品课 3商品 4 线下产品门票类 6新会员 7:讲座 8:听书 9:直播)
        $post_data = [
            'GroupId' => '@TGS#2OOSYXIHU',
            'Random' => $random,
        ];

//@property (nonatomic , strong) NSString  *goodsID;
//@property (nonatomic , strong) NSString  *cover_pic;
//@property (nonatomic , strong) NSString  *titleName;
//@property (nonatomic , strong) NSString  *subtitle;
//@property (nonatomic , strong) NSString  *price;
//@property (nonatomic , strong) NSString  *type;//类型( 1专栏 2精品课 3商品 4 线下产品门票类 6新会员 7:讲座 8:听书 9:直播 10:外链 11:训练营)


        //10自定类型
        //20文件
        //30 文本

//        $post_data = [
//            "GroupId" => "@TGS#2OOSYXIHU",
//            "From_Account" => "168934", // 指定消息发送者（选填）
//            "Random" => time().rand(1000000,9999999), // 随机数字，五分钟数字相同认为是重复消息
//            "MsgBody" => [ // 消息体，由一个element数组组成，详见字段说明
//                [
//                    "MsgType" => "TIMTextElem", // 文本
//                    "MsgContent" =>[
//                        "Text" => "red packet".__LINE__
//                    ]
//                ],
//                [
//                    "MsgType" => "TIMFaceElem", // 表情
//                    "MsgContent" => [
//                        "Index" => 6,
//                        "Data" => "abc\u0000\u0001"
//                    ]
//                ]
//            ]
//        ];

        dd($post_data);

        $res = ImClient::curlPost($url, json_encode($post_data));
        $res = json_decode($res, true);

        dd($res);
    }
}
