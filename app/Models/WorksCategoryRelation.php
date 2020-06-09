<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class WorksCategoryRelation extends Model {

    protected $table = 'nlsg_works_category_relation';


    public function CategoryName()
    {
        return $this->belongsTo('App\Models\WorksCategory','category_id','id');
    }
}
