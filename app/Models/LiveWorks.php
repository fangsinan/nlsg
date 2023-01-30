<?php

namespace App\Models;


use Illuminate\Support\Facades\Cache;

class LiveWorks extends Base
{
    protected $table = 'nlsg_live_works';

    public function getLiveWorks($live_id = 0, $pos = 1, $limit = 10)
    {
        $cache_live_name = 'live_live_works_'.$live_id.'_'.$pos;
        $data = Cache::get($cache_live_name);
        if (empty($data)) {
            $query = LiveWorks::query();
            if ($pos==2){
                $query->whereIn('type', [1, 2, 3, 4, 5]);
            } else {
                $query->whereIn('type', [1, 2, 3, 4]);
            }
            $recommend = $query->select('id', 'rid', 'type','status','sort')
                ->where('status', 1)
                ->where('pos', $pos)
                ->where('live_id', $live_id)
                ->orderBy('sort')
                ->limit($limit)
                ->get()
                ->toArray();

            if ($recommend) {
                //            dd($recommend);
                $data = [];
                foreach ($recommend as &$v) {
                    if ($v['type'] == 1) {
                        $lists = Column::select('id', 'name as title', 'subtitle', 'original_price', 'price',
                            'cover_pic as cover_img')
                            ->where('app_project_type','=',APP_PROJECT_TYPE)
                            ->where('id', $v['rid'])
                            ->where('type', 2)
                            ->where('status', 1)
                            ->first();
                        $lists->type = 1;
                    } elseif ($v['type'] == 2) {
                        $lists = Works::select('id', 'title', 'subtitle', 'cover_img', 'original_price', 'price')
                            ->where('id', $v['rid'])
                            ->where('status', 4)
                            ->where('app_project_type','=',APP_PROJECT_TYPE)
                            ->first();
                        $lists->type = 2;
                    } elseif ($v['type'] == 3) {
                        $lists = MallGoods::select('id', 'name as title', 'subtitle', 'picture as cover_img',
                            'original_price', 'price')
                            ->where('id', $v['rid'])
                            ->first();
                        $lists->type = 3;
                    } elseif ($v['type'] == 4) {
                        $lists = [
                            'title'     => '幸福360会员',
                            'price'     => 360.00,
                            'cover_img'     => '/live/recommend/360_xhc.png',
                            'cover_details' => '/live/recommend/360_tc.png',
                            'type'      => 4
                        ];
                    } elseif ($v['type'] == 5 && $pos ==2) {
                        $lists = OfflineProducts::select('id', 'title', 'subtitle', 'cover_img',
                            'image as cover_details', 'total_price as original_price', 'price')
                            ->where('id', $v['rid'])
                            ->where('app_project_type','=',APP_PROJECT_TYPE)
                            ->first();
                        $lists->type = 5;
                    }
                    $data[] = $lists;

                }
            }

            $expire_num = CacheTools::getExpire('live_live_works');
            Cache::put($cache_live_name, $data, $expire_num);
        }


        return $data ?? [];
    }
}
