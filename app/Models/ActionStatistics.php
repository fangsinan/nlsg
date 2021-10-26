<?php

namespace App\Models;



class ActionStatistics extends Base
{

    protected $table = 'nlsg_action_statistics';

    protected  $fillable = ['action_id','user_id','os_type',];



    public static function actionAdd($action=0,$uid=0,$os_type=0){
        if(empty($uid) || empty($action)){
            return "";
        }

        ActionStatistics::insert([
            'action_id' => $action,
            'user_id' => $uid,
            'os_type' => $os_type,
        ]);
        return ;
    }

}
