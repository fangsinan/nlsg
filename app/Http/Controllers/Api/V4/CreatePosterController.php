<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\CreatePost;
use App\Models\CreateQRcode;
use App\Models\Live;
use App\Models\MallGoods;
use App\Models\MallSku;
use App\Models\MallTwitter;
use App\Models\User;
use App\Models\Works;
use App\Models\WorksInfo;
use Illuminate\Http\Request;

class CreatePosterController extends Controller
{
    public static  $Api_url = 'https://app.api.test.nlsgapp.com/';
    public static  $IMAGES_URL = 'https://image.nlsgapp.com/';


    /**
     * @api {get} /api/v4/create/create_poster   制作专属海报
     * @apiName create_poster
     * @apiVersion 1.0.0
     * @apiGroup create
     *
     * @apiParam {int} post_type  类型 post_type 2皇钻钻邀请卡 4会员  5精品课  7优品海报   8 专栏
     * @apiParam {int} relation_id  对应id
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
     *
     *
    {
    "code": 200,
    "msg": "成功",
    "data": [
    ]
    }
     */

    public function CreatePoster(Request $request)
    {
        $uid = $this->user['id']??0;
        $gid = $request->input('relation_id', 0);
        $post_type = $request->input('post_type', 0);


        $level = User::getLevel($uid);
        if ($level < 2) return $this->error(0,'用户身份不是推客');


        $save_path = base_path() . '/public/image/';//存储路径
        if (!file_exists($save_path)) {
            mkdir($save_path, 0777, true);
        }

        $source_name = '';
        switch ($post_type) {
            case 1://黑钻邀请卡
                $source_name = 'black_vip.png';
                break;
            case 2://皇钻钻邀请卡
                $source_name = 'yellow_vip.png';
                break;
            case 3://好书
                $source_name = 'haoshu@2x.png';
                break;
            case 4://会员海报
                $source_name = 'huiyuan@2x.png';
                break;
            case 5://精品课海报
                $source_name = 'jingpinke@2x.png';
                break;
            case 6://线下课海报
                $source_name = 'xianxiake@2x.png';
                break;
            case 7://优品海报
                $source_name = 'shangpin@2x.png';
                break;
            case 8://专栏
                $temp_get_gid = Column::find($gid);
                $gid          = $temp_get_gid['id'];
                $g_t_id       = $temp_get_gid['id'];
//                $g_t_id       = $temp_get_gid['user_id'];
                $source_name  = 'zhuanlan@2x.png';
                break;
            case 9://二维码
                $QR_url     = $this->getGetQRUrl(4, $gid, $uid);
                $temp_9_res = $this->createQRcode($QR_url, false, true, true);
                $src        = '';
                $url        = self::$Api_url.'temp_poster/' . $temp_9_res;
                return ['url' => $url, 'src' => $src];
            //                return $temp_9_res;
            case 20://优品海报
                $source_name = 'temp_qiancheng.png';
                break;
            case 21: //直播海报
                $source_name = 'zhibo.png';
                break;
            case 22: //免费送精品课(三八赠课活动)
                $source_name = 'poster_38_bg.png';
                break;
        }
        $source = storage_path() . '/app/public/PosterMaterial/' . $source_name;

        $init = [
            'path'   => $save_path,
            'source' => $source,
        ];

        $cp   = new CreatePost($init);

        if (empty($g_t_id)) {
            $draw = $this->getDraw($uid, $post_type, $gid, $level);
        } else {
            $draw = $this->getDraw($uid, $post_type, $gid, $level, $g_t_id);
        }
        $temp_del_path = $draw['QR']['path'];
        $res           = $cp::draw($draw);
        $return_qr_url = '';
        if (!empty($draw['QR']['path'])) {
            $temp_qr_new_url = str_replace(storage_path() . '/app/public/PosterMaterial/', '', $temp_del_path);
            $c_res           = copy($temp_del_path, $save_path . $temp_qr_new_url);

            unlink($temp_del_path);
        }
        $file_path = $save_path . $res;
        $res_data = false;
        if ($fp = fopen($file_path, "rb", 0)) {
            $src    = '';
            $domain   = self::$Api_url .'temp_poster/';
            $res_data = ['url' => $domain. $res, 'src' => $src, 'qr_url' => $domain .$temp_qr_new_url];
        }
        return $res_data;

    }

