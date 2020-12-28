<?php


namespace App\Models;


class VipWorksList extends Base
{
    protected $table = 'nlsg_vip_works_list';

    public function getList($flag = 1, $category_id = 0)
    {
        $data = $this->getListFromDB();
        if ($flag === 1) {
            return array_slice($data['list'], 0, 6);
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

    public function getListFromDB()
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
                        $category_res[] = $temp_category_res;
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
            } else {
                continue;
            }

            $temp_res['user_info'] = User::getTeacherInfo($temp_res['user_id']);
            $res[] = $temp_res;
        }

        return ['list' => $res, 'category' => $category_res];
    }

    public function column()
    {
        return $this->hasOne(Column::class, 'id', 'works_id')
            ->select(['id', 'name as title', 'subtitle', 'cover_pic as cover_img',
                'details_pic as detail_img', 'column_type', 'price', 'user_id']);
    }

    public function works()
    {
        return $this->hasOne(Works::class, 'id', 'works_id')
            ->select(['id', 'title', 'subtitle', 'cover_img', 'detail_img', 'type', 'price', 'user_id']);
    }
}
