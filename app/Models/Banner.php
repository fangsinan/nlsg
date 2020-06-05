<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'nlsg_banner';

    /**
     * 首页Banner
     * @return mixed
     */
    public function getIndexBanner()
    {
        $lists = $this->select('id', 'pic','title','url')
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->take(5)
            ->get()
            ->toArray();
        return $lists;
    }
}
