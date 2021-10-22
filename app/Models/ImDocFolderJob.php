<?php

namespace App\Models;

class ImDocFolderJob extends Base
{
    protected $table = 'nlsg_im_doc_folder_job';

    public function jobInfo()
    {
        return $this->hasMany('App\Models\ImDocFolderJobInfo', 'job_id', 'id')
            ->where('status', '<>', 3);
    }

    public function groupInfo()
    {
        return $this->hasOne('App\Models\ImGroup', 'id', 'group_id');
    }

    public function folderInfo()
    {
        return $this->hasOne('App\Models\ImDocFolder', 'id', 'folder_id');
    }
}
