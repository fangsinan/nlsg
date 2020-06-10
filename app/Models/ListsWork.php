<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListsWork extends Model
{
    protected $table = 'nlsg_lists_work';

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

        $lists  = ListsWork::with(['lists'=>function($query){
            $query->select('id','title', 'subtitle','cover');
        }, 'works'=> function($query){
                $query->select('id','user_id','title', 'cover_img');
            }, 'works.user' =>function($query){
            $query->select('id','username','nick_name');
        }])->whereIn('lists_id',$ids)
            ->get()
            ->toArray();
        dd(array_values($lists));
        $arr = [];
        foreach ($lists as $v) {
            $arr[$v['lists_id']][] = $v;
        }
        dd($arr);
        return $arr;
    }

    public function lists()
    {
        return $this->belongsToMany('App\Models\Lists', 'nlsg_lists_work','lists_id', 'works_id');
    }

}
