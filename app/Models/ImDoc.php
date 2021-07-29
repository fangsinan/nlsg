<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ImDoc extends Base
{
    protected $table = 'nlsg_im_doc';

    public function mediaInfo(){
        return $this->hasOne(ImMedia::class,'id','media_id');
    }
}
