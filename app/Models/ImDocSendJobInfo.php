<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ImDocSendJobInfo extends Base
{
    protected $table = 'nlsg_im_doc_send_job_info';

    public function groupInfo()
    {
        return $this->hasOne(ImGroup::class, 'id', 'send_obj_id')
            ->select();
    }
}
