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
        $lists= Wiki::select('id','name','intro','content','cover','view_num','like_num', 'comment_num','collection_num')
            ->whereIn('id',$ids)
            ->where('status',1)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        if ($lists){
            foreach ($lists as &$v) {
                $v['content'] =  strip_tags($v['content']);
            }
        }
        return $lists;
    }

    public function category()
    {
        return $this->hasMany(WikiCategory::class, 'category_id', 'id');
    }
    public  function  reward()
    {
        return $this->hasMany(Order::class, 'relation_id', 'id');
    }

    public static  function  search($keywords)
    {
        if (!$keywords){
            return false;
        }
        $res= Wiki::select('id','name','content','cover','view_num','like_num', 'comment_num')
            ->where('name','LIKE',"%$keywords%")
            ->where('status',1)
            ->orderBy('created_at','desc')
            ->get();

        return ['res' => $res, 'count'=> $res->count() ];
    }



}
