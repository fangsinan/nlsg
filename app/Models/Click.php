<?php


namespace App\Models;


class Click extends Base
{
    protected $table = 'nlsg_click';

    public function add($params,$user = [],$ip = ''){
        $model = new self();
        if (empty($params['type']??0) || !in_array($params['type'],[1,2,3,4])){
            return ['code'=>false,'msg'=>'参数错误'];
        }
        if (empty($params['cpid']??0)){
            return ['code'=>false,'msg'=>'参数错误'];
        }

        $model->type = $params['type'];
        $model->user_id = $user['id']??0;
        $model->cpid = $params['cpid'];
        $model->flag = $params['flag']??'';
        $model->ip = $ip;
    }
}
