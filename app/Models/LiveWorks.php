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
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
        if ($recommend) {
            $data = [];
            foreach ($recommend as &$v) {
                if ($v['type'] == 1) {
                    $lists = Column::select('id', 'name', 'subtitle', 'original_price', 'price', 'cover_pic')
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
                    $lists = OfflineProducts::select('id', 'title','subtitle', 'cover_img', 'total_price', 'price')
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
                }
                $data[] = $lists;
            }
        }
        return $data ?? [];
    }
}
