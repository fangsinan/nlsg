<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * Description of LiveUserPrivilege
 *
 * @author wangxh
 */
class LiveUserPrivilege extends Model{
    protected $table = 'nlsg_live_user_privilege';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
