<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wiki extends Model
{
    protected $table = 'nlsg_wiki';

    public function  getIndexWiki($ids)
    {
        if (!$ids){
            return false;
        }
        $lists= Wiki::select('id','name','content','cover','view_num','like_num', 'comment_num')
            ->whereIn('id',$ids)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        return $lists;
    }
}
