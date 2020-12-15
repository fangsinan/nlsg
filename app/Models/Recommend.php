<?php

namespace App\Models;

use App\Models\Lists;
use App\Models\Works;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Wiki;
use App\Models\Column;

class Recommend extends Base
{
    protected $table = 'nlsg_recommend';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'relation_id', 'position', 'type', 'sort'
    ];

    public function getIndexRecommend($type = 1, $position = '1', $limit = 5, $row = 1)
    {
        if (!$type) {
            return false;
        }
        $ids = Recommend::where('position', $position)
            ->where('type', $type)
            ->pluck('relation_id');

        switch ($type) {
            case 1:
                $model = new Column();
                $result = $model->getIndexColumn($ids);
                break;
            case 2:
                $model = new Works();
                $result = $model->getIndexWorks($ids);
                break;
            case 3:
                $model = new Column();
                $result = $model->getIndexColumn($ids);
                break;
            case 4:
                $model = new Lists();
                $result = $model->getIndexListWorks($ids, 3);
                break;
            case 5:
                $model = new Wiki();
                $result = $model->getIndexWiki($ids);
                break;
            case 7:
                $model = new Live();
                $result = $model->getIndexLive($ids);
                break;
            case 8:
                $model = new MallGoods();
                $result = $model->getIndexGoods($ids);
                break;
            case 9:
                //听书
                $model = new Works();
                $result = $model->getIndexWorks($ids, 1);
                break;
            case 10:
                $model = new Lists();
                $result = $model->getIndexListCourse($ids, 1);
                break;

        }
        return $result;
    }


    public function getEditorWorks($uid = false)
    {
        $lists = Recommend::select('id', 'relation_id', 'relation_type', 'reason')
            ->where('position', 1)
            ->where('type', 12)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
        if ($lists) {
            foreach ($lists as $k => $v) {
                if ($v['relation_type'] == 1 || $v['relation_type'] == 2) {
                    if ($uid) {
                        $lists[$k]['is_sub'] = Subscribe::isSubscribe($uid, $v['relation_id'], 2);
                    }
                    $lists[$k]['works'] = Works::with([
                        'user' => function ($query) {
                            $query->select('id', 'nickname', 'headimg');
                        }])
                        ->select(['id', 'user_id', 'is_free', 'title', 'subtitle', 'cover_img', 'price', 'chapter_num', 'subscribe_num'])
                        ->where('id', $v['relation_id'])
                        ->where('status', 4)
                        ->first();

                } elseif ($v['relation_type'] == 3 || $v['relation_type'] == 4) {
                    if ($uid) {
                        $lists[$k]['is_sub'] = Subscribe::isSubscribe($uid, $v['relation_id'], 1);
                    }
                    $lists[$k]['works'] = Column::with([
                        'user' => function ($query) {
                            $query->select('id', 'nickname', 'headimg');
                        }])
                        ->select(['id', 'user_id', 'is_free', 'name', 'title', 'subtitle', 'cover_pic', 'price'])
                        ->where('id', $v['relation_id'])
                        ->where('status', 1)
                        ->first();
                }
                if (empty($lists[$k]['works'])) {
                    unset($lists[$k]);
                }

            }
            $lists = array_values($lists);
        }

        return $lists;

    }

    public function works()
    {
        return $this->belongsTo('App\Models\Works', 'relation_id', 'id');
    }

    public function goods()
    {
        return $this->belongsTo('App\Models\MallGoods', 'relation_id', 'id');
    }

    public function wiki()
    {
        return $this->belongsTo('App\Models\Wiki', 'relation_id', 'id');
    }


}
