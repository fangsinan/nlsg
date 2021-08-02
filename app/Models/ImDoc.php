<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ImDoc extends Base
{
    protected $table = 'nlsg_im_doc';

    public function mediaInfo(){
        return $this->hasMany(ImMedia::class,'doc_id','id');
    }
}
