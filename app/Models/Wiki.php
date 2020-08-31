<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wiki extends Model
{
    protected $table = 'nlsg_wiki';

    /**
     * 首页百科推荐
     * @param $ids
     * @return bool
     */
    public function  getIndexWiki($ids)
    {
        if (!$ids){
            return false;
        }
        $lists= Wiki::select('id','name','content','cover','view_num','like_num', 'comment_num')
            ->whereIn('id',$ids)
            ->where('status',1)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        return $lists;
    }

    public function category()
    {
        return $this->hasMany(WikiCategory::class, 'category_id', 'id');
    }


    public function  search($keywords)
    {
        if (!$keywords){
            return false;
        }
        $res= Wiki::select('id','name','content','cover','view_num','like_num', 'comment_num')
            ->where('name',$keywords,'like')
            ->where('status',1)
            ->orderBy('created_at','desc')
            ->get();

        return ['res' => $res, 'count'=> $res->count() ];
    }



}
