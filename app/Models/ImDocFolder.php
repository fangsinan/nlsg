<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ImDocFolder extends Base
{
    protected $table = 'nlsg_im_doc_folder';

    public function docList()
    {
        return $this->hasMany(ImDocFolderBind::class, 'folder_id', 'id')
            ->where('status','=',1)
            ->orderBy('sort')
            ->select(['id','folder_id','doc_id']);
    }
}
