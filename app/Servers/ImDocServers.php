<?php


namespace App\Servers;


use App\Models\CacheTools;
use App\Models\ConfigModel;
use App\Models\ImDoc;
use App\Models\ImDocSendJob;
use App\Models\ImDocSendJobInfo;
use App\Models\imDocSendJobLog;
use App\Models\ImGroup;
use App\Models\ImGroupUser;
use App\Models\Live;
use App\Models\LiveInfo;
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
    public function groupList($params, $user_id)
    {
        $group_id_list = ImGroupUser::where('group_account', '=', $user_id)
            ->where('exit_type', '=', 0)->pluck('group_id')->toArray();
        if (empty($group_id_list)) {
            return [];
        }

        return ImGroup::whereIn('group_id', $group_id_list)
            ->where('status', '=', 1)
            ->select(['id', 'name'])
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
        $file_size = $params['file_size'] ?? 0;
        $for_app = $params['for_app'] ?? 0;
        $second = $params['second'] ?? 0;
        $format = $params['format'] ?? '';
        $img_size = $params['img_size'] ?? 0;
        $img_width = $params['img_width'] ?? 0;
        $img_height = $params['img_height'] ?? 0;
        $img_format = $params['img_format'] ?? 0;
        $file_md5 = $params['file_md5'] ?? 0;
        $img_md5 = $params['img_md5'] ?? 0;

        if (!in_array($status, [1, 2])) {
            return ['code' => false, 'msg' => '状态错误'];
        }

        if ($type_info < ($type * 10) || $type_info > ($type * 10 + 9)) {
            return ['code' => false, 'msg' => '详细类型错误'];
        }

        //1商品 2附件 3文本
        switch (intval($params['type'])) {
            case 1:
                // 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链
                if ($type_info = 17) {
                    //判断网址
                } else {
                    if (empty($obj_id)) {
                        return ['code' => false, 'msg' => '目标id错误'];
                    }

                    if (empty($cover_img)) {
                        return ['code' => false, 'msg' => 'cover_img错误', 'ps' => '封面图'];
                    }
                    if (empty($cover_img)) {
                        return ['code' => false, 'msg' => 'content错误', 'ps' => '标题'];
                    }
                }
                break;
            case 2:
                //21音频 22视频 23图片 24文件
                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }
                if (empty($file_url)) {
                    return ['code' => false, 'msg' => '附件地址不能为空'];
                }
                if (empty($format)) {
                    return ['code' => false, 'msg' => '格式后缀名不能为空format'];
                }


                if (in_array($params['type_info'], [21, 22, 24])) {
                    if (empty($file_size)) {
                        return ['code' => false, 'msg' => 'file_size不能为空'];
                    }
                }


                if (in_array($params['type_info'], [21, 22])) {
                    if (empty($second)) {
                        return ['code' => false, 'msg' => 'second不能为空'];
                    }
                }
                if ($params['type_info'] == 22 || $params['type_info'] == 21) {
                    if (empty($file_md5)) {
                        return ['code' => false, 'msg' => '文件md5不能为空'];
                    }
                }
                if ($params['type_info'] == 22) {
                    //如果是视频,必须有封面
                    if (empty($cover_img) || empty($img_size) || empty($img_width)
                        || empty($img_height) || empty($img_format) || empty($img_md5)) {
                        return ['code' => false, 'msg' => '必须有封面和尺寸长宽后缀名参数'];
                    }
                }
                if ($params['type_info'] == 23) {
                    //url,size,width,height,md5
                    $file_url = explode(';', $file_url);
                    foreach ($file_url as $fuv) {
                        $fuv = explode(',', $fuv);
                        if (count($fuv) != 5) {
                            return ['code' => false, 'msg' => '图片参数格式错误'];
                        }
                    }
                }
                break;
            case 3:
                //31文本
                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }

                break;
        }

        $docModel->type = $type;
        $docModel->type_info = $type_info;
        $docModel->obj_id = $obj_id;
        $docModel->cover_img = $cover_img;
        $docModel->content = $content;
        $docModel->subtitle = $subtitle;
        $docModel->file_url = $file_url;
        $docModel->file_size = $file_size;
        $docModel->status = $status;
        $docModel->user_id = $user_id;
        $docModel->second = $second;
        $docModel->format = $format;
        $docModel->img_size = $img_size;
        $docModel->img_width = $img_width;
        $docModel->img_height = $img_height;
        $docModel->img_format = $img_format;
        $docModel->img_format = $file_md5;
        $docModel->img_format = $img_md5;

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
        if (!is_array($info)){
            $info = json_decode($info,true);
        }

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
            if (!is_array($v['list'])){
                $v['list'] = explode(',',$v['list']);
            }
            foreach ($v['list'] as $vv) {
                $temp_info_data = [];
                $temp_info_data['send_obj_type'] = $temp_type;
                $temp_obj_id = ImGroup::getId($vv);
                $temp_obj_id = $temp_obj_id['id'];
                $temp_info_data['send_obj_id'] = $temp_obj_id;
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

        $query = ImDocSendJob::query()
            ->with(['docInfo', 'jobInfo', 'jobInfo.groupInfo:id,name'])
            ->where('status', '<>', 3);

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
        $group_id = $params['group_id'] ?? 0;
        $send_obj_type = $params['send_obj_type'] ?? 0;
        $send_obj_id = ImGroup::getId($group_id);
        $send_obj_id = $send_obj_id['id'];

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
                ->first();
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
        $size = $params['size'] ?? 10;
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
                    ->leftJoin('nlsg_user as u', 'works.user_id', '=', 'u.id')
                    ->select(['works.id', 'works.type', 'works.title', 'works.cover_img', 'works.price', 'works.subtitle',
                        'works.title as doc_content', DB::raw('1 as doc_type'), DB::raw('12 as doc_type_info'),
                        'relation.category_id', 'works.is_end', 'u.nickname']);
                if ($cate_id_arr && $category_id != 0) {
                    $query->whereIn('relation.category_id', $cate_id_arr);
                }

                $lists = $query->where($where)
                    ->orderBy('works.created_at', 'desc')
                    ->groupBy('works.id')
                    ->paginate($size);
                break;
            case 2:
                $lists = DB::table('nlsg_column as col')
                    ->join('nlsg_user as u', 'col.user_id', '=', 'u.id')
                    ->where('col.type', '=', 2)
                    ->where('col.status', '<>', 3)
                    ->orderBy('col.id', 'desc')
                    ->select([
                        'col.id', 'col.user_id', 'col.name', 'col.title', 'col.subtitle',
                        DB::raw('col.name as doc_content'),
                        DB::raw('1 as doc_type'), DB::raw('11 as doc_type_info'),
                        'col.cover_pic as cover_img', 'col.price', 'col.status', 'col.is_end',
                        'col.info_num', 'u.nickname'])
                    ->paginate($size);

                break;
            case 3:
                $query = MallGoods::query();
                if ($category_id != 0) {
                    $query->where('category_id', $category_id);
                }
                $lists = $query->select(['id', 'name', 'subtitle', 'picture', 'status', 'picture as cover_img',
                    DB::raw('name as doc_content'), 'price',
                    DB::raw('1 as doc_type'), DB::raw('13 as doc_type_info'),])
                    ->orderBy('created_at', 'desc')
                    ->paginate($size);

                $gModel = new MallGoods();
                foreach ($lists as &$v) {
                    $v->stock = $gModel->getGoodsAllStock($v->id);
                }

                break;
            case 4:
                $lists = Live::select(['id', 'user_id', 'title', 'price', 'order_num',
                    DB::raw('title as doc_content'),
                    DB::raw('1 as doc_type'), DB::raw('15 as doc_type_info'),
                    'status', 'begin_at', 'cover_img'])
                    ->where('is_del', 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate($size);
                foreach ($lists as &$val) {
                    $val['live_status'] = 1;  //默认值
                    $channel = LiveInfo::where('live_pid', $val['id'])
                        ->where('status', 1)
                        ->orderBy('id', 'desc')
                        ->first();
                    if ($channel) {
                        if ($channel->is_begin == 0 && $channel->is_finish == 0) {
                            $val['live_status'] = 1;
                        } elseif ($channel->is_begin == 1 && $channel->is_finish == 0) {
                            $val['live_status'] = 3;
                        } elseif ($channel->is_begin == 0 && $channel->is_finish == 1) {
                            $val['live_status'] = 2;
                        }
                    }
                }
                break;
            case 5:
                $lists = DB::table('nlsg_column as col')
                    ->join('nlsg_user as u', 'col.user_id', '=', 'u.id')
                    ->where('col.type', '=', 3)
                    ->where('col.status', '<>', 3)
                    ->orderBy('col.id', 'desc')
                    ->select([
                        'col.id', 'col.user_id', 'col.name', 'col.title', 'col.subtitle',
                        DB::raw('col.name as doc_content'),
                        DB::raw('1 as doc_type'), DB::raw('16 as doc_type_info'),
                        'col.cover_pic as cover_img', 'col.price', 'col.status', 'col.is_end',
                        'col.info_num', 'u.nickname'])
                    ->paginate($size);
                break;
            case 6:
                $lists = [
                    'current_page' => 1,
                    "first_page_url" => "http://127.0.0.1:8000/api/admin_v4/im_doc/category/product?page=1",
                    "from" => 1,
                    "last_page" => 1,
                    "last_page_url" => "",
                    "next_page_url" => "",
                    "path" => "",
                    "per_page" => 1,
                    "prev_page_url" => null,
                    "to" => 1,
                    "total" => 1,
                    'data' => [
                        ['id' => 1,
                            'name' => '360幸福大使',
                            'title' => '360幸福大使',
                            'subtitle' => '360幸福大使',
                            'cover_img' => ConfigModel::getData(22),
                            'doc_content' => '360幸福大使',
                            'doc_type' => 1,
                            'doc_type_info' => 14,
                            'price' => 360
                        ]
                    ]
                ];
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

    public function imgUrl($url)
    {
        $url = ltrim($url, '/');
        if (substr($url, 0, 4) != 'http') {
            $url = 'http://image.nlsgapp.com/' . $url;
        }
        return $url;
    }

    public function test()
    {
        if (0) {
            //拉去群消息记录
            $url = ImClient::get_im_url('https://console.tim.qq.com/v4/group_open_http_svc/group_msg_get_simple');
            $body = [
                "GroupId" => "@TGS#2GZCYDJHT",    //拉取消息的群 ID
                "ReqMsgNumber" => 2    //需要拉取的消息条数
            ];
            $res = ImClient::curlPost($url, json_encode($body));
            $res = json_decode($res, true);
            dd([$res, $body]);
        }

        if (0) {
            $url = ImClient::get_im_url('https://console.tim.qq.com/v4/group_open_http_svc/add_group_member');
            //加人
            $body = [
                "GroupId" => "@TGS#2GZCYDJHT", // 要操作的群组（必填）
                "Silence" => 1, // 是否静默加人（选填）
                "MemberList" => [ // 一次最多添加300个成员
                    [
                        "Member_Account" => "425232" // 要添加的群成员 ID（必填）
                    ],
                ]
            ];
            $res = ImClient::curlPost($url, json_encode($body));
            $res = json_decode($res, true);
            dd([$res, $body]);
        }


        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");

        $custom_elem_body = [
            "goodsID" => "81",
//            "cover_pic" => "https://image.nlsgapp.com/nlsg/goods/20201218171703214387.jpg",
            "cover_pic" => "nlsg/goods/20201218171703214387.jpg",
            "titleName" => "自定标题sdfsdf" . rand(1, 999),
            "subtitle" => "自定副标题fdfaas" . rand(1, 999),
            "type" => "3",
        ];
        $custom_elem_body = json_encode($custom_elem_body);

        $post_data = [
            "GroupId" => "@TGS#2GZCYDJHT",
            "From_Account" => "168934", // 指定消息发送者（选填）
            "Random" => $this->getMsgRandom(), // 随机数字，五分钟数字相同认为是重复消息
            "MsgBody" => [ // 消息体，由一个element数组组成，详见字段说明
//                [
//                    "MsgType" => "TIMFaceElem", // 表情
//                    "MsgContent" => [
//                        "Index" => 6,
//                        "Data" => "abc\u0000\u0001"
//                    ]
//                ],
//                [
//                    "MsgType" => "TIMTextElem", // 文本
//                    "MsgContent" =>[
//                        "Text" => "red packet".__LINE__
//                    ]
//                ],
//                [
//                    "MsgType" => "TIMCustomElem", // 自定义,不成功
//                    "MsgContent" => [
//                        "Data" => $custom_elem_body,
//                        "Desc" => '',
//                        'Ext' => '',
//                        'Sound' => '',
//                    ]
//                ],
//                [
//                    "MsgType" => "TIMSoundElem",//音频
//                    "MsgContent" => [
//                        "Url" => "https://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/f63da4f95285890780889058541/aaodecBf5FAA.mp3",
//                        "Size" => 4426079,
//                        "Second" => 275,
//                        "Download_Flag" => 2
//                    ]
//                ],
//                [
//                    "MsgType" => "TIMVideoFileElem",//视频
//                    "MsgContent" => [
//                        "VideoUrl" => 'https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/345e2a389fe32d62fedad3d6d2150110.mp4',
//                        "VideoUUID" => 'sdfafafasfafamp4',
//                        "VideoSize" => 1247117,
//                        "VideoSecond" => 7,
//                        "VideoFormat" => 'mp4',
//                        "VideoDownloadFlag" => 2,
//                        "ThumbUrl" => "https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/643665ba437cf198a9961f85795d8474.jpg?imageMogr2/",
//                        "ThumbUUID" => '131231313jpg',
//                        "ThumbSize" => 277431,
//                        "ThumbWidth" => 720,
//                        "ThumbHeight" => 1600,
//                        "ThumbFormat" => "jpg",
//                        "ThumbDownloadFlag" => 2
//                    ]
//                ],
                [
                    "MsgType" => "TIMImageElem", //图片
                    "MsgContent" => [
                        "UUID" => "8912484a9e64fa8fa89f84c1e6371edc1231",
                        "ImageFormat" => 255,
                        "ImageInfoArray" => [
                            [
                                "Type" => 1,
                                "Size" => 4534,
                                "Width" => 200,
                                "Height" => 200,
                                "URL" => "http://image.nlsgapp.com/nlsg/works/20210526154253564773.png"
                            ],
                            [
                                "Type" => 2,
                                "Size" => 4534,
                                "Width" => 0,
                                "Height" => 0,
                                "URL" => "http://image.nlsgapp.com/nlsg/works/20210526154253564773.png"],
                            [
                                "Type" => 3,
                                "Size" => 4534,
                                "Width" => 0,
                                "Height" => 0,
                                "URL" => "http://image.nlsgapp.com/nlsg/works/20210526154253564773.png"],
                        ]
                    ]
                ],
//                [
//                    "MsgType" => "TIMFileElem",//文件音频
//                    "MsgContent" => [
//                        "Url" => "https://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/f63da4f95285890780889058541/aaodecBf5FAA.mp3",
//                        "FileSize" => 4426079,
//                        "FileName" => "fdldaslf.mp3",
//                        "Download_Flag" => 2
//                    ]
//                ],
//                [
//                    "MsgType" => "TIMFileElem",//文件视频
//                    "MsgContent" => [
//                        "Url" => "https://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/70085c833701925921203766717/zR8GaCZiBnUA.mp4",
//                        "FileSize" => 407499,
//                        "FileName" => "fdffa.mp4",
//                        "Download_Flag" => 2
//                    ]
//                ],
            ]
        ];

        $res = ImClient::curlPost($url, json_encode($post_data));
        $res = json_decode($res, true);
        dd([$res, $post_data]);
    }

    public function sendGroupDocMsgJob()
    {
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
                $temp_post_data = [];
                $temp_post_data['GroupId'] = $vv->groupInfo->group_id;
                $temp_post_data['From_Account'] = (string)$v->user_id;
                $temp_post_data['Random'] = $this->getMsgRandom();
                $temp_post_data['MsgBody'] = [];
                $temp_msg_type = 0;
                switch (intval($v->docInfo->type_info)) {
                    case 11:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 7;
                        }
                    case 12:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 2;
                        }
                    case 13:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 3;
                        }
                    case 14:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 6;
                        }
                    case 15:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 9;
                        }
                    case 17:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 10;
                        }
                    case 16:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 11;
                        }

                        $custom_elem_body = [
                            "goodsID" => $temp_msg_type == 10 ? $v->docInfo->content : (string)$v->docInfo->obj_id,
                            "cover_pic" => $v->docInfo->cover_img,
                            "titleName" => $v->docInfo->content,
                            "subtitle" => $v->docInfo->subtitle,
                            "type" => (string)$temp_msg_type,
                        ];

                        $custom_elem_body = json_encode($custom_elem_body);
                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMCustomElem", // 自定义,不成功
                            "MsgContent" => [
                                "Data" => $custom_elem_body,
                                "Desc" => '',
                                'Ext' => '',
                                'Sound' => '',
                            ]
                        ];
                        $post_data_array[] = $temp_post_data;
                        break;
                    case 21://音频
                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMSoundElem",//音频
                            "MsgContent" => [
                                "Url" => $v->docInfo->file_url,
                                "Size" => $v->docInfo->file_size,
                                "Second" => $v->docInfo->second,
                                "Download_Flag" => 2
                            ]
                        ];
                        $post_data_array[] = $temp_post_data;
                        break;
                    case 22://视频
                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMVideoFileElem",//视频
                            "MsgContent" => [
                                "VideoUrl" => $v->docInfo->file_url,
//                                "VideoUUID" => $v->docInfo->file_md5,
                                "VideoUUID" => $this->getMsgRandom(),
                                "VideoSize" => $v->docInfo->file_size,
                                "VideoSecond" => $v->docInfo->second,
                                "VideoFormat" => $v->docInfo->format,
                                "VideoDownloadFlag" => 2,
                                "ThumbUrl" => $v->docInfo->cover_img,
//                                "ThumbUUID" => $v->docInfo->img_md5,
                                "ThumbUUID" => $this->getMsgRandom(),
                                "ThumbSize" => $v->docInfo->img_size,
                                "ThumbWidth" => $v->docInfo->img_width,
                                "ThumbHeight" => $v->docInfo->img_height,
                                "ThumbFormat" => $v->docInfo->img_format,
                                "ThumbDownloadFlag" => 2
                            ]
                        ];
                        $post_data_array[] = $temp_post_data;
                        break;
                    case 23:
                        //图片
                        $file_url = explode(';', $v->docInfo->file_url);
                        $file_url = array_filter($file_url);
                        foreach ($file_url as $fuv) {
                            $temp_post_data['MsgBody'] = [];
                            $fuv = explode(',', $fuv);
                            $temp_post_data['MsgBody'][] = [
                                "MsgType" => "TIMImageElem",
                                "MsgContent" => [
//                                    "UUID" => $fuv[4],
                                    "UUID" => $this->getMsgRandom(),
                                    "ImageFormat" => 1,
                                    "ImageInfoArray" => [
                                        [
                                            "Type" => 1,
                                            "Size" => (int)$fuv[1],
                                            "Width" => (int)$fuv[2],
                                            "Height" => (int)$fuv[3],
                                            "URL" => $fuv[0],
                                        ],
                                        [
                                            "Type" => 2,
                                            "Size" => (int)$fuv[1],
                                            "Width" => 0,
                                            "Height" => 0,
                                            "URL" => $fuv[0],
                                        ],
                                        [
                                            "Type" => 3,
                                            "Size" => $fuv[1],
                                            "Width" => 0,
                                            "Height" => 0,
                                            "URL" => $fuv[0],
                                        ],
                                    ]
                                ]
                            ];
                            $post_data_array[] = $temp_post_data;
                        }
                        break;
                    case 24:
                        //文件
                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMFileElem",
                            "MsgContent" => [
                                "Url" => $v->docInfo->file_url,
                                "FileSize" => $v->docInfo->file_size,
                                "FileName" => $v->docInfo->content,
                                "Download_Flag" => 2,
                            ]
                        ];
                        $post_data_array[] = $temp_post_data;
                        break;
                    case 31:
                        //文本
                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMTextElem",
                            "MsgContent" => [
                                "Text" => $v->docInfo->content,
                            ]
                        ];
                        $post_data_array[] = $temp_post_data;
                        break;
                }

            }
        }

        if (empty($post_data_array)) {
            return ['code' => true, 'msg' => '没有任务'];
        }

        ImDocSendJob::whereIn('id', $job_id_list)->update([
            'is_done' => 2
        ]);

        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");

        $res_list = [];
        foreach ($post_data_array as $v) {
            $res = ImClient::curlPost($url, json_encode($v));
            $res_list[] = json_decode($res, true);
//            sleep(1);
        }

        ImDocSendJob::whereIn('id', $job_id_list)->update([
            'is_done' => 3,
            'success_at' => date('Y-m-d H:i:s')
        ]);

        return true;
//        return ['code' => true, 'msg' => '成功'];
    }
}
