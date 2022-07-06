<?php

namespace App\Models;

class CampPrize extends Base
{
    protected $table = 'crm_camp_prize';

    protected $fillable = [
        'camp_id', 'type',  'title', 'status', 'cover_pic','week_id',
    ];

}
