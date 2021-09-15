<?php


namespace App\Servers;


use App\Models\CacheTools;
use App\Models\ConfigModel;
use App\Models\ImBeatWord;
use App\Models\ImDoc;
use App\Models\ImDocSendJob;
use App\Models\ImDocSendJobInfo;
use App\Models\ImDocSendJobLog;
use App\Models\ImGroup;
use App\Models\ImGroupUser;
use App\Models\ImMedia;
use App\Models\Live;
use App\Models\LiveInfo;
use App\Models\MallCategory;
use App\Models\MallGoods;
use App\Models\OfflineProducts;
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
//        $for_app = $params['for_app'] ?? 0;
        $second = $params['second'] ?? 0;
        $format = $params['format'] ?? '';
        $img_size = $params['img_size'] ?? 0;
        $img_width = $params['img_width'] ?? 0;
        $img_height = $params['img_height'] ?? 0;
        $img_format = $params['img_format'] ?? 0;
        $file_md5 = $params['file_md5'] ?? '';
        $img_md5 = $params['img_md5'] ?? '';
        $media_id = $params['media_id'] ?? '';
        $url = $params['url'] ?? '';

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
                if ($type_info == 17) {
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
                if (empty($media_id)) {
                    return ['code' => false, 'msg' => '媒体id不能为空'];
                }

                $media_id_list = explode(',', $media_id);
                $media_id_list = array_unique($media_id_list);
                if ($params['type_info'] == 23) {
                    $media_id = $media_id_list[0];
                } else {
                    if (count($media_id_list) > 1) {
                        return ['code' => false, 'msg' => '媒体id错误'];
                    }
                }

                $media_info = ImMedia::where('media_id', '=', $media_id)->first();
                //$media_info_type = 20 + $media_info->type;

//                if (intval($type_info) != intval($media_info_type)) {
//                    return ['code' => false, 'msg' => '媒体类型不匹配'];
//                }

                if (empty($media_info)) {
                    return ['code' => false, 'msg' => '媒体信息为空,请重试.'];
                } else {
//                    $content = $media_info->file_name ?: $media_info->id;

                    $file_url = $media_info->url;
                    $format = $media_info->format ?: 'mp4';
                    $file_size = $media_info->size ?: 1119442;
                    $second = $media_info->second ?: 60;
                    $second = intval(round($second));

                    if ($params['type_info'] != 23) {
                        if (empty($content)) {
                            $content = $media_info->file_name ?: $media_info->id;
                        }
                        $file_md5 = $media_id;
                        $cover_img = $media_info->thumb_url ?: 'https://image.nlsgapp.com/nlsg/works/20210729141614776327.png';
                        $img_size = $media_info->thumb_size ?: 61440;
                        $img_width = $media_info->thumb_width ?: 953;
                        $img_height = $media_info->thumb_height ?: 535;
                        $img_format = $media_info->thumb_format ?: 'png';
                        $img_md5 = $media_id . '_' . 'c';
                    }
                }

                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }
                if (empty($file_url)) {
                    return ['code' => false, 'msg' => '附件地址不能为空'];
                }
                if (empty($format)) {
                    return ['code' => false, 'msg' => '格式后缀名不能为空format'];
                }
                if (empty($file_size)) {
                    return ['code' => false, 'msg' => 'file_size不能为空'];
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
                    $file_url = '';
                    foreach ($media_id_list as $milv) {
                        $temp_media_info = ImMedia::where('media_id', '=', $milv)->first();

                        if (empty($cover_img)) {
                            $cover_img = $temp_media_info->url;
                        }

                        $temp_file_url = $temp_media_info->url . ',' . $temp_media_info->size . ',' .
                            $temp_media_info->width . ',' . $temp_media_info->height . ',' . $milv . ';';
                        $file_url .= $temp_file_url;
                    }

                    //url,size,width,height,md5
//                    $file_url = explode(';', $file_url);
//                    foreach ($file_url as $fuv) {
//                        $fuv = explode(',', $fuv);
//                        if (count($fuv) != 5) {
//                            return ['code' => false, 'msg' => '图片参数格式错误'];
//                        }
//                    }
                }
                break;
            case 3:
                //31文本
                if (empty($content)) {
                    return ['code' => false, 'msg' => '内容不能为空'];
                }
                $beat_word = ImBeatWord::pluck('beat_word')->toArray();

                if (!empty($beat_word)) {
                    foreach ($beat_word as $bwv) {
                        $temp_pos = strpos($content, $bwv);
                        if ($temp_pos) {
                            return ['code' => false, 'msg' => '敏感词:' . $bwv];
                        }
                    }
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
        $docModel->file_md5 = $file_md5;
        $docModel->img_md5 = $img_md5;
        $docModel->media_id = $media_id;

//        DB::beginTransaction();

        $res = $docModel->save();
        if ($res === false) {
//            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        }

//        if ($for_app == 1) {
//            $jobModel = new ImDocSendJob();
//            $jobModel->doc_id = $docModel->id;
//            $jobModel->status = 2;
//            $jobModel->send_type = 3;
//            $jobModel->is_done = 4;
//            $jobModel->month = date('Y-m');
//            $jobModel->day = date('Y-m-d');
//            $job_res = $jobModel->save();
//            if ($job_res === false) {
//                DB::rollBack();
//                return ['code' => false, 'msg' => '失败'];
//            }
//        }

//        DB::commit();

        if (!empty($media_id_list)) {
            $doc_id = $docModel->id;
            ImMedia::whereIn('media_id', $media_id_list)->update([
                'doc_id' => $doc_id
            ]);
        }

        return ['code' => true, 'msg' => '成功'];

    }

    public function list($params)
    {
        $size = $params['size'] ?? 10;
        $query = ImDoc::query()
            ->with(['mediaInfo:id,type,media_id,url,doc_id,file_name,size,width,height,second,format,thumb_url,thumb_size,thumb_width,thumb_height,thumb_format']);

        if (!empty($params['id'] ?? 0)) {
            $query->where('id', '=', $params['id']);
        }
        if (!empty($params['type_info'] ?? 0)) {
            $query->where('type_info', '=', $params['type_info']);
        }
        if (!empty($params['content'] ?? '')) {
            $query->where('content', 'like', '%' . $params['content'] . '%');
        }

        $query->where('status', '=', 1)
            ->orderBy('id', 'desc')
            ->select([
                'id', 'type', 'type_info', 'obj_id', 'cover_img', 'content', 'file_url', 'subtitle', 'media_id'
            ]);

        return $query->paginate($size);
    }

    public function changeStatus($params, $user_id)
    {
        $id = $params['id'] ?? 0;
        $flag = $params['flag'] ?? '';
        $check = ImDoc::query()
            ->where('id', '=', $id)
            ->first();
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
        if (!is_array($info)) {
            $info = json_decode($info, true);
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
                //$temp_line = date('Y-m-d H:i:00', strtotime('+1 minute'));
//                $temp_line = date('Y-m-d H:i:00');
//                if ($send_at <= $temp_line) {
//                    return ['code' => false, 'msg' => '发送时间错误' . $temp_line];
//                }
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
            if (!is_array($v['list'])) {
                $v['list'] = explode(',', $v['list']);
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

        $logModel = new ImDocSendJobLog();
        $logModel->job_id = $jobModel->id;
        $logModel->user_id = $user_id;
        $logModel->record = $job_str;
        $log_res = $logModel->save();
        if ($log_res == false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败' . __LINE__];
        }

        DB::commit();
        return $this->sendGroupDocMsgJob($jobModel->id);
        //return ['code' => true, 'msg' => '成功'];
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
        $content = $params['content'] ?? '';

        if (!empty($send_obj_type)) {
            $query->whereHas('jobInfo', function ($q) use ($send_obj_type) {
                $q->where('send_obj_type', '=', $send_obj_type);
            });
        } else {
//            return ['code' => false, 'msg' => '目标类型参数无效'];
        }
        if (!empty($send_obj_id)) {

            $send_obj_id = ImGroup::getId($send_obj_id);
            $send_obj_id = $send_obj_id['id'];

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
        if (!empty($content)) {
            $query->whereHas('docInfo', function ($q) use ($content) {
                $q->where('content', 'like', '%' . $content . '%');
            });
        }
        if (!empty($is_done)) {
            $query->where('is_done', '=', $is_done);
        }


        $query->orderBy('is_done')
            ->orderBy('send_at', 'desc')
            ->orderBy('success_at', 'desc')
            ->orderBy('id', 'desc')
            ->select([
                'id', 'doc_id', 'created_at', 'send_at', 'status', 'send_type', 'is_done', 'success_at'
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
                ->orderBy('send_at', 'desc')
                ->orderBy('is_done', 'asc')
                ->orderBy('success_at', 'desc')
                ->orderBy('id', 'desc')
                ->select([
                    'id', 'doc_id', 'created_at', 'status', 'send_type',
                    'is_done', 'success_at', 'send_at',
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
//                if (in_array($jobModel->is_done, [2, 3])) {
//                    return ['code' => false, 'msg' => '发送中和已完成的任务无法编辑'];
//                }

//                if ($jobModel->user_id != $user_id) {
//                    return ['code' => false, 'msg' => '任务只有创建人自己能编辑'];
//                }
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
                case 'send':
                    if ($jobModel->is_done > 1){
                        return ['code' => false, 'msg' => '任务已定时发送'];
                    }
                    $now = time();
                    $jobModel->user_id = $user_id;
                    $jobModel->status = 1;
                    $jobModel->send_type = 1;
                    $jobModel->is_done = 2;
                    $jobModel->send_at = date('Y-m-d H:i:s', $now);
                    $jobModel->month = date('Y-m', $now);
                    $jobModel->day = date('Y-m-d', $now);
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

            $logModel = new ImDocSendJobLog();
            $logModel->job_id = $jobModel->id;
            $logModel->user_id = $user_id;
            $logModel->record = $job_str;
            $log_res = $logModel->save();
            if ($log_res == false) {
                DB::rollBack();
                return ['code' => false, 'msg' => '失败' . __LINE__];
            }

            DB::commit();
            if ($flag == 'send'){
                return $this->sendGroupDocMsgJob($jobModel->id);
            }
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
                }else{
                    $cate_id_arr = [$cate_data['id']];
                }
                $where = [
                    'works.status' => 4,
                    'works.type' => 2,
                    'works.is_audio_book' => 0
                ];

                $relationObj = new WorksCategoryRelation();
                $worksObj = new Works();
                $query = DB::table($relationObj->getTable(), ' relation')
                    ->leftJoin($worksObj->getTable() . ' as works',
                        'works.id', '=', 'relation.work_id')
                    ->leftJoin('nlsg_user as u',
                        'works.user_id', '=', 'u.id')
                    ->select(['works.id', 'works.type', 'works.title', 'works.cover_img', 'works.status',
                        'works.price', 'works.subtitle', 'works.title as doc_content', 'is_audio_book',
                        DB::raw('if(is_audio_book=1,19,12) as doc_type_info'),
                        DB::raw('1 as doc_type'),
                        'relation.category_id', 'works.is_end', 'u.nickname']);

                if ($category_id != 0) {
                    $query->whereIn('relation.category_id', $cate_id_arr);
                }
//                DB::connection()->enableQueryLog();
                $lists = $query->where($where)
                    ->orderBy('works.created_at', 'desc')
                    ->groupBy('works.id')
                    ->paginate($size);
//                dd(DB::getQueryLog());
                break;
            case 2:
                $lists = DB::table('nlsg_column as col')
                    ->join('nlsg_user as u', 'col.user_id', '=', 'u.id')
                    ->where('col.type', '=', 2)
                    ->where('col.status', '=', 1)
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
                $query = MallGoods::query()->where('status','=',2);
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
                    ->where('status','=',4)
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
            case 7:
                $lists = OfflineProducts::query()
                    ->where('is_show', '=', 1)
                    ->where('is_del', '=', 0)
                    ->select([
                        'id', 'title', 'subtitle', 'cover_img',
                        DB::raw('title as doc_content'),
                        DB::raw('1 as doc_type'),
                        DB::raw('18 as doc_type_info'),
                        'price',
                    ])
                    ->paginate($size);

        }
        return $lists ?? [];
    }

    public function getCategory()
    {
        $cache_key_name = 'works_category_list';
        $expire_num = CacheTools::getExpire('goods_category_list');
        $works_category = Cache::get($cache_key_name);
        if (empty($works_category)) {
//            $works_category = WorksCategory::select('id', 'name', 'pid', 'level', 'sort')
//                ->where(['status' => 1,])
//                ->orderBy('sort', 'asc')
//                ->get()
//                ->toArray();

            $works_category = DB::table('nlsg_works_category as wc')
                ->join('nlsg_works_category_relation as wcr','wc.id','=','wcr.category_id')
                ->join('nlsg_works as w','w.id','=','wcr.work_id')
                ->where('wc.status','=',1)
                ->where('w.type','=',2)
                ->where('w.status','=',4)
                ->where('w.is_audio_book','=',0)
                ->groupBy('wc.id')
                ->select(['wc.id','wc.name','wc.pid','wc.level','wc.sort'])
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
                    [
                        'id' => '100004',
                        'type' => 7,
                        'name' => '线下课'
                    ],
                ]
            ]
        ];

    }

    public function getMsgRandom()
    {
        //$date = date('YmdHi');
        $date = date('is');
        $cache_key_name = 'msg_counter_' . $date;
        $expire_num = 60;
        $counter = Cache::get($cache_key_name);
        if ($counter < 1) {
            Cache::put($cache_key_name, 1, $expire_num);
        }
        $counter = Cache::increment($cache_key_name);
        return $date . $counter;
//        return intval($date . $counter);
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

    public function sendGroupDocMsgJob($id = 0)
    {
        $query = ImDocSendJob::query();

        if (!empty($id)) {
            $query->where('id', '=', $id);
        }else{
            $query->where('is_done', '=', 1);
        }

        $job_info = $query->where('status', '=', 1)
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
//dd($job_info->toArray());
        foreach ($job_info as $v) {
            $job_id_list[] = $v->id;
            foreach ($v->jobInfo as $vv) {
                $temp_post_data = [];
                if (empty($vv->groupInfo->group_id)) {
                    ImDocSendJob::query()->where('id', '=', $v->id)->update(['status' => 2]);
                    continue;
                }
                $temp_post_data['GroupId'] = $vv->groupInfo->group_id;
                $temp_post_data['From_Account'] = (string)$v->user_id;
                $temp_post_data['Random'] = $this->getMsgRandom();
                $temp_post_data['MsgBody'] = [];
                $temp_msg_type = 0;
                switch ((int)$v->docInfo->type_info) {
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
                    case 18:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 4;
                        }
                    case 19:
                        if (empty($temp_msg_type)) {
                            $temp_msg_type = 8;
                        }
                        //类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链 18线下课 19听书
                        $custom_elem_body = [
                            "goodsID" => $temp_msg_type == 10 ? $v->docInfo->subtitle : (string)$v->docInfo->obj_id,
//                            "goodsID" => (string)$v->docInfo->obj_id,
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
                    case 211://音频
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
                        $temp_media_id = $v->docInfo->media_id;
                        $temp_meida_info = ImMedia::query()->where('media_id', '=', $temp_media_id)->first();
                        if (!empty($temp_meida_info->second)) {
                            $temp_video_second = intval(round($temp_meida_info->second));
                        } else {
                            $temp_video_second = $v->docInfo->second;
                        }

                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMVideoFileElem",//视频
                            "MsgContent" => [
//                                "VideoUrl" => $v->docInfo->file_url,
                                "VideoUrl" => str_replace('https:', 'http:', $v->docInfo->file_url),
//                                "VideoUUID" => $v->docInfo->file_md5,
                                "VideoUUID" => $this->getMsgRandom(),
                                "videouuid" => $this->getMsgRandom(),
                                "VideoSize" => $v->docInfo->file_size,
                                "VideoSecond" => $temp_video_second,
                                "VideoFormat" => $v->docInfo->format,
                                "VideoDownloadFlag" => 2,
//                                "ThumbUrl" => $v->docInfo->cover_img,
                                "ThumbUrl" => str_replace('https:', 'http:', $v->docInfo->cover_img),
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
                            $temp_format = pathinfo($fuv[0])['extension'] ?? 'jpg';
                            $temp_format_num = 255;
                            switch (strtolower($temp_format)) {
                                case 'jpg':
                                    $temp_format_num = 1;
                                    break;
                                case 'gif':
                                    $temp_format_num = 2;
                                    break;
                                case 'png':
                                    $temp_format_num = 3;
                                    break;
                                case 'bmp':
                                    $temp_format_num = 4;
                                    break;
                            }
                            $temp_post_data['Random'] = $this->getMsgRandom();
                            $temp_post_data['MsgBody'] = [];
                            $fuv = explode(',', $fuv);
                            $temp_fuv_width = (int)$fuv[2];
                            $temp_fuv_height = (int)$fuv[3];
                            $temp_wh_str = '';
                            $img_url = $fuv[0];
                            $img_url = str_replace('https:', 'http:', $img_url);

                            if ($temp_fuv_height > 396 && $temp_fuv_width > 396) {
                                $temp_w_c = floor($temp_fuv_width / 198);
                                $temp_h_c = floor($temp_fuv_height / 198);

                                $temp_wh = $temp_h_c > $temp_w_c ? $temp_w_c : $temp_h_c;
                                $temp_wh = floor(100 / $temp_wh);
                                $temp_wh = $temp_wh > 50 ? 50 : $temp_wh;
                                $temp_wh_str = '?x-oss-process=image/resize,p_' . $temp_wh;
                            }

                            $temp_post_data['MsgBody'][] = [
                                "MsgType" => "TIMImageElem",
                                "MsgContent" => [
//                                    "UUID" => $fuv[4],
                                    "UUID" => 'ali_rest_image_' . $this->getMsgRandom() . '.' . $temp_format,
//                                    "UUID" => $this->getMsgRandom(),
                                    "ImageFormat" => $temp_format_num,
                                    "ImageInfoArray" => [
                                        [
                                            "Type" => 1,
                                            "Size" => (int)$fuv[1],
                                            "Width" => (int)$fuv[2],
                                            "Height" => (int)$fuv[3],
                                            "URL" => $img_url,
                                        ],
                                        [
                                            "Type" => 2,
                                            "Size" => (int)$fuv[1],
                                            "Width" => 0,
                                            "Height" => 0,
                                            "URL" => $img_url,
                                        ],
                                        [
                                            "Type" => 3,
                                            "Size" => (int)$fuv[1],
                                            "Width" => 0,
                                            "Height" => 0,
                                            "URL" => $img_url . $temp_wh_str,
                                        ],
                                    ]
                                ]
                            ];
                            $post_data_array[] = $temp_post_data;
                        }
                        break;
                    case 21:
                        //音频
                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMFileElem",
                            "MsgContent" => [
//                                "Url" => $v->docInfo->file_url,
                                "Url" => str_replace('https:', 'http:', $v->docInfo->file_url),
                                "FileSize" => $v->docInfo->file_size,
                                "FileName" => $v->docInfo->content,
                                "Download_Flag" => 2,
                            ]
                        ];
                        $post_data_array[] = $temp_post_data;
                        break;
                    case 24:
                        //文件
                        $temp_post_data['MsgBody'][] = [
                            "MsgType" => "TIMFileElem",
                            "MsgContent" => [
//                                "Url" => $v->docInfo->file_url,
                                "Url" => str_replace('https:', 'http:', $v->docInfo->file_url),
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
//dd([$post_data_array,json_encode($post_data_array)]);
        if (empty($post_data_array)) {
            return ['code' => true, 'msg' => '没有任务'];
        }

        ImDocSendJob::whereIn('id', $job_id_list)->update([
            'is_done' => 2
        ]);

        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/send_group_msg");

        $res_list = [];
        foreach ($post_data_array as $v) {
//            dd([$v,json_encode($v)]);
            $res = ImClient::curlPost($url, json_encode($v));
            $temp_res_list = [];
            $temp_res_list['id'] = substr($v['Random'], 22);
            $res_list[] = array_merge(json_decode($res, true), $temp_res_list);
            //sleep(1);
            if (empty($id)) {
                //usleep(500000);
                sleep(1);
            }
        }

        ImDocSendJob::whereIn('id', $job_id_list)->update([
            'is_done' => 3,
            'success_at' => date('Y-m-d H:i:s')
        ]);

        $res_list_data = [];

        $id_res_data = [];
        $id_res_data['res'] = true;
        $id_res_data['msg'] = 'ok';
        $id_res_data['num'] = 0;
        $beat_word = [];

        foreach ($res_list as $rlv) {
            $temp_res_list_data = [];
            $temp_res_list_data['job_id'] = $rlv['id'];
            $temp_res_list_data['user_id'] = 0;
            if ($rlv['ErrorCode'] <> 0) {
                $id_res_data['res'] = false;
                $id_res_data['msg'] = $rlv['ErrorInfo'];
                $id_res_data['num'] = $rlv['ErrorCode'];
            }
            if ($rlv['ErrorCode'] == 80001) {
                $str_1 = strpos($rlv['ErrorInfo'], 'beat word:');
                $str_2 = strpos($rlv['ErrorInfo'], '|requestid');
                $str = substr($rlv['ErrorInfo'], $str_1 + 10, $str_2 - $str_1 - 10);
                $beat_word[] = trim($str);
            }
            $temp_res_list_data['record'] = $rlv['ActionStatus'] . ':' . $rlv['ErrorCode'] . ':' . $rlv['ErrorInfo'];
            $res_list_data[] = $temp_res_list_data;
        }

        $logModel = new ImDocSendJobLog();
        $logModel->insert($res_list_data);

        if (!empty($beat_word)) {
            foreach ($beat_word as $bwv) {
                ImBeatWord::updateOrCreate(array('beat_word' => $bwv), array('times' => DB::raw('times+1')));
            }
        }

        if (empty($id)) {
            return true;
        } else {
            if ($id_res_data['res']) {
                return ['code' => true, 'msg' => '成功'];
            } else {
                return ['code' => false, 'msg' => $id_res_data['num'] . ':' . $id_res_data['msg']];
            }
        }
//        return ['code' => true, 'msg' => '成功'];
    }
}
