<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ImDocFolderBind extends Base
{
    protected $table = 'nlsg_im_doc_folder_bind';

    protected $fillable = [
        'folder_id','doc_id','created_at','updated_at','status','sort'
    ];

    public function docInfo()
    {
        return $this->hasOne(ImDoc::class, 'id', 'doc_id');
    }
}
