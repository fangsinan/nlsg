<?php

namespace App\Models;



class ImGroupUser extends Base
{

    protected $table = 'nlsg_im_group_user';

    protected $fillable = ['group_id', 'group_account', 'group_role','join_type',
        'operator_account','exit_type'];


}