    //生成二维码
    public function createQRcode($value, $b64 = true, $online = true, $web = false) {
        $logo = storage_path() . '/app/public/PosterMaterial/qrcode_logo.png';
        if ($web) {
            //$path = EASYSWOOLE_ROOT . '/webroot/temp_poster/';
            $path = base_path() . '/public/image/';
        } else {
            $path = storage_path() . '/app/public/PosterMaterial/';
        }

        $name = 'qr_' . time() . rand(1, 999) . '.png';



        $QRcode = new CreateQRcode();
        $QRcode::Create($value, $path, $name, $logo, 'L', 10, 2, 2);

        if ($b64) {
            $file = $path . $name;
            if ($fp = fopen($file, "rb", 0)) {
                $gambar = fread($fp, filesize($file));
                fclose($fp);
                $base64 = chunk_split(base64_encode($gambar));
                unlink($file);
                return 'data:image/jpg/png/gif;base64,' . $base64;
            }
        } else {
            if ($web) {
                return $name;
            } else {
                return $path . $name;
            }
        }
    }

    //校验头像地址
    public function checkHeadImgUrl($url) {
        $url_head_img = rtrim(self::$IMAGES_URL, '/');

        if (strpos($url, 'http:') === 0 || strpos($url, 'https:') === 0) {
            $check_url = $url;
        } else {
            $check_url = $url_head_img . $url;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $check_url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code === 200) {
            return $check_url;
        } else {
            return false;
        }
    }

