<?php


namespace App\Models;


use Illuminate\Support\Facades\Cache;

class VipWorksList extends Base
{
    protected $table = 'nlsg_vip_works_list';

    public function getList($flag = 1, $category_id = 0, $size = 0,$version=0)
    {
        $cache_key_name = 'vip_works_list_'.$version;
        $expire_num = CacheTools::getExpire('vip_works_list');
        $data = Cache::get($cache_key_name);
        if (empty($data)) {
            $data = $this->getListFromDB($version);
            Cache::put($cache_key_name, $data, $expire_num);
        }

        if ($flag === 1) {
            if (empty($size)) {
                return $data['list'];
            } else {
                return array_slice($data['list'], 0, 6);
            }

        } else {
            $list = [];
            if (empty($category_id)) {
                $list = $data['list'];
            } else {
                foreach ($data['list'] as $v) {
                    if (in_array($category_id, $v['category_id'])) {
                        $list[] = $v;
                    }
                }
            }
            $res['list'] = $list;
            $res['category'] = $data['category'];
            return $res;
        }
    }

    public function getListFromDB($version)
    {
        $list = VipWorksList::where('status', '=', 1)
            ->with([
                'column',
                'works',
                'works.categoryRelation', 'works.categoryRelation.categoryName'
            ])
            ->orderBy('rank', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->toArray();

        $res = [];
        $new_res = [];

        $category_res = [];

        foreach ($list as $v) {
            $temp_res = [];
            $temp_res['id'] = $v['works_id'];
            $temp_res['works_type'] = $v['type'];
            if ($v['type'] == 1) {
                if (empty($v['column'])) {
                    continue;
                }
                $temp_res['title'] = $v['column']['title'];
                $temp_res['subtitle'] = $v['column']['subtitle'];
                $temp_res['price'] = $v['column']['price'];
                $temp_res['cover_img'] = $v['cover_img'];
                $temp_res['detail_img'] = $v['column']['cover_img'];
                $temp_res['type'] = 1;
                $temp_res['column_type'] = $v['column']['column_type'];
                $temp_res['category_id'] = [0];
                $temp_res['user_id'] = $v['column']['user_id'];
                $temp_res['view_num'] = $v['column']['view_num'];

            } else if ($v['type'] == 2) {
                if (empty($v['works'])) {
                    continue;
                }
                $temp_res_category_id = [];
                foreach ($v['works']['category_relation'] as $cv) {
                    $temp_category_res = [];
                    $temp_category_res['id'] = $cv['category_name']['id'];
                    $temp_category_res['name'] = $cv['category_name']['name'];
                    if (!empty($temp_category_res['id']) && !empty($temp_category_res['name'])) {
                        $temp_res_category_id[] = $temp_category_res['id'];
                        if (!in_array($cv['category_name']['id'], array_column($category_res, 'id'))) {
                            $category_res[] = $temp_category_res;
                        }
                    }

                }
                $temp_res['title'] = $v['works']['title'];
                $temp_res['subtitle'] = $v['works']['subtitle'];
                $temp_res['price'] = $v['works']['price'];
                $temp_res['cover_img'] = $v['cover_img'];
                $temp_res['detail_img'] = $v['works']['cover_img'];
                $temp_res['type'] = $v['works']['type'];
                $temp_res['column_type'] = 1;
                $temp_res['category_id'] = $temp_res_category_id;
                $temp_res['user_id'] = $v['works']['user_id'];
                $temp_res['view_num'] = $v['works']['view_num'];
            } else {
                continue;
            }
            if($version == '5.0.0'){
                $new_res[$temp_res['user_id']]["user_info"] = User::getTeacherInfo($temp_res['user_id']);;
                $new_res[$temp_res['user_id']]["list"][] = $temp_res;
                $new_res[$temp_res['user_id']]["count"] = count($new_res[$temp_res['user_id']]["list"]);


            }else{
                $temp_res['user_info'] = User::getTeacherInfo($temp_res['user_id']);
                $res[] = $temp_res;
            }



        }

        if($version == '5.0.0'){
            $new_res = array_values($new_res);
            array_multisort(array_column($new_res,'count'),SORT_DESC,$new_res);
            return ['list' => $new_res, 'category' => $category_res];
        }
        return ['list' => $res, 'category' => $category_res,"new_list"=>$new_res];
    }

    public function column()
    {
        return $this->hasOne(Column::class, 'id', 'works_id')
            ->select(['id', 'name as title', 'subtitle', 'cover_pic as cover_img',
                'details_pic as detail_img', 'column_type', 'price', 'user_id','view_num']);
    }

    public function works()
    {
        return $this->hasOne(Works::class, 'id', 'works_id')
            ->select(['id', 'title', 'subtitle', 'cover_img', 'detail_img', 'type', 'price', 'user_id','view_num']);
    }
}
