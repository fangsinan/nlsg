<?php
/**
 * Created by PhpStorm.
 * User: nlsg2017
 * Date: 2019/6/13
 * Time: 1:59 PM
 */

namespace App\Models;


//require dirname(dirname(__DIR__)) . '/vendor/phpqrcode/qrlib.php';
require_once(base_path() . '/vendor/phpqrcode/qrlib.php');

class CreateQRcode
{
    /*
        * 生成二维码
        * value  值
        * path   地址
        * name   图片名称
        * logo   会员头像
        * level  容错率
        * size   尺寸
        * margin 白边距离
        */
    public static function Create($value, $path, $name, $logo, $level = 'L', $size = 10, $margin = 2)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file = $path . $name;
        if (!file_exists($file)) {
            \QRcode::png($value, $file, $level, $size, $margin);
            if (!file_exists($file)) {
                return false;
            }
        }

        $QR = $file;//已经生成的原始二维码图


        if ($logo !== FALSE) {

            $QR = imagecreatefromstring(file_get_contents($QR));

            $logo = imagecreatefromstring(file_get_contents($logo));

            $QR_width = imagesx($QR);//二维码图片宽度

            $QR_height = imagesy($QR);//二维码图片高度

            $logo_width = imagesx($logo);//logo图片宽度

            $logo_height = imagesy($logo);//logo图片高度

            $logo_qr_width = $QR_width / 5;

            $scale = $logo_width / $logo_qr_width;

            $logo_qr_height = $logo_height / $scale;

            $from_width = ($QR_width - $logo_qr_width) / 2;

            //重新组合图片并调整大小

            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        }
        //输出图片
        imagepng($QR, $file);

        return true;

    }
}
