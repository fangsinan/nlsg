<?php

namespace App\Models;

class ImDocFolderJobInfo extends Base
{
    protected $table = 'nlsg_im_doc_folder_job_info';

    public function docInfo()
    {
        return $this->hasOne(\App\Models\ImDoc::class, 'id', 'doc_id');
    }

    public function jobTop()
    {
        return $this->hasOne(\App\Models\ImDocFolderJob::class, 'id', 'job_id');
    }

}
