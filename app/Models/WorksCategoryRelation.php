<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;


class WorksCategoryRelation extends Base {

    protected $table = 'nlsg_works_category_relation';

    protected $fillable = [
       'work_id','category_id'
    ];

    public function categoryName()
    {
        return $this->belongsTo('App\Models\WorksCategory','category_id','id');
    }

    public function works()
    {
        return $this->belongsTo('App\Models\Works','work_id');
    }
}
