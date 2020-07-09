<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lists extends Model
{
    protected $table = 'nlsg_lists';

    /**
     * 首页听书推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexListenBook($ids)
    {
        if (!$ids){
            return false;
        }

        $lists  = Lists::select('id','title', 'subtitle','cover','num')
        ->with(['works'=> function($query){
            $query->select('user_id','title', 'cover_img');
        }, 'works.user'=>function($query){
            $query->select('id','nickname','headimg');
        }])->whereIn('id',$ids)
            ->get()
            ->toArray();
        return $lists;
    }

    public function works()
    {
        return $this->belongsToMany('App\Models\Works',
            'nlsg_lists_work','lists_id', 'works_id');
    }



    public function getIndexGoods($ids) {

        $lists = Lists::query()
            ->select('id', 'title', 'subtitle', 'cover')
            ->whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->toArray();
        return $lists;
    }


}
