<?php


namespace App\Models;

class LiveBgp extends Base
{
    protected $table = 'nlsg_live_bgp';
    protected $fillable = [
        'title',
        'url',
        'color',
        'status',
        'created_at',
        'updated_at',
    ];
}
