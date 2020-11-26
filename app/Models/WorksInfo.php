<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class WorksInfo extends Base
{
    protected $table = 'nlsg_works_info';
    public $timestamps = false;


    public function getDateFormat()
    {
        return time();
    }

    // $type  1 单课程  2 多课程
    public function getInfo($works_id, $is_sub = 0, $user_id = 0, $type = 1, $order = 'asc', $page_per_page = 50, $page = 0, $size = 0)
    {
        $where = ['status' => 4];
        if ($type == 1) {
            $where['pid'] = $works_id;
        } else if ($type == 2) {
            $where['outline_id'] = $works_id;
        }
        $query = WorksInfo::select([
            'id', 'type', 'title', 'section', 'introduce', 'url', 'callback_url1', 'callback_url1', 'callback_url2',
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
            if ($is_sub == 1 || $val['free_trial'] == 1) {
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
        $works_id = $params['works_id'] ?? 0;
        $works_info_id = $params['works_info_id'] ?? 0;
        $ob = $params['ob'] ?? 'desc';
        if (empty($works_id) || empty($works_info_id)) {
            return ['code' => false, 'msg' => '课程不存在'];
        }
        $query = self::where('pid', '=', $works_id)
            ->select(['id as works_info_id', 'pid as works_id', 'title', 'duration', 'free_trial', 'url',
                'introduce', 'section', 'type', 'view_num', 'callback_url1', 'callback_url2', 'callback_url3']);

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
        }

        if ($info_key == -1) {
            return ['code' => false, 'msg' => '章节不存在'];
        }
        $info_key = $info_key + count($info_list);

        $info_list = array_merge($info_list, $info_list, $info_list);


        $works_info = DB::table('nlsg_works as w')
            ->leftJoin('nlsg_subscribe as s', function ($join) use ($user) {
                $join->on('s.relation_id', '=', 'w.id')
                    ->whereRaw('s.user_id = ' . $user['id'])
                    ->where('s.type', '=', 2)
                    ->where('s.status', '=', 1)
                    ->where('s.is_del', '=', 0);
            })
            ->where('w.id', '=', $works_id)
            ->select(['w.id', 'w.price', 'w.original_price', 'w.is_pay', 'w.type', 'w.is_free', 'w.status',
                DB::raw('if(s.id > 0,1,0) as is_sub')])
            ->first();

        $is_show_url = true;
        if ($works_info->is_free == 0 && $works_info->is_sub == 0) {
            $is_show_url = false;
        }

        $list['previous'] = $this->three2one($info_list[$info_key - 1], $is_show_url);
        $list['current'] = $this->three2one($info_list[$info_key], $is_show_url);
        $list['next'] = $this->three2one($info_list[$info_key + 1], $is_show_url);

        $user_info = [
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

}
