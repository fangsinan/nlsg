<?php


namespace App\Models;

use Illuminate\Support\Facades\Cache;

class ChannelWorksList extends Base
{
    protected $table = 'nlsg_channel_works_list';

    public static function getPrice($type, $id)
    {
        $price = self::where('works_id', '=', $id)->where('type', '=', $type)->first();
        if (empty($price)) {
            return 0;
        } else {
            return $price->price;
        }
    }

    public function cytxNew($params,$user){

    }

    public function getList($page = 1, $size = 10, $category_id = 0, $channel = 0, $user_id)
    {
        if (empty($channel)) {
            return [];
        }

        $cache_key_name = 'channel_works_list_' . $channel;
        $expire_num = CacheTools::getExpire('channel_works_list');
        $data = Cache::get($cache_key_name);
        if (empty($data)) {
            $data = $this->getListFromDB($channel);
            Cache::put($cache_key_name, $data, $expire_num);
        }

        $list = [];
        if (!empty($category_id)) {
            foreach ($data['list'] as $v) {
                if (in_array($category_id, $v['category_id'])) {
                    $list[] = $v;
                }
            }
        } else {
            $list = $data['list'];
        }

        return ['category' => $data['category'], 'list' => $list];
    }

    public function getListFromDB($channel = 0)
    {
        $list = self::where('status', '=', 1)
            ->where('channel_type', '=', $channel)
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
            $temp_res['works_id'] = $v['works_id'];
            $temp_res['works_type'] = $v['type'];
            $temp_res['price'] = $v['price'];
            if ($v['type'] == 1) {
                if (empty($v['column'])) {
                    continue;
                }
                $temp_res['title'] = $v['column']['title'];
                $temp_res['subtitle'] = $v['column']['subtitle'];
                //$temp_res['cover_img'] = $v['cover_img'];
                $temp_res['cover_img'] = $v['column']['cover_img'];
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
                //$temp_res['cover_img'] = $v['cover_img'];
                $temp_res['cover_img'] = $v['works']['cover_img'];
                $temp_res['detail_img'] = $v['works']['cover_img'];
                $temp_res['type'] = $v['works']['type'];
                $temp_res['column_type'] = 1;
                $temp_res['category_id'] = $temp_res_category_id;
                $temp_res['user_id'] = $v['works']['user_id'];
                $temp_res['view_num'] = $v['works']['view_num'];
            } else {
                continue;
            }

            if ($temp_res['view_num'] >= 10000) {
                $leftNumber = floor($temp_res['view_num'] / 10000);
                $rightNumber = round(($temp_res['view_num'] % 10000) / 10000, 2);
                $temp_res['view_num'] = floatval($leftNumber + $rightNumber) . '万';
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
                'details_pic as detail_img', 'column_type', 'price', 'user_id', 'view_num']);
    }

    public function works()
    {
        return $this->hasOne(Works::class, 'id', 'works_id')
            ->select(['id', 'title', 'subtitle', 'cover_img', 'detail_img', 'type', 'price', 'user_id', 'view_num']);
    }
}
