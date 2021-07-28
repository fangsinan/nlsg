<?php

namespace App\Models;



class ImUserFriend extends Base
{

    protected $table = 'nlsg_im_user_friend';

    public function UserInfo(){
        return $this->hasOne(User::class,'id','from_account');
    }


}
