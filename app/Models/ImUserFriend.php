<?php

namespace App\Models;



class ImUserFriend extends Base
{

    protected $table = 'nlsg_im_user_friend';

    public function UserInfo(){
        return $this->hasOne(User::class,'id','to_account');
    }

    public function ImUser(){
        return $this->hasOne(ImUser::class,'tag_im_to_account','to_account');
    }

}