    //按类型生成坐标数组
    public function getDraw($uid, $type, $gid, $level, $g_t_id = 0) {
        //帽子&图标  2 推客 3黑钻 4皇钻
        $cap_img = '';
        $sign_img = '';
        switch (intval($level)) {
            case 2:
                $cap_img = '';
                $sign_img = storage_path() . '/app/public/PosterMaterial/' . 'tuike@2x.png';
                break;
            case 3:
                $cap_img = storage_path() . '/app/public/PosterMaterial/' . 'hei@2x.png';
                $sign_img = storage_path() . '/app/public/PosterMaterial/' . 'heizuan@2x.png';
                break;
            case 4:
                $cap_img = storage_path() . '/app/public/PosterMaterial/' . 'huang@2x.png';
                $sign_img = storage_path() . '/app/public/PosterMaterial/' . 'huangzuan@2x.png';
                break;
            case 5:
                $cap_img = storage_path() . '/app/public/PosterMaterial/' . 'huang@2x.png';
                $sign_img = storage_path() . '/app/public/PosterMaterial/' . 'fuwushang@2x.png';
                break;
        }

        $user_info = User::find($uid);
        //Tool::del_emoji($user_info['nick_name']);
        $font = storage_path() . '/app/public/PosterMaterial/SourceHanSansCN-Regular.otf'; //字体
        $nickname_font_size = 31;
        $img_url_share = self::$IMAGES_URL;

        $temp_headimg = $this->checkHeadImgUrl($user_info['headimg']);

        if ($temp_headimg === false) {
            switch (intval($user_info['level'])) {
                case 5:
                    $temp_headimg = $img_url_share . '/wechat/vip_morentouxiang.png';
                    break;
                case 4:
                    $temp_headimg = $img_url_share . '/wechat/vip_morentouxiang.png';
                    break;
                case 3:
                    $temp_headimg = $img_url_share . '/wechat/vip_morentouxiang_hei@2x.png';
                    break;
                default:
                    $temp_headimg = $img_url_share . '/wechat/head.png';
            }
        }
        $res = [];
        switch ($type) {
            case 3://好书
                $res['name'] = [
                    'type' => 'text',
                    'size' => 31,
                    'x' => 180,
                    'y' => 78,
                    'font' => $font,
                    'text' => $user_info['nick_name'],
                    'rgb' => '0,0,0',
                ];
                //身份帽子图标
                if (!empty($cap_img)) {
                    $res['cap'] = [
                        'type' => 'image',
                        'path' => $cap_img,
                        'dst_x' => 130,
                        'dst_y' => 20,
                        'src_w' => 34,
                        'src_h' => 32,
                        'corners' => 0,
                        'scaling' => ['w' => 34, 'h' => 32]
                    ];
                }
                //身份文字图标
                if (!empty($sign_img)) {
                    //计算名字所用长度
                    $nick_name_len = $this->calculateTextBox($user_info['nick_name'], $font, $nickname_font_size, 0);
                    $sign_img_x = 160 + $nick_name_len + 30;
                    $res['sign'] = [
                        'type' => 'image',
                        'path' => $sign_img,
                        'dst_x' => $sign_img_x,
                        'dst_y' => 34,
                        'src_w' => 88,
                        'src_h' => 34,
                        'corners' => 0,
                        'scaling' => ['w' => 88, 'h' => 34]
                    ];
                }
                $res['headimg'] = [
                    'type' => 'image',
                    'path' => $temp_headimg,
                    'dst_x' => 60,
                    'dst_y' => 32,
                    'src_w' => 100,
                    'src_h' => 100,
                    'corners' => 50,
                    'scaling' => ['w' => 100, 'h' => 100]
                ];
                $QR_url = $this->getGetQRUrl(3, $gid, $uid);
                $QR_path = $this->createQRcode($QR_url, false, false);
                $res['QR'] = [
                    'type' => 'image',
                    'path' => $QR_path,
                    'dst_x' => 402,
                    'dst_y' => 598,
                    'src_w' => 176,
                    'src_h' => 176,
                    'corners' => 0,
                    'scaling' => ['w' => 176, 'h' => 176]
                ];
                //$skuModel = new GoodsSku();
                //$main_info = $skuModel->db->where('sku_number', $gid)->getOne($skuModel->tableName);
                $main_info = MallSku::where('sku_number', $gid)->first();
                $res['main_img'] = [
                    'type' => 'image',
                    'path' => $img_url_share . $main_info['book_cover_img'],
                    'dst_x' => 160,
                    'dst_y' => 152,
                    'src_w' => 320,
                    'src_h' => 424,
                    'corners' => 0,
                    'scaling' => ['w' => 320, 'h' => 424]
                ];
                $res['main_text_1'] = [
                    'type' => 'text',
                    'size' => 26,
                    'x' => 60,
                    'y' => 644,
                    'font' => $font,
                    'text' => $main_info['book_name'],
                    'rgb' => '0,0,0',
                ];
                $res['main_text_2'] = [
                    'type' => 'text',
                    'size' => 18,
                    'x' => 60,
                    'y' => 656 + 34 - (34 - 24) / 2,
                    'font' => $font,
                    'text' => $main_info['book_writer'],
                    'rgb' => '153,153,153',
                ];
                break;
            case 4://会员海报
                $res['name'] = [
                    'type' => 'text',
                    'size' => 31,
                    'x' => 160,
                    'y' => 78,
                    'font' => $font,
                    'text' => $user_info['nick_name'],
                    'rgb' => '0,0,0',
                ];
                //身份帽子图标
                if (!empty($cap_img)) {
                    $res['cap'] = [
                        'type' => 'image',
                        'path' => $cap_img,
                        'dst_x' => 110,
                        'dst_y' => 20,
                        'src_w' => 34,
                        'src_h' => 32,
                        'corners' => 0,
                        'scaling' => ['w' => 34, 'h' => 32]
                    ];
                }
                //身份文字图标
                if (!empty($sign_img)) {
                    //计算名字所用长度
                    //                    $nick_name_len = mb_strlen($user_info['nick_name']) * 31;
                    $nick_name_len = $this->calculateTextBox($user_info['nick_name'], $font, $nickname_font_size, 0);
                    $sign_img_x = 140 + $nick_name_len + 30;
                    $res['sign'] = [
                        'type' => 'image',
                        'path' => $sign_img,
                        'dst_x' => $sign_img_x,
                        'dst_y' => 34,
                        'src_w' => 88,
                        'src_h' => 34,
                        'corners' => 0,
                        'scaling' => ['w' => 88, 'h' => 34]
                    ];
                }
                $res['headimg'] = [
                    'type' => 'image',
                    'path' => $temp_headimg,
                    'dst_x' => 40,
                    'dst_y' => 32,
                    'src_w' => 100,
                    'src_h' => 100,
                    'corners' => 50,
                    'scaling' => ['w' => 100, 'h' => 100]
                ];
                $QR_url = $this->getGetQRUrl(4, $gid, $uid);
                $QR_path = $this->createQRcode($QR_url, false, false);
                $res['QR'] = [
                    'type' => 'image',
                    'path' => $QR_path,
                    'dst_x' => 232,
                    'dst_y' => 584,
                    'src_w' => 176,
                    'src_h' => 176,
                    'corners' => 0,
                    'scaling' => ['w' => 176, 'h' => 176]
                ];
                break;
            case 5://精品课海报
                $res['name'] = [
                    'type' => 'text',
                    'size' => 31,
                    'x' => 160,
                    'y' => 70,
                    'font' => $font,
                    'text' => $user_info['nick_name'],
                    'rgb' => '0,0,0',
                ];
                //身份帽子图标
                if (!empty($cap_img)) {
                    $res['cap'] = [
                        'type' => 'image',
                        'path' => $cap_img,
                        'dst_x' => 110,
                        'dst_y' => 20,
                        'src_w' => 34,
                        'src_h' => 32,
                        'corners' => 0,
                        'scaling' => ['w' => 34, 'h' => 32]
                    ];
                }
                //身份文字图标
                if (!empty($sign_img)) {
                    //计算名字所用长度
                    //                    $nick_name_len = mb_strlen($user_info['nick_name']) * 31;
                    $nick_name_len = $this->calculateTextBox($user_info['nick_name'], $font, $nickname_font_size, 0);
                    $sign_img_x = 140 + $nick_name_len + 30;
                    $res['sign'] = [
                        'type' => 'image',
                        'path' => $sign_img,
                        'dst_x' => $sign_img_x,
                        'dst_y' => 34,
                        'src_w' => 88,
                        'src_h' => 34,
                        'corners' => 0,
                        'scaling' => ['w' => 88, 'h' => 34]
                    ];
                }
                $res['headimg'] = [
                    'type' => 'image',
                    'path' => $temp_headimg,
                    'dst_x' => 40,
                    'dst_y' => 32,
                    'src_w' => 100,
                    'src_h' => 100,
                    'corners' => 50,
                    'scaling' => ['w' => 100, 'h' => 100]
                ];
                $QR_url = $this->getGetQRUrl(5, $gid, $uid);
                $QR_path = $this->createQRcode($QR_url, false, false);
                $res['QR'] = [
                    'type' => 'image',
                    'path' => $QR_path,
                    'dst_x' => 422,
                    'dst_y' => 498,
                    'src_w' => 176,
                    'src_h' => 176,
                    'corners' => 0,
                    'scaling' => ['w' => 176, 'h' => 176]
                ];
//                $workModel = new Works();
//                $main_info = $workModel->db->where('id', $gid)->getOne($workModel->tableName);
                $main_info = Works::find($gid);
                if ($main_info['is_audio_book']) {
//                    $skuModel = new GoodsSku();
//                    $book_sku_info = $skuModel->db
//                        ->where('id', $main_info['book_sku'])
//                        ->getOne($skuModel->tableName, 'id,picture');
                    $book_sku_info = MallSku::find($main_info['book_sku']);

                    $res['main_img'] = [
                        'type' => 'image',
                        'path' => $img_url_share . $book_sku_info['picture'],
                        'dst_x' => 160,
                        'dst_y' => 152,
                        'src_w' => 320,
                        'src_h' => 320,
                        'corners' => 0,
                        'scaling' => ['w' => 320, 'h' => 320]
                    ];
                } else {
                    $res['main_img'] = [
                        'type' => 'image',
                        'path' => $img_url_share . $main_info['cover_img'],
                        'dst_x' => 40,
                        'dst_y' => 152,
                        'src_w' => 560,
                        'src_h' => 320,
                        'corners' => 0,
                        'scaling' => ['w' => 560, 'h' => 320]
                    ];
                }

                //计算需要些几行
                $temp_title = $main_info['title'];
                $temp_title_len = mb_strlen($temp_title);
                if ($temp_title_len > 16) {
                    $temp_title = mb_substr($temp_title, 0, 14) . '⋯';
                }
                $temp_title_len = mb_strlen($temp_title);
                if ($temp_title_len > 9) {
                    $res['main_text_1'] = [
                        'type' => 'text',
                        'size' => 26,
                        'x' => 40,
                        'y' => 533,
                        'font' => $font,
                        'text' => mb_substr($temp_title, 0, 9),
                        'rgb' => '0,0,0',
                    ];
                    $res['main_text_2'] = [
                        'type' => 'text',
                        'size' => 26,
                        'x' => 40,
                        'y' => 581,
                        'font' => $font,
                        'text' => mb_substr($temp_title, 9),
                        'rgb' => '0,0,0',
                    ];
                } else {
                    $res['main_text'] = [
                        'type' => 'text',
                        'size' => 26,
                        'x' => 40,
                        'y' => 555,
                        'font' => $font,
                        'text' => $temp_title,
                        'rgb' => '0,0,0',
                    ];
                }
                $money_res = $this->createPriceArr($main_info['price'], 5, $font);
                $res = array_merge($res, $money_res);
                break;
            case 6://线下课海报
                $res = [];
                break;
            case 7://优品海报
                $res['name'] = [
                    'type' => 'text',
                    'size' => 31,
                    'x' => 160,
                    'y' => 78,
                    'font' => $font,
                    'text' => $user_info['nick_name'],
                    'rgb' => '0,0,0',
                ];
                //身份帽子图标
                if (!empty($cap_img)) {
                    $res['cap'] = [
                        'type' => 'image',
                        'path' => $cap_img,
                        'dst_x' => 110,
                        'dst_y' => 20,
                        'src_w' => 34,
                        'src_h' => 32,
                        'corners' => 0,
                        'scaling' => ['w' => 34, 'h' => 32]
                    ];
                }
                //身份文字图标
                if (!empty($sign_img)) {
                    //计算名字所用长度
                    //                    $nick_name_len = mb_strlen($user_info['nick_name']) * 31;
                    $nick_name_len = $this->calculateTextBox($user_info['nick_name'], $font, $nickname_font_size, 0);
                    $sign_img_x = 140 + $nick_name_len + 30;
                    $res['sign'] = [
                        'type' => 'image',
                        'path' => $sign_img,
                        'dst_x' => $sign_img_x,
                        'dst_y' => 34,
                        'src_w' => 88,
                        'src_h' => 34,
                        'corners' => 0,
                        'scaling' => ['w' => 88, 'h' => 34]
                    ];
                }
                $res['headimg'] = [
                    'type' => 'image',
                    'path' => $temp_headimg,
                    'dst_x' => 40,
                    'dst_y' => 32,
                    'src_w' => 100,
                    'src_h' => 100,
                    'corners' => 50,
                    'scaling' => ['w' => 100, 'h' => 100]
                ];
                $QR_url = $this->getGetQRUrl(7, $gid, $uid);
                $QR_path = $this->createQRcode($QR_url, false, false);
                $res['QR'] = [
                    'type' => 'image',
                    'path' => $QR_path,
                    'dst_x' => 424,
                    'dst_y' => 620,
                    'src_w' => 176,
                    'src_h' => 176,
                    'corners' => 0,
                    'scaling' => ['w' => 176, 'h' => 176]
                ];
//                $goodsObj = new MallGoods();
//                $main_info = $goodsObj->getOne($goodsObj::$table, ['id' => $gid], '*');
                $main_info = MallGoods::find($gid);

                $res['main_img'] = [
                    'type' => 'image',
                    'path' => $img_url_share . $main_info['picture'],
                    'dst_x' => 100,
                    'dst_y' => 152,
                    'src_w' => 440,
                    'src_h' => 440,
                    'corners' => 0,
                    'scaling' => ['w' => 440, 'h' => 440]
                ];
                //计算需要些几行
                $temp_title = $main_info['name'];
                $temp_title_len = mb_strlen($temp_title);
                if ($temp_title_len > 16) {
                    $temp_title = mb_substr($temp_title, 0, 14) . '⋯';
                }
                $temp_title_len = mb_strlen($temp_title);
                if ($temp_title_len > 9) {
                    $res['main_text_1'] = [
                        'type' => 'text',
                        'size' => 26,
                        'x' => 40,
                        'y' => 654,
                        'font' => $font,
                        'text' => mb_substr($temp_title, 0, 10),
                        'rgb' => '0,0,0',
                    ];
                    $res['main_text_2'] = [
                        'type' => 'text',
                        'size' => 26,
                        'x' => 40,
                        'y' => 696,
                        'font' => $font,
                        'text' => mb_substr($temp_title, 10),
                        'rgb' => '0,0,0',
                    ];
                } else {
                    $res['main_text'] = [
                        'type' => 'text',
                        'size' => 26,
                        'x' => 40,
                        'y' => 675,
                        'font' => $font,
                        'text' => $temp_title,
                        'rgb' => '0,0,0',
                    ];
                }
                $money_res = $this->createPriceArr($main_info['price'], 7, $font);
                $res = array_merge($res, $money_res);
                break;
            case 8://专栏
                $res['name'] = [
                    'type' => 'text',
                    'size' => 31,
                    'x' => 160,
                    'y' => 70,
                    'font' => $font,
                    'text' => $user_info['nick_name'],
                    'rgb' => '0,0,0',
                ];
                //身份帽子图标
                if (!empty($cap_img)) {
                    $res['cap'] = [
                        'type' => 'image',
                        'path' => $cap_img,
                        'dst_x' => 110,
                        'dst_y' => 19,
                        'src_w' => 34,
                        'src_h' => 32,
                        'corners' => 0,
                        'scaling' => ['w' => 34, 'h' => 32]
                    ];
                }
                //身份文字图标
                if (!empty($sign_img)) {
                    //计算名字所用长度
                    //                    $nick_name_len = mb_strlen($user_info['nick_name']) * 31;
                    $nick_name_len = $this->calculateTextBox($user_info['nick_name'], $font, $nickname_font_size, 0);
                    $sign_img_x = 140 + $nick_name_len + 30;
                    $res['sign'] = [
                        'type' => 'image',
                        'path' => $sign_img,
                        'dst_x' => $sign_img_x,
                        'dst_y' => 34,
                        'src_w' => 88,
                        'src_h' => 34,
                        'corners' => 0,
                        'scaling' => ['w' => 88, 'h' => 34]
                    ];
                }
                $res['headimg'] = [
                    'type' => 'image',
                    'path' => $temp_headimg,
                    'dst_x' => 40,
                    'dst_y' => 32,
                    'src_w' => 100,
                    'src_h' => 100,
                    'corners' => 50,
                    'scaling' => ['w' => 100, 'h' => 100]
                ];
                $QR_url = $this->getGetQRUrl(8, $g_t_id, $uid);
                $QR_path = $this->createQRcode($QR_url, false, false);
                $res['QR'] = [
                    'type' => 'image',
                    'path' => $QR_path,
                    'dst_x' => 422,
                    'dst_y' => 618,
                    'src_w' => 176,
                    'src_h' => 176,
                    'corners' => 0,
                    'scaling' => ['w' => 176, 'h' => 176]
                ];
//                $colModel = new Column();
//                $main_info = $colModel->db->where('id', $gid)->getOne($colModel::$table);
                $main_info = Column::find($gid);
                $res['main_img'] = [
                    'type' => 'image',
                    'path' => $img_url_share . $main_info['cover_pic'],
                    'dst_x' => 100,
                    'dst_y' => 152,
                    'src_w' => 440,
                    'src_h' => 440,
                    'corners' => 0,
                    'scaling' => ['w' => 440, 'h' => 440]
                ];
                $res['main_text_name'] = [
                    'type' => 'text',
                    'size' => 26,
                    'x' => 40,
                    'y' => 646,
                    'font' => $font,
                    'text' => $main_info['name'],
                    'rgb' => '0,0,0',
                ];
                //计算需要些几行
                $temp_title = $main_info['subtitle'];
                $temp_title_len = mb_strlen($temp_title);
                if ($temp_title_len > 16) {
                    $temp_title = mb_substr($temp_title, 0, 13) . '……';
                }
                $res['main_text_subtitle'] = [
                    'type' => 'text',
                    'size' => 17,
                    'x' => 40,
                    'y' => 686,
                    'font' => $font,
                    'text' => $temp_title,
                    'rgb' => '153,153,153',
                ];
                $money_res = $this->createPriceArr($main_info['price'], 8, $font);
                $res = array_merge($res, $money_res);
                break;
            case 21: //直播海报
                $res['name'] = [
                    'type' => 'text',
                    'size' => 31,
                    'x' => 160,
                    'y' => 70,
                    'font' => $font,
                    'text' => $user_info['nick_name'],
                    'rgb' => '0,0,0',
                ];
                //身份文字图标
                if (!empty($sign_img)) {
                    //计算名字所用长度
                    $nick_name_len = $this->calculateTextBox($user_info['nick_name'], $font, $nickname_font_size, 0);
                    $sign_img_x = 140 + $nick_name_len + 30;
                    $res['sign'] = [
                        'type' => 'image',
                        'path' => $sign_img,
                        'dst_x' => $sign_img_x,
                        'dst_y' => 32,
                        'src_w' => $sign_img_w ?? 88,
                        'src_h' => 34,
                        'corners' => 0,
                        'scaling' => ['w' => $sign_img_w ?? 88, 'h' => 34],
                        //'scaling' => ['w' => 88, 'h' => 34]
                    ];
                }
                $res['headimg'] = [
                    'type' => 'image',
                    'path' => $temp_headimg,
                    'dst_x' => 40,
                    'dst_y' => 32,
                    'src_w' => 100,
                    'src_h' => 100,
                    'corners' => 50,
                    'scaling' => ['w' => 100, 'h' => 100],
                ];
//                $res['headimg_bg'] = [
//                    'type' => 'image',
//                    'path' => $headimg_bg_path,
//                    'dst_x' => 35,
//                    'dst_y' => 27,
//                    'src_w' => 110,
//                    'src_h' => 110,
//                ];
                //身份帽子图标
                if (!empty($cap_img)) {
                    $res['cap'] = [
                        'type' => 'image',
                        'path' => $cap_img,
                        'dst_x' => 110,
                        'dst_y' => 20,
                        'src_w' => 32,
                        'src_h' => 30,
                    ];
                }
                $QR_url = $this->getGetQRUrl(10, $gid, $uid);
                $QR_path = $this->createQRcode($QR_url, false, false);
                $res['QR'] = [
                    'type' => 'image',
                    'path' => $QR_path,
                    'dst_x' => 417,
                    'dst_y' => 494,
                    'src_w' => 200,
                    'src_h' => 200,
                    'corners' => 0,
                    'scaling' => ['w' => 200, 'h' => 200],
                    //'scaling' => ['w' => 176, 'h' => 176]
                ];

//                $live_Obj = new \App\Model\App\V3_1\Live();
//                $main_info = $live_Obj->db->where('id', $gid)->getOne($live_Obj->tableName);
                $main_info =  Live::find($gid);
                $res['main_img'] = [
                    'type' => 'image',
                    'path' => $img_url_share . $main_info['cover_img'],
                    'dst_x' => 40,
                    'dst_y' => 152,
                    'src_w' => 560,
                    'src_h' => 320,
                    'corners' => 0,
                    'scaling' => ['w' => 560, 'h' => 320],
                ];

                //计算需要些几行
                $temp_title = $main_info['title'];
                $temp_title_len = mb_strlen($temp_title);

                $temp_title_len = mb_strlen($temp_title);
                if ($temp_title_len > 9) {
                    $arr = str_split($temp_title, 38);
                    $str_s_y = 537;
                    foreach ($arr as $key => $val) {
                        if ($key >= 2) {
                            break;
                        }
                        $text_line = $key + 1;
                        $res['main_text_' . $text_line] = [
                            'type' => 'text',
                            'size' => 20,
                            'x' => 40,
                            'y' => $str_s_y,
                            'font' => $font,
                            'text' => $val,
                            'rgb' => '3,3,3',
                        ];
                        $str_s_y += 30;
                    }

                    $main_text_3 = 3;
                    if ($main_info['id'] == 4) {
                        $res['main_text_' . $main_text_3] = [
                            'type' => 'text',
                            'size' => 16,
                            'x' => 40,
                            'y' => $str_s_y,
                            'font' => $font,
                            'text' => "   全程本人现场直播",
                            'rgb' => '3,3,3',
                        ];
                        $str_s_y += 30;
                        $main_text_3++;
                    }
                    $res['main_text_' . $main_text_3] = [
                        'type' => 'text',
                        'size' => 16,
                        'x' => 40,
                        'y' => $str_s_y,
                        'font' => $font,
                        'text' => date('m月d日 H:i', $main_info['str_time']) . ' - ' . date('m月d日 H:i', $main_info['end_time']),
                        'rgb' => '3,3,3',
                    ];
                    if ($main_info['id'] == 4) {
                        $str_s_y += 30;
                        $res['main_text_' . $main_text_3]['text'] = '   ' . date('m月d日', $main_info['str_time']) . ' - ' . date('m月d日', $main_info['end_time']);
                        $res['main_text_' . ($main_text_3 + 1)] = [
                            'type' => 'text',
                            'size' => 16,
                            'x' => 40,
                            'y' => $str_s_y,
                            'font' => $font,
                            'text' => '   每晚' . date('H:i', $main_info['str_time']) . ' - ' . date('H:i', $main_info['end_time']),
                            'rgb' => '3,3,3',
                        ];
                    }
                } else {
                    $res['main_text_1'] = [
                        'type' => 'text',
                        'size' => 20,
                        'x' => 40,
                        'y' => 520,
                        'font' => $font,
                        'text' => $temp_title,
                        'rgb' => '3,3,3',
                    ];
                    $str_s_y = 550;
                    $main_text_2 = 2;
                    if ($main_info['id'] == 4) {
                        $res['main_text_' . $main_text_2] = [
                            'type' => 'text',
                            'size' => 16,
                            'x' => 40,
                            'y' => $str_s_y,
                            'font' => $font,
                            'text' => "   全程本人现场直播",
                            'rgb' => '3,3,3',
                        ];
                        $str_s_y += 30;
                        $main_text_2++;
                    }

                    $res['main_text_' . $main_text_2] = [
                        'type' => 'text',
                        'size' => 16,
                        'x' => 40,
                        'y' => $str_s_y,
                        'font' => $font,
                        'text' => date('m月d日 H:i', $main_info['str_time']) . ' - ' . date('m月d日 H:i', $main_info['end_time']),
                        'rgb' => '3,3,3',
                    ];
                    if ($main_info['id'] == 4) {
                        $str_s_y += 30;
                        $res['main_text_' . $main_text_2]['text'] = '   ' . date('m月d日', $main_info['str_time']) . ' - ' . date('m月d日', $main_info['end_time']);
                        $res['main_text_' . ($main_text_2 + 1)] = [
                            'type' => 'text',
                            'size' => 16,
                            'x' => 40,
                            'y' => $str_s_y,
                            'font' => $font,
                            'text' => '   每晚' . date('H:i', $main_info['str_time']) . ' - ' . date('H:i', $main_info['end_time']),
                            'rgb' => '3,3,3',
                        ];
                    }
                }
                break;

        }
        return $res;
    }

