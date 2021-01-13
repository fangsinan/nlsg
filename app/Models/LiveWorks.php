<?php

namespace App\Models;


class LiveWorks extends Base
{
    protected $table = 'nlsg_live_works';

    public function getLiveWorks($live_id = 0, $pos = 1, $limit = 10)
    {
        $recommend = LiveWorks::select('id', 'rid', 'type')
            ->where('status', 1)
            ->where('pos', $pos)
            ->where('live_id', $live_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
        if ($recommend) {
//            dd($recommend);
            $data = [];
            foreach ($recommend as &$v) {
                if ($v['type'] == 1) {
                    $lists = Column::select('id', 'name as title', 'subtitle', 'original_price', 'price', 'cover_pic as cover_img')
                        ->where('id', $v['rid'])
                        ->where('type', 2)
                        ->where('status', 1)
                        ->first();
                    $lists->type = 1;
                } elseif ($v['type'] == 2) {
                    $lists = Works::select('id', 'title','subtitle', 'cover_img','original_price','price')
                        ->where('id', $v['rid'])
                        ->where('status', 4)
                        ->first();
                    $lists->type = 2;
                } elseif ($v['type'] ==3){
                    $lists = MallGoods::select('id', 'name as title','subtitle', 'picture as cover_img', 'original_price', 'price')
                        ->where('id', $v['rid'])
                        ->first();
                    $lists->type = 3;
                } elseif ($v['type'] ==4){
                    $lists = [
                        'title' => '幸福360会员',
                        'price' => 360.00,
                        'cover_img' => 'nlsg/works/20201215165707565448.png',
                        'type' => 4
                    ];
                } elseif ($v['type'] ==5 ){
                    $lists = OfflineProducts::select('id','title','subtitle','cover_img', 'total_price as original_price','price')
                        ->where('id', $v['rid'])
                        ->first();
                    $lists->type = 5;
                    if($live_id == 0){  //临时改动 客户端直播首页不展示 01-12 22:15:00
                        $lists = [];
                    }

                }
                if($lists)
                    $data[] = $lists;

            }
        }
        return $data ?? [];
    }
}
