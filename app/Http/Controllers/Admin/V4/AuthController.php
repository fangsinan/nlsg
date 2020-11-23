<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\Controller;
use Mews\Captcha\Captcha;

class AuthController extends Controller
{
    public function captcha(Captcha $captcha)
    {
        $time_out = time() + Config('captcha.default.expire');
        $data = $captcha->create('default', true);
        $data['expire'] = $time_out;
        return $this->getRes($data);
    }



}
