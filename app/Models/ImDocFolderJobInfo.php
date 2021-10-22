<?php

namespace App\Models;

class ImDocFolderJobInfo extends Base
{
    protected $table = 'nlsg_im_doc_folder_job_info';

    public function docInfo()
    {
        return $this->hasOne('App\Models\ImDoc', 'id', 'doc_id');
    }

}
