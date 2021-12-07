<?php

namespace App\Models;



class RecommendConfig extends Base
{
    protected $table = 'nlsg_recommend_config';

    protected $fillable = [
        'title', 'icon_pic', 'show_position', 'jump_type','modular_type','is_show','sort',
    ];


}
