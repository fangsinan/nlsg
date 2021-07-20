<?php


namespace App\Models;


class ImDocSendJob extends Base
{
    protected $table = 'nlsg_im_doc_send_job';


    public function docInfo()
    {
        return $this->hasOne(ImDoc::class, 'id', 'doc_id')
            ->select([
                'id', 'type', 'type_info', 'obj_id', 'cover_img', 'content',
                'subtitle', 'file_url', 'status', 'second', 'format','file_size',
                'img_size','img_width','img_height','img_format'
            ]);
    }

    public function jobInfo()
    {
        return $this->hasMany(ImDocSendJobInfo::class, 'job_id', 'id')
            ->select(['id', 'job_id', 'send_obj_type', 'send_obj_id']);
    }

}
