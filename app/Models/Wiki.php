<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wiki extends Model
{
    protected $table = 'nlsg_wiki';


    public function category()
    {
        return $this->hasMany(WikiCategory::class, 'category_id', 'id');
    }
}
