<?php


namespace App\Models;


class ChannelCategoryBind extends Base
{

    protected $table = 'nlsg_channel_category_bind';

    public function categoryName()
    {
        return $this->hasOne(ChannelCategory::class, 'id', 'category_id')
            ->select(['id','id as category_id','name']);
    }

}
