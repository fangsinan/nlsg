<?php

namespace App\Models;


class LiveWorks extends Base
{
    protected $table = 'nlsg_live_works';

    public function getLiveWorks($live_id=0, $pos=1, $limit = 10)
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
                    $lists = Column::select('id', 'title', 'subtitle', 'original_price', 'price', 'cover_pic')
                        ->where('id', $v['rid'])
                        ->first();
                    $lists->type = 1;
                    $data[]  = $lists;
                }
            }
        }
        return $data ?? [];
    }
}
