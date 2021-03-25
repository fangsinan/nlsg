<?php

namespace App\Http\Controllers\Live\V4;
use App\Http\Controllers\ControllerBackend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends ControllerBackend
{
    public  function  index()
    {
        echo '直播后台首页';
    }
}