    //计算名字长度像素
    function calculateTextBox($text, $fontFile, $fontSize, $fontAngle) {
        //$text = Tool::textDecode($text);
        $rect = imagettfbbox($fontSize, $fontAngle, $fontFile, $text);
        $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6]));
        $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6]));
        $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7]));
        $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7]));

        $res = array(
            "left" => abs($minX) - 1,
            "top" => abs($minY) - 1,
            "width" => $maxX - $minX,
            "height" => $maxY - $minY,
            "box" => $rect
        );

        return $res['width'];
    }

    //获取二维码网址
    protected function getGetQRUrl($type, $gid, $uid) {

        $info_id = 0;
        $u_type = 0;
        switch ($type) {
            case 3://好书
                $m_t_type = 2;
                $u_type = 8;
                break;
            case 4://会员
                $m_t_type = 6;
                $u_type = 10;
                break;
            case 5://精品课
                $temp_work = Works::find($gid);
                $temp_work_info = WorksInfo::select('id')->where(['pid' => $gid, 'status' => 4])->OrderBy('id','desc')->first();
                $info_id = $temp_work_info['id'];
                if ($temp_work['is_audio_book']) {
                    $m_t_type = 4;
                    $u_type = 5;
                } else {
                    $m_t_type = 3;
                    if ($temp_work['is_pay']) {
                        switch (intval($temp_work['type'])) {
                            case 1:
                                $u_type = 6;
                                break;
                            case 2:
                                $u_type = 7;
                                break;
                            case 3:
                                $u_type = 4;
                                break;
                        }
                    } else {
                        switch (intval($temp_work['type'])) {
                            case 1:
                                $u_type = 2;
                                break;
                            case 2:
                                $u_type = 3;
                                break;
                            case 3:
                                $u_type = 4;
                                break;
                        }
                    }
                }
                break;
            case 7://商品
                $u_type = 9;
                $m_t_type = 2;
                break;
            case 8://专栏
                $u_type = 1;
                $m_t_type = 1;
                break;
            case 10:
                //直播
                $u_type = 11;
                $m_t_type = 11;
                break;
            case 22: //三八活动app注册邀请
                //todo 添加用户标识
                // $res = $mtModel->createJumpUrl(22, 0, 0, 0);
                // return $res;
                return 'https://a.app.qq.com/o/simple.jsp?pkgname=com.huiyujiaoyu.powertime';
        }
        $twitterObj = new MallTwitter();
        //  1:专栏  2:课程视频  3:课程音频  4:课程文章  5:听书
        //  6:精品课视频  7:精品课音频  8:书籍  9:商品  10:会员
        $res = $twitterObj->createJumpUrl($u_type, $gid, $info_id, $uid);

        //添加 mallTwitter Twitter_add
        // 1：专栏   2：商品  3：精品课 4听书 5线下课 6邀请卡(有且只有一条记录当前用户)
        $m_t_data['user_id'] = $uid;
        $m_t_data['type'] = $m_t_type;
        $m_t_data['cpid'] = $gid;
        $twitterObj::Twitter_Add($m_t_data);
        return $res;
    }

    //生成价签部分
    function createPriceArr($price, $post_type, $font) {
        $price = strval($price);
        $money_color = '87, 150, 255';
        $money_1 = '￥';
        $temp_money_dot = strpos($price, '.');
        if ($temp_money_dot === false) {
            $price_1 = $price;
            $price_2 = '00';
        } else {
            $price_1 = substr($price, 0, $temp_money_dot);
            $price_2 = substr($price, $temp_money_dot + 1);
        }

        $money_2 = $price_1;
        if ($post_type == 7) {
            $money_3 = '.' . $price_2;
        } else if ($post_type == 5) {
            $money_3 = '.' . $price_2 . '/永久';
        } else {
            $money_3 = '.' . $price_2 . '/年';
        }

        switch (intval($post_type)) {
            case 5://精品课海报
                $x = 40;
                $y = 636;
                $unit = '/永久';
                break;
            case 7://优品海报
                $x = 40;
                $y = 758;
                $unit = '';
                break;
            case 8://专栏
                $x = 40;
                $y = 752;
                $unit = '/年';
                break;
        }

        $res['main_text_price_1'] = [
            'type' => 'text',
            'size' => 21,
            'x' => $x,
            'y' => $y,
            'font' => $font,
            'text' => $money_1,
            'rgb' => $money_color,
        ];
        $res['main_text_price_2'] = [
            'type' => 'text',
            'size' => 34,
            'x' => $x + 22,
            'y' => $y,
            'font' => $font,
            'text' => $money_2,
            'rgb' => $money_color,
        ];
        $res['main_text_price_3'] = [
            'type' => 'text',
            'size' => 21,
            'x' => $x + 18 + strlen($money_2) * 26,
            'y' => $y,
            'font' => $font,
            'text' => $money_3,
            'rgb' => $money_color,
        ];
        return $res;
    }


}