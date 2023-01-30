<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListsWork extends Model
{
    protected $table = 'nlsg_lists_work';

    protected $fillable = [
        'lists_id', 'works_id', 'sort', 'state', 'type'
    ];

    /**
     * 首页听书推荐
     * @param $ids 相关作品id
     * @return bool
     */
    public function getIndexListenBook($ids)
    {
        if (!$ids) {
            return false;
        }

        $lists = ListsWork::with(['lists' => function ($query) {
            $query->select('id', 'title', 'subtitle', 'cover');
        }, 'works'                        => function ($query) {
            $query->select('id', 'user_id', 'title', 'cover_img');
        }, 'works.user'                   => function ($query) {
            $query->select('id', 'username', 'nick_name');
        }])->whereIn('lists_id', $ids)
                          ->where('app_project_type', '=', APP_PROJECT_TYPE)
                          ->get()
                          ->toArray();
        $arr   = [];
        foreach ($lists as $v) {
            $arr[$v['lists_id']][] = $v;
        }
        return $arr;
    }

    public function lists()
    {
        return $this->belongsToMany(
            'App\Models\Lists', 'nlsg_lists_work', 'lists_id', 'works_id'
        )->where('app_project_type', '=', APP_PROJECT_TYPE);
    }

    public function wiki()
    {
        return $this->belongsTo('App\Models\Wiki', 'works_id', 'id')
                    ->where('app_project_type', '=', APP_PROJECT_TYPE);
    }

    public function works()
    {
        return $this->belongsTo('App\Models\Works', 'works_id', 'id')
                    ->where('app_project_type', '=', APP_PROJECT_TYPE);
    }

    public function goods()
    {
        return $this->belongsTo('App\Models\MallGoods', 'works_id', 'id');
    }

}
