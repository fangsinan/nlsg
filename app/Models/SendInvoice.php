<?php


namespace App\Models;


class SendInvoice extends Base
{
    protected $table = 'nlsg_send_invoice';

    protected $fillable = ['user_id', 'express', 'express_num', 'img', 'status',];





}