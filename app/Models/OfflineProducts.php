<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineProducts extends Base
{
    protected $table = 'nlsg_offline_products';


    static function search($keywords)
    {
        $res = OfflineProducts::select('id', 'title', 'subtitle', 'total_price', 'price', 'cover_img')
            ->where('is_del', 0)
            ->where(function ($query) use ($keywords) {
                $query->orWhere('title', 'LIKE', "%$keywords%");
                $query->orWhere('subtitle', 'LIKE', "%$keywords%");
            })->get();

        return ['res' => $res, 'count' => $res->count()];
    }
}
