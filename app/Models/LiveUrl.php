<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class LiveUrl extends Model
{
    protected $table = 'nlsg_live_url';

    static function search($keywords)
    {
        $res = LiveUrl::select('id', 'name', 'describe', 'url', 'image', 'img', 'user_id')
            ->where('is_del', 0)
//            ->where(function ($query) use ($keywords) {
//                $query->orWhere('name', 'LIKE', "%$keywords%");
//            })
            ->get();

        return ['res' => $res, 'count' => $res->count()];
    }
}
