<?php

namespace App\Models;



class ImSendAll extends Base
{

    protected $table = 'nlsg_im_send_all';

    protected $fillable = ['from_account', 'to_account', 'group_id','type','msg_id'];





}
