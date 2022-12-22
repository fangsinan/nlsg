<?php

namespace App\Servers\V5;

use App\Models\User;
use App\Models\XiaoeTech\XeDistributor;
use App\Models\XiaoeTech\XeDistributorCustomer;
use App\Models\XiaoeTech\XeOrder;
use App\Models\XiaoeTech\XeOrderDistribute;
use App\Models\XiaoeTech\XeOrderGoods;
use App\Models\XiaoeTech\XeUser;
use App\Models\XiaoeTech\XeUserJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class XiaoeTechServers
{
    public $err_msg = '';
    public $access_token = '';

    public function __construct()
    {
        $this->get_token();
    }

    public function test()
    {
//        $arr=['1643191','823187','1643189','1643186','1643185','1643183','1643181','1643180','1643178','1643177','1643175','1643174','1643173','1643172','1643171','1643170','1643168','1643167','1643166','1643164','1643162','1643161','1643160','1643159','1643157','1643156','1643155','1643154','1643153','1643152','823356','1643151','1643150','1643149','1643148','1643146','1643145','1643144','1643143','1643142','1643141','1643140','1643139','1643138','1643137','1643136','1643135','1643134','1643132','1643131','1643129','1643128','1643127','1643126','1643125','1643124','1643122','1643120','1643119','1643118','1643117','1643116','1355175','1643115','1372629','1643114','1643113','1643112','1643111','1643110','1643109','1643108','1643107','1643106','1643105','1643104','1643103','1643102','1643101','1643100','1643099','1643098','1643096','1643095','1643094','1643093','1643092','1643091','1643090','1643089','1643088','1643085','1643083','1643082','1643081','1643080','1643079','1643078','1643077','1643075','1643074','1643073','1643072','1643071','1643069','1643067','1643064','1643063','1643062','1643061','1643060','1643059','1643058','1643057','1643056','1643055','1643054','1643053','1643052','1643051','1643050','1643049','1643048','1643047','1643046','1643045','1643044','1643043','1643042','1643040','1643039','1643038','1643037','1643033','1643032','1643031','1643030','1643029','1643028','1643027','1643026','1643025','823441','1643023','1643022','1643020','1643019','1643017','1643016','1643015','1643014','1643013','1643012','1643011','1643010','1643009','1643007','1643006','1643004','1643003','1643001','1643000','1642999','1642998','1642997','1642996','1642995','1642994','1642993','1642992','1642991','1642990','1642989','1642988','1642987','1642985','1642984','1642983','1642982','1642980','1642979','1642978','1642977','1642976','1642975','822342','1642974','1642973','1642972','1642971','1642970','1642969','1642968','1642967','1642966','1642965','1642964','1642960','1642959','1642958','1642957','1642956','1642955','1642954','1642953','1642952','1642951','822549','1642950','1642948','1642944','1642943','1642942','1642941','1642940','1642939','1642938','1642937','1642936','1642934','1642933','1642932','1642931','1642930','1642929','1642928','1642927','1642926','1642925','1642924','1642923','1642922','1642921','1642920','1642919','1642918','1642916','1642914','1642913','1642912','822072','1642910','1642909','1642908','1642907','1642906','1642904','1642903','1642901','1642900','1642899','1642898','1642897','1642895','1642894','1642893','1642892','1642891','1642890','1642889','1642887','1642886','1642885','1642884','1642883','1642882','1642881','1642880','1642879','1642877','1642876','1642875','1642874','1642872','1642871','1642870','1642869','1642864','1642863','1642862','1642861','1642859','1642858','1642857','1642855','1642853','1642852','1642851','1642850','1642849','1642848','1642846','1642845','1642844','1642842','1642840','1642839','1642838','1642837','1642836','1642835','1642834','1642833','1642832','1642831','1642830','1642829','1642828','1642827','1642826','1642824','1642823','1642821','1642820','1642819','1642818','1642817','1642816','1642815','1642811','1642810','1642809','1642808','1642807','1642806','1642805','1642804','1642802','1642801','1642800','1642799','1642798','1642797','1642796','1642795','1642794','1642793','1642792','1642791','1642790','1642789','1642788','1642786','1642785','1642784','1642783','1642782','1642780','1642778','1642777','1642775','1642773','1642772','1642771','1642769','1642768','1642767','1642766','821960','1642765','823083','1642764','1642763','1642762','1642761','1642760','1642758','1642756','1642755','1642754','1642753','1642752','1642751','1642749','1642747','1642744','1642743','1642740','1642739','1642737','1401174','1642736','1642735','1642733','1642732','1642731','1642730','1642729','1642728','1642727','1642725','1642724','1642723','1642722','1642721','1642720','1642719','1642718','1642717','1604882','1642716','822378','1642714','1642713','1642712','1642711','1642710','1642709','1642707','1642706','1642705','1642704','1642703','1642702','1642701','1642699','822863','1642696','1642695','1642694','1642693','1642691','1642690','1642689','1642688','1642687','1642686','1642685','1642683','1642682','1642680','1642679','1642678','1642677','1642676','1642675','1642674','1642673','1642672','1642671','1642670','1642669','1642668','1642667','1642666','1642665','1642664','1642663','1642662','1642661','1642660','1642658','1642657','1642656','1642654','1642652','1642651','1642650','1642649','1642648','1642647','1642646','1642645','1642644','1642643','1642641','1642642','1642638','1642636','1642634','1642633','1642631','1642630','1642629','822565','1642628','1642627','1642626','1642625','1642624','1642623','1642622','1642621','1642619','1642618','1642616','1642615','1642614','1642613','1642612','1642611','1642609','1642606','1642605','1642603','1642602','1642601','1642600','1642598','1642597','1642596','1642595','1642592','1642591','1642590','1642589','1642588','1642587','1642586','1642585','1642584','1642581','1642579','1642578','1642576','1642575','1642574','1642573','822372','1642569','1642568','1642567','1642566','1642564','1642562','1642561','1642560','1642559','1642557','1642556','1642555','1642554','1642553','1642552','1642551','1642550','1642548','1642547','1642546','1642545','1642544','1642541','1642539','1642538','1642535','1642534','1642533','1642532','1642531','1642530','1642528','1642527','1642526','1642525','1642524','1642523','1642521','1642520','1642519','1642518','1642516','1642513','1642512','1642511','1642510','1642507','1636396','1642506','1642503','1642502','1642500','1642499','1642498','1642495','1642494','1642493','1642492','1642491','1642490','1642489','1642488','1642487','1642485','1642484','1642483','1642482','1642481','1642480','1642478','1642476','1642475','1642474','1642471','1642470','1642469','1642468','1642466','1642464','1642463','1642462','1642461','1642460','1642459','822621','821998','1642457','1642455','1642454','1642453','1642450','823013','1642445','1642444','1642443','1642441','1642440','1642439','1642438','1642437','1642435','1642434','1642433','1642432','1642431','1642430','1642428','1642427','1642424','1642422','1642421','1642418','1642417','1657576','1642416','1642415','1642413','1642411','1642410','1642409','1642408','1642407','1642401','1642398','1642396','1642394','1642391','1642390','1642386','1642385','1642383','1642382','1642378','1642377','1642372','1642371','1642370','1642369','822593','1642367','1642366','1642365','1642363','1642362','1642359','1642357','1642354','1642349','1642347','1642345','1642344','1642343','1642341','1642339','1642335','1642334','1642332','1642331','1642329','1642324','1642323','1642322','1642321','1642320','1642319','1642308','1642307','1642305','822509','1642302','1642300','1642298','1642295','1643065','1642395','1657575','1642653','1642419','1642297','1642325','1657574','1642350','1657573','1642352','1657572','1657571','1642577','1642429','1642543','1642515','1642338','1642542','1642497','1642472','1642393','1657570','1657569','1642537','1642420','1657568','1642681','1657567','1642399','1657566','1642379','1657565','1657564','1642364','1657563','1643429','1642289','1642746','1642757','1643035','1642917','1642486','1642301','1642355','1642514','1643066','1642742','1642442','1642911','1642452','1181638','1642384','1657562','1642389','1657561','1642380','1643021','1642698','1642361','1643187','1642604','1648637','1642414','1642403','1642336','1642400','1642692','1642309','1642583','1648635','1642902','1642303','1642337','1642306','1643169','1642449','1642580','1642318','1642317','1642517','1642456','1643182','1648634','1642327','1642639','1642509','1643458','1642715','1642311','1642299','1643354','1642330','1642962','1642358','1642436','1642342','1642640','1642540','1657577','1642388','1642387','1642423','1642360','822248','1642333','1642397','1642505','1642326','1642726','1642310','1642700','1642632','1642572','1642803'];
//        $err_arr=[];
//        $list=XeDistributorCustomer::query()->whereIn('id',$arr)->get();
//        foreach ($list as $k=>$XeDistributorCustomer){
//
//            if($k%10==0){
//                sleep(1);
//            }
//            $res=$this->distributor_member_change($XeDistributorCustomer->sub_user_id,$XeDistributorCustomer->xe_user_id);
//            var_dump($k);
//            var_dump($XeDistributorCustomer->id);
//            var_dump($res);
//            die;
//            if(!checkRes($res)){
//                $err_arr[]=$XeDistributorCustomer->id;
//            }
//        }
//        var_export($err_arr);
////        var_dump($this->access_token);
////        die;
    }

    public function get_token($is_refresh = 0)
    {

        $token_key = 'xiaoe-tech-token';
        if (!$is_refresh) {
            $access_token = Redis::get($token_key);
            if ($access_token) {
                $this->access_token = $access_token;
                return $access_token;
            }
        }

        $paratms =
            [
                "app_id" => "appPfbUuN2M8786",
                "client_id" => "xopNbM35i9O5609",
                "secret_key" => "QS7bKFK2N4SRXTDM0Slcm4D5U5qL1Uo8",
                "grant_type" => "client_credential"    //获取token时， grant_type = client_credential
            ];

        $res = self::curlGet('https://api.xiaoe-tech.com/token', $paratms);
        DB::table('nlsg_log_info')->insert([
            'url' => 'token',
            'parameter' => json_encode($paratms),
            'message' => json_encode($res),
            'created_at' => date('Y-m-d H:i:s', time())
        ]);
        if (empty($res['body']['data']['access_token'])) {
            $this->err_msg = $res['body']['msg'];
            return false;
        }

        Redis::setex($token_key, 3000, $res['body']['data']['access_token']);
        $this->access_token = $res['body']['data']['access_token'];
        return $res['body']['data']['access_token'];
    }


    /**
     * 获取小鹅通订单
     * 一小时运行一次 todo
     */
    public function sync_order_list($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }


        for ($i = 1; $i <= 1000; $i++) {

            $redis_page_index_key = 'xe_sync_order_list_page_index';
            $page_index = Redis::lpop($redis_page_index_key);

            if ($is_init) {
                $page_index = 1;
            }

            if (empty($page_index)) {
                return false;
            }

            $page_size = 100;
            $paratms = [
                'access_token' => $this->get_token(),
                'page' => intval($page_index),
                'page_size' => intval($page_size),
                'order_asc' => 'desc',
            ];

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.ecommerce.order.list/1.0.0', $paratms);
            DB::table('nlsg_log_info')->insert([
                'url' => 'xe.ecommerce.order.list',
                'line' => $res['body']['code'],
                'parameter' => json_encode($paratms),
                'message' => $res['body']['data']['total'] ?? 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ]);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                    continue;
                }
                $this->err_msg = $res['body']['msg'];
                return false;
            }


            $return_list = $res['body']['data']['list'] ?? [];

            if (empty($return_list)) {
                return false;
            } else {
                if ($is_init) {
                    Redis::del($redis_page_index_key);
                    $count = $res['body']['data']['total'];
                    $total_page = ceil($count / $page_size) + 1;
                    for ($i = 2; $i <= $total_page; $i++) {
                        var_dump($i);
                        Redis::rpush($redis_page_index_key, $i);
                    }
                }
            }

            foreach ($return_list as $order) {

                $order_info = $order['order_info'] ?? [];
                $good_list = $order['good_list'] ?? [];
                $buyer_info = $order['buyer_info'] ?? [];
                $payment_info = $order['payment_info'] ?? [];
                $price_info = $order['price_info'] ?? [];
                $ship_info = $order['ship_info'] ?? [];

                var_dump($order_info['order_id']);
                try {
                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $order_info['user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                        $XeUser->xe_user_id = $order_info['user_id'];
                        $XeUser->avatar = $buyer_info['avatar_url'];
                        $XeUser->nickname = $buyer_info['nickname'];
                        $XeUser->phone = $buyer_info['phone_number'];
                        $XeUser->is_sync = 1;
                        $XeUser->save();
                    }
                } catch (\Exception $e) {
                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }

                //保存推广员用户
                if ($order_info['share_user_id']) {
                    try {
                        $XeShareUser = XeUser::query()->where('xe_user_id', $order_info['share_user_id'])->first();
                        if (!$XeShareUser) {
                            $XeShareUser = new XeUser();
                            $XeShareUser->xe_user_id = $order_info['share_user_id'];
                            $XeShareUser->is_sync = 1;
                            $XeShareUser->save();
                        }
                    } catch (\Exception $e) {
                        $errCode = $e->getCode();
                        if ($errCode != 23000) {
                            return $e->getMessage();
                        }
                    }
                }

                try {
                    //查询订单是否存在
                    $XeOrder = XeOrder::query()->where('order_id', $order_info['order_id'])->first();
                    if (!$XeOrder) {
                        $XeOrder = new XeOrder();
                    }

                    foreach ($order_info as $key => $val) {

                        if ($val === '0000-00-00 00:00:00') {
                            $val = null;
                        }

                        if (in_array($key, [
                            'actual_fee', 'aftersale_show_state', 'aftersale_show_state_time', 'app_id', 'channel_bus_id', 'channel_type', 'check_state',
                            'created_time', 'deduct_amount', 'discount_amount', 'freight_actual_price', 'freight_original_price', 'goods_buy_num', 'goods_name',
                            'goods_original_total_price', 'goods_spu_sub_type', 'goods_spu_type', 'modified_amount', 'order_close_type', 'order_id', 'order_state',
                            'order_state_time', 'order_type', 'pay_state', 'pay_state_time', 'pay_type', 'refund_fee', 'refund_time', 'relation_order_appid',
                            'relation_order_id', 'relation_order_type', 'settle_state', 'settle_state_time', 'share_type', 'share_user_id', 'ship_way_choose_type',
                            'sub_order_type', 'trade_id', 'update_time', 'use_collection', 'user_id', 'wx_app_type'])) {
                            switch ($key) {
                                case 'user_id':
                                    $XeOrder->xe_user_id = $val;
                                    break;
                                case 'created_time':
                                    $XeOrder->xe_created_time = $val;
                                    break;
                                case 'update_time':
                                    $XeOrder->xe_update_time = $val;
                                    break;
                                default:
                                    $XeOrder->$key = $val;
                            }
                        }
                    }

                    foreach ($buyer_info as $k => $v) {
                        if (in_array($k, ['nickname', 'avatar_url', 'phone_number'])) {
                            $XeOrder->$k = $v;
                        }
                    }

                    foreach ($payment_info as $k => $v) {
                        if (in_array($k, ['third_order_id', 'out_order_id'])) {
                            $XeOrder->$k = $v;
                        }
                    }
                    foreach ($price_info as $k => $v) {
                        if (in_array($k, ['actual_price', 'freight_modified_price', 'freight_price', 'origin_price', 'total_modified_amount', 'total_price'])) {
                            $XeOrder->$k = $v;
                        }
                    }
                    foreach ($ship_info as $k => $v) {
                        if (in_array($k, ["city", "company", "confirm_time", "county", "detail", "express_id", "invalid_time", "phone", "province", "receiver", "remark", "ship_time", "user_id"])) {
                            if (in_array($k, ['confirm_time', 'ship_time', 'invalid_time']) && empty($v)) {
                                $v = null;
                            }
                            $key = 'ship_info_' . $k;
                            $XeOrder->$key = $v;
                        }
                    }

                    //判断是否是推广员
                    if ($XeOrder->is_distributor == 0
                        && $XeOrder->goods_name == '幸福学社合伙人'
                        && $XeOrder->goods_original_total_price == 258000
                        && $XeOrder->pay_state == 1
                        && $XeOrder->order_state == 4
                    ) {

                        var_dump($XeOrder->goods_name);
                        $res = $this->distributor_member_add('', $XeOrder->xe_user_id);
                        var_dump($res);
                        if (checkRes($res)) {
                            if (empty($res['is_exist'])) {
                                $XeOrder->is_distributor = 1;
                            } else {
                                $XeOrder->is_distributor = 2;
                            }
                        }
                    }

                    $XeOrder->save();
                } catch (\Exception $e) {
                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }


                foreach ($good_list as $good) {


                    $XeOrderGoods = XeOrderGoods::query()->where('order_id', $order_info['order_id'])->where('sku_id', $good['sku_id'])->first();
                    if (!$XeOrderGoods) {
                        $XeOrderGoods = new XeOrderGoods();
                    }

                    $discounts_info = $good['discounts_info'] ?? [];
                    unset($good['discounts_info']);

                    $XeOrderGoods->xe_user_id = $order_info['user_id'];
                    $XeOrderGoods->order_id = $order_info['order_id'];
                    $XeOrderGoods->discount_amount_total = $discounts_info['discount_amount_total'] ?? 0;
                    $XeOrderGoods->discount_count = $discounts_info['discount_count'] ?? 0;
                    $XeOrderGoods->discount_desc = $discounts_info['discount_detail']['discount_desc'] ?? '';
                    $XeOrderGoods->discount_id = $discounts_info['discount_detail']['discount_id'] ?? '';
                    $XeOrderGoods->discount_name = $discounts_info['discount_detail']['discount_name'] ?? '';
                    $XeOrderGoods->discount_type = $discounts_info['discount_detail']['discount_type'] ?? '';
                    $XeOrderGoods->discount_price = $discounts_info['discount_detail']['discount_price'] ?? 0;

                    foreach ($good as $k => $v) {
                        if (in_array($k, [
                            "buy_num", "check_state", "discounts_info", "expire_desc", "expire_end", "expire_start",
                            "goods_desc", "goods_image", "goods_name", "goods_sn", "goods_spec_desc", "period_type",
                            "refund_state", "refund_state_desc", "relation_goods_id", "relation_goods_type", "relation_goods_type_desc",
                            "resource_id", "resource_type", "ship_state", "ship_state_desc", "sku_id", "sku_spec_code", "spu_id", "spu_type", "total_price", "unit_price"])) {

                            if ($v === '0000-00-00 00:00:00') {
                                $v = null;
                            }
                            $XeOrderGoods->$k = $v;
                        }

                    }

                    $XeOrderGoods->save();
                }
            }

            if ($is_init) {
                return false;
            }

        }

    }

    /**
     * 注册新用户
     */
    public function user_register($phone)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        try {
            //保存客户信息
            $baseUser = User::query()->where('phone', $phone)->first();
            if (!$baseUser) {
                $baseUser = new User();
                $baseUser->phone = strval($phone);
                $baseUser->nickname = substr_replace($phone, '****', 3, 4);
                $res = $baseUser->save();
                if (!$res) {
                    return '用户保存失败';
                }
            }
        } catch (\Exception $e) {
            $errCode = $e->getCode();
            if ($errCode != 23000) {
                return $e->getMessage();
            }
        }

        //查询小鹅通用户
        $XeUser = XeUser::query()->where('phone', $phone)->first();
        if ($XeUser) {
            return ['user_id' => $XeUser->xe_user_id, 'created_at' => $XeUser->user_created_at];
        }

        if (empty($baseUser->headimg)) {
            $avatar = config('env.IMAGES_URL') . '/image/202009/13f952e04c720a550193e5655534be86.jpg';
        } else {
            $avatar = config('env.IMAGES_URL') . $baseUser->headimg;
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'data' => [
                'phone' => strval($phone),
                'avatar' => $avatar,
                'nickname' => $baseUser->nickname,
            ],
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.user.register/1.0.0', $paratms);
        if ($res['body']['code'] != 0) {
            $this->err_msg = $res['body']['msg'];
            return $res['body']['msg'];
        }

        if (empty($res['body']['data']['user_id'])) {
            return 'user_id为空';
        }
        try {
            $xe_user_id = $res['body']['data']['user_id'];
            $XeUser = XeUser::query()->where('xe_user_id', $xe_user_id)->first();
            if (!$XeUser) {
                $XeUser = new XeUser();
                $XeUser->xe_user_id = $xe_user_id;
                $XeUser->avatar = $avatar;
                $XeUser->phone = $phone;
                $XeUser->nickname = $baseUser->nickname;
                $XeUser->save();
            }
        } catch (\Exception $e) {
            $errCode = $e->getCode();
            if ($errCode != 23000) {
                return $e->getMessage();
            }
        }
        return $res['body']['data'];
    }

    /**
     * @return string
     * 更新用户列表数据
     */
    public function sync_user_batch_get()
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        do {

            $redis_page_index_key = 'xe_sync_user_batch_get_page_index';
            $page_index = Redis::get($redis_page_index_key) ?? '';
            $page_size = 50;
            $paratms = [
                'access_token' => $this->get_token(),
                'page_size' => intval($page_size),
            ];

            if ($page_index) {
                $paratms['es_skip'] = json_decode($page_index, true);
            }

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.user.batch.get/2.0.0', $paratms);
            var_dump($paratms);

            if ($res['body']['code'] != 0) {
                $this->err_msg = $res['body']['msg'];
                return $this->err_msg;
            }

            $return_list = $res['body']['data']['list'] ?? [];

            if (empty($return_list)) {
                Redis::set($redis_page_index_key, '');
                return false;
            }

            $last = $return_list[count($return_list) - 1];
            if (!empty($last['es_skip'])) {
                Redis::set($redis_page_index_key, json_encode($last['es_skip']));
            }

            foreach ($return_list as $user) {
                var_dump($user['user_id']);
                try {
                    $XeUser = XeUser::query()->where('xe_user_id', $user['user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                    }
                    $XeUser->xe_user_id = $user['user_id'];
                    $XeUser->wx_union_id = $user['wx_union_id'];
                    $XeUser->wx_open_id = $user['wx_open_id'];
                    $XeUser->wx_app_open_id = $user['wx_app_open_id'];
                    $XeUser->nickname = $user['user_nickname'];
                    $XeUser->user_created_at = $user['user_created_at'];
                    $XeUser->avatar = $user['avatar'];
                    $XeUser->phone = $user['bind_phone'];
                    $XeUser->save();
                } catch (\Exception $e) {
                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }
            }

        } while ($return_list);

    }

    /**
     * 获取客户详情列表
     * 五分钟一次 todo
     */
    public function sync_user_info($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }
        $redis_page_index_key = 'sync_user_info_user_ids';
        if ($is_init) {
            $user_id_list = XeUser::query()->where('is_sync', 1)->pluck('xe_user_id')->toArray();
            if (empty($user_id_list)) {
                return false;
            }
            $user_id_list_arr = array_chunk($user_id_list, 50);
            Redis::del($redis_page_index_key);
            foreach ($user_id_list_arr as $user_ids) {
                Redis::rpush($redis_page_index_key, json_encode($user_ids));
            }
            return false;
        }


        for ($i = 1; $i <= 1000; $i++) {

            $user_ids = json_decode(Redis::lpop($redis_page_index_key), true);
            if (empty($user_ids)) {
                return false;
            }

            $page_index = 1;
            $page_size = 50;
            $paratms = [
                'user_id_list' => $user_ids,
                'access_token' => $this->get_token(),
                'page' => intval($page_index),
                'page_size' => intval($page_size),
            ];

            var_dump($paratms);

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.user.batch_by_user_id.get/1.0.0', $paratms);

            DB::table('nlsg_log_info')->insert([
                'url' => 'xe.user.batch_by_user_id.get',
                'line' => $res['body']['code'],
                'parameter' => json_encode($paratms),
//                'message'       =>  json_encode($res),
                'created_at' => date('Y-m-d H:i:s', time())
            ]);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }

                $this->err_msg = $res['body']['msg'];
                return $res['body']['msg'];
            }

            $return_list = $res['body']['data']['list'] ?? [];

            foreach ($return_list as $user) {

                try {
                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $user['user_id'])->first();
                    if ($XeUser) {
                        $XeUser->avatar = $user['avatar'];
                        $XeUser->phone = $user['bind_phone'];
                        $XeUser->phone_collect = $user['collect_phone'];
                        $XeUser->user_created_at = $user['user_created_at'];
                        $XeUser->nickname = $user['user_nickname'];
                        $XeUser->wx_union_id = $user['wx_union_id'];
                        $XeUser->wx_open_id = $user['wx_open_id'];
                        $XeUser->wx_app_open_id = $user['wx_app_open_id'];
                        $XeUser->is_sync = 2;
                        $XeUser->sync_time = times();
                        $XeUser->save();

                    }
                } catch (\Exception $e) {
                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }
            }
        }

    }

    /**
     * 获取推广员列表
     * 5分钟一次 todo
     */
    public function sync_distributor_list($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        for ($i = 1; $i <= 1000; $i++) {

            $redis_page_index_key = 'xe_get_distributor_list_page_index';
            $page_index = Redis::lpop($redis_page_index_key);
            if ($is_init) {
                $page_index = 1;
            }

            if (empty($page_index)) {
                return false;
            }

            $page_size = 50;
            $paratms = [
                'access_token' => $this->get_token(),
                'page_index' => intval($page_index),
                'page_size' => intval($page_size),
            ];

            var_dump($paratms);
            $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.list.get/1.0.0', $paratms);
            DB::table('nlsg_log_info')->insert([
                'url' => 'xe.distributor.list.get',
                'line' => $res['body']['code'],
                'parameter' => json_encode($paratms),
                'message' => $res['body']['data']['count'] ?? 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ]);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }
                $this->err_msg = $res['body']['msg'];
                return $res['body']['msg'];
            }

            $return_list = $res['body']['data']['return_list'] ?? [];

            if (empty($return_list)) {
                return false;
            } else {
                if ($is_init) {
                    //清除过期数据
                    XeDistributor::query()->where('refresh_time','<=',date("Y-m-d H:i:s",strtotime("-2 hour")))->delete();
                    Redis::del($redis_page_index_key);
                    $count = $res['body']['data']['count'];
                    $total_page = ceil($count / $page_size) + 1;
                    for ($i = 2; $i <= $total_page; $i++) {
                        var_dump($i);
                        Redis::rpush($redis_page_index_key, $i);
                    }
                }
            }

            foreach ($return_list as $distributor) {

                var_dump($distributor['user_id']);
                try {
                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $distributor['user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                        $XeUser->xe_user_id = $distributor['user_id'];
                        $XeUser->avatar = $distributor['avatar'];
                        $XeUser->nickname = $distributor['nickname'];
                        $XeUser->is_sync = 1;
                        $XeUser->save();
                    }
                } catch (\Exception $e) {
                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }

                try {

                    //保存推广员
                    $XeDistributor = XeDistributor::query()->where('xe_user_id', $distributor['user_id'])->first();
                    if (!$XeDistributor) {
                        $XeDistributor = new XeDistributor();
                    }

                    $XeDistributor->xe_user_id = $distributor['user_id'];
                    $XeDistributor->nickname = $distributor['nickname'];
                    $XeDistributor->level = $distributor['level'];
                    $XeDistributor->group_name = $distributor['group_name'];
                    $XeDistributor->group_id = $distributor['group_id'];
                    $XeDistributor->avatar = $distributor['avatar'];
                    $XeDistributor->refresh_time = times();
                    $XeDistributor->status = 1;
                    $XeDistributor->is_sync_customer   = 1;
                    $XeDistributor->save();

                } catch (\Exception $e) {

                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }
            }

            if ($is_init) {
                return false;
            }

        }
    }

    /**
     * 推广员客户列表
     * 一小时一次 todo
     */
    public function sync_distributor_customer_list($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        //获取推广员列表
        if ($is_init) {
            $redis_page_index_key = 'xe_sync_distributor_customer_list_page_index';
            Redis::del($redis_page_index_key);
            $XeDistributorList = XeDistributor::query()->where('is_sync_customer', 1)->get();
            foreach ($XeDistributorList as $k=>$XeDistributor) {
                var_dump($k);
                Redis::rpush($redis_page_index_key, json_encode(['xe_user_id' => $XeDistributor->xe_user_id, 'page_index' => 1]));
            }

        } else {

            $this->do_distributor_customer_list();

        }

    }


    public function do_distributor_customer_list($xe_user_id = '')
    {

        $redis_page_index_key = 'xe_sync_distributor_customer_list_page_index';

        for ($i = 1; $i <= 1000; $i++) {

            $page_index = Redis::lpop($redis_page_index_key);
            if ($page_index) {
                $page_index_arr = json_decode($page_index, true);
                $xe_user_id = $page_index_arr['xe_user_id'] ?? 0;
                $page_index = $page_index_arr['page_index'] ?? 0;
            }

            if (empty($xe_user_id)) {
                return false;
            }
            if (empty($page_index)) {
                return false;
            }

            $page_size = 100;
            $paratms = [
                'access_token' => $this->get_token(),
                'user_id' => $xe_user_id,
                'page_index' => intval($page_index),
                'page_size' => intval($page_size),
            ];

            var_dump($paratms);

            $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.sub_customer/1.0.0', $paratms);
            DB::table('nlsg_log_info')->insert([
                'url' => 'xe.distributor.member.sub_customer',
                'line' => $res['body']['code'],
                'parameter' => json_encode($paratms),
                'created_at' => date('Y-m-d H:i:s', time())
            ]);

            if ($res['body']['code'] != 0) {

                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }

                Redis::rpush($redis_page_index_key, json_encode(['xe_user_id' => $xe_user_id, 'page_index' => $page_index]));

                $this->err_msg = $res['body']['msg'];

                return $this->err_msg;
            }

            $return_list = $res['body']['data']['list'] ?? [];

            if ($page_index==1 && $return_list) {

                $count = $res['body']['data']['count'];
                $total_page = ceil($count / $page_size) + 1;
                for ($i = 2; $i <= $total_page; $i++) {
                    var_dump($i);
                    Redis::rpush($redis_page_index_key, json_encode(['xe_user_id' => $xe_user_id, 'page_index' => $i]));
                }

            }

            foreach ($return_list as $customer) {
                try {
                    //保存小鹅通用户
                    $XeUser = XeUser::query()->where('xe_user_id', $customer['sub_user_id'])->first();
                    if (!$XeUser) {
                        $XeUser = new XeUser();
                        $XeUser->xe_user_id = $customer['sub_user_id'];
                        $XeUser->avatar = $customer['wx_avatar'];
                        $XeUser->nickname = $customer['wx_nickname'];
                        $XeUser->is_sync = 1;
                        $XeUser->save();
                    }
                } catch (\Exception $e) {
                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }
                try {
                    //保存推广员客户
                    $XeDistributorCustomer = XeDistributorCustomer::query()->where('xe_user_id', $xe_user_id)->where('sub_user_id', $customer['sub_user_id'])->first();
                    if (!$XeDistributorCustomer) {
                        $XeDistributorCustomer = new XeDistributorCustomer();
                    }

                    $XeDistributorCustomer->xe_user_id = $xe_user_id;
                    $XeDistributorCustomer->sub_user_id = $customer['sub_user_id'];
                    $XeDistributorCustomer->wx_nickname = $customer['wx_nickname'];
                    $XeDistributorCustomer->wx_avatar = $customer['wx_avatar'];
                    $XeDistributorCustomer->order_num = $customer['order_num'];
                    $XeDistributorCustomer->sum_price = $customer['sum_price'];
                    $XeDistributorCustomer->bind_time = $customer['bind_time'];
                    $XeDistributorCustomer->status = $customer['status'];
                    $XeDistributorCustomer->status_text = $customer['status_text'];
                    $XeDistributorCustomer->remain_days = $customer['remain_days'];
                    $XeDistributorCustomer->expired_at = $customer['expired_at'];
                    $XeDistributorCustomer->is_editable = $customer['is_editable'];
                    $XeDistributorCustomer->is_anonymous = $customer['is_anonymous'] ? 1 : 0;
                    $XeDistributorCustomer->refresh_time = times();
                    $XeDistributorCustomer->save();
                } catch (\Exception $e) {
                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }
            }

            var_dump('end');

        }

    }



    /**
     * 同步订单详情
     */
    public function sync_order_detail($is_init = 0)
    {

        if (!$this->access_token) {
            return $this->err_msg;
        }

        $redis_page_index_key = 'sync_order_detail_order_ids';
        if ($is_init) {
            $list = XeOrder::query()->where('share_type', 5)->where('order_state', 4)->whereIn('settle_state',[0,1])->get()->toArray();
            if (empty($list)) {
                return false;
            }
            Redis::del($redis_page_index_key);
            foreach ($list as $order) {
                var_dump($order['order_id']);
                Redis::rpush($redis_page_index_key, $order['order_id']);
            }
            return false;
        }

        for ($i = 1; $i <= 1000; $i++) {

            $order_id = Redis::lpop($redis_page_index_key);
            if (empty($order_id)) {
                return false;
            }

            $paratms = [
                'access_token' => $this->get_token(),
                'order_id' => $order_id,
            ];
            var_dump($paratms);
            $res = self::curlPost('https://api.xiaoe-tech.com/xe.order.detail/1.0.0', $paratms);
//            DB::table('nlsg_log_info')->insert([
//                'url' => 'xe.order.detail',
//                'line' => $res['body']['code'],
//                'parameter' => json_encode($paratms),
//                'created_at' => date('Y-m-d H:i:s', time())
//            ]);

            if ($res['body']['code'] != 0) {
                if ($res['body']['code'] == 2008) {
                    $this->get_token(1);
                }

                $this->err_msg = $res['body']['msg'];
                return $res['body']['msg'];
            }

            $distribute_info = $res['body']['data']['distribute_info'] ?? [];

            if ($distribute_info) {

                var_dump($distribute_info);
                try {
                    //保存分销
                    $XeOrderDistribute = XeOrderDistribute::query()->where('order_id', $order_id)->first();
                    if (!$XeOrderDistribute) {
                        $XeOrderDistribute = new XeOrderDistribute();
                    }

                    $XeOrderDistribute->order_id = $order_id;
                    $XeOrderDistribute->share_user_id = $distribute_info['share_user_id'];
                    $XeOrderDistribute->share_user_nickname = $distribute_info['share_user_nickname'];
                    $XeOrderDistribute->distribute_price = $distribute_info['distribute_price'];
                    $XeOrderDistribute->superior_distribute_user_id = $distribute_info['superior_distribute_user_id'];
                    $XeOrderDistribute->superior_distribute_user_nickname = $distribute_info['superior_distribute_user_nickname'];
                    $XeOrderDistribute->superior_distribute_price = $distribute_info['superior_distribute_price'];
                    $XeOrderDistribute->save();

                } catch (\Exception $e) {

                    $errCode = $e->getCode();
                    if ($errCode != 23000) {
                        return $e->getMessage();
                    }
                }

            }

        }

    }

    public function sync_user_userid()
    {

        //同步user_id
        $list = XeUser::query()->from(XeUser::DB_TABLE . ' as XeUser')
            ->select('XeUser.*', 'User.unionid', 'User.id as base_user_id')
            ->leftJoin('nlsg_user as User', 'User.phone', '=', 'XeUser.phone')
            ->whereRaw(DB::raw("XeUser.phone <> '' and XeUser.user_id=0 and User.id is not null "))
            ->orderBy('XeUser.id','asc')
            ->get();

        foreach ($list as $k=>$XeUser) {

            $XeUser->user_id=$XeUser->base_user_id;
            $XeUser->save();

            $user_id = $XeUser->base_user_id;
            $unionid = $XeUser->unionid;

            var_dump($k);
            var_dump($XeUser->id);
            var_dump($user_id);
            var_dump($unionid);

            if ($unionid) {

                //如果没有客服
                if (!DB::table('crm_live_user_waiter')->where([
                    "user_id" => $user_id,
                ])->count()) {

                    $old_check = DB::table('nlsg_user_wechat as uw')
                        ->join('crm_live_waiter_wechat as ww',
                            'uw.follow_user_userid', '=', 'ww.follow_user_userid'
                        )
                        ->where('uw.unionid', '=', $unionid)
                        ->select(['ww.follow_user_userid', 'uw.follow_user_createtime as bind_admin_time', 'ww.admin_id'])
                        ->first();

                    if ($old_check) {
                        $savedata = [
                            'admin_id' => $old_check->admin_id,
                            'user_id' => $user_id,
                            'follow_user_userid' => $old_check->follow_user_userid,
                        ];
                        var_dump($savedata);
                        DB::table('crm_live_user_waiter')
                            ->insert($savedata);
                    }
                }

            }


        }
    }

    /**
     * 新增推广员
     */
    public function distributor_member_add($phone = '', $user_id = '', $params = [])
    {
        if (empty($phone) && empty($user_id)) {
            return '参数错误';
        }

        if ($phone) {
            $res = $this->user_register($phone);
            if (!checkRes($res)) {
                return $res;
            }
            $user_id = $res['user_id'] ?? '';
        }

        if (!$user_id) {
            return '客户不存在';
        }

        $XeDistributor = XeDistributor::query()->where('xe_user_id', $user_id)->first();
        if ($XeDistributor) {
            return ['user_id' => $user_id, 'is_exist' => 1, 'created_at' => $XeDistributor->created_at];
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id'      => $user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.add/1.0.0', $paratms);
        if ($res['body']['code'] != 0 && $res['body']['code'] != 20003) {
            $this->err_msg = $res['body']['msg'];
            return $res['body']['msg'];
        }

        $XeDistributor             = new XeDistributor();
        $XeDistributor->xe_user_id = $user_id;
        $XeDistributor->level      = 1;
        $XeDistributor->group_id   = 0;
        $XeDistributor->group_name = '合伙人';
        $XeDistributor->source     = $params['source'] ?? 0;
        $XeDistributor->admin_id   = $params['admin_id'] ?? 0;
        $XeDistributor->is_sync_customer   = 1;
        $XeDistributor->save();

        $is_exist = 0;
        if ($res['body']['code'] == 20003) {
            $is_exist = 1;
        }

        return ['user_id' => $user_id, 'is_exist' => $is_exist, 'created_at' => date('Y-m-d H:i:s')];

    }

    /**
     * 批量添加推广员
     */
    public function distributor_member_batch_add($phone_arr, $parent_phone = '')
    {

        if (count($phone_arr) > 100) {
            return '最多添加100个';
        }

        $user_id_arr = [];
        foreach ($phone_arr as $phone) {
            $res = $this->distributor_member_add($phone);
            if (!checkRes($res)) {
                return $res;
            }
            $user_id_arr[] = $res['user_id'];
        }

        if (empty($parent_phone)) {
            return $user_id_arr;
        }

        $res = $this->distributor_member_add($parent_phone);
        if (!checkRes($res)) {
            return $res;
        }
        $parent_user_id = $res['user_id'];
        foreach ($user_id_arr as $user_id) {
            $res = $this->distributor_superior_set($parent_user_id, $user_id);
            if (!checkRes($res)) {
                return $res;
            }
        }

        return true;
    }

    /**
     * 推广员上级
     */
    public function distributor_superior_set($parent_user_id, $user_id)
    {


        $XeDistributor = XeDistributor::query()->where('xe_user_id', $user_id)->first();
        if (!$XeDistributor) {
            return '推广员不已存在';
        }

        $XeParentDistributor = XeDistributor::query()->where('xe_user_id', $parent_user_id)->first();
        if (!$XeParentDistributor) {
            return '上级推广员不已存在';
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $user_id,
            'parent_user_id' => $parent_user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.superior.set/1.0.0', $paratms);
        if ($res['body']['code'] != 0) {
            $this->err_msg = $res['body']['msg'];
            return false;
        }

        $XeDistributor->xe_parent_user_id = $parent_user_id;
        $XeDistributor->save();

        return true;

    }

    /**
     * 推广员绑定客户
     */
    public function distributor_member_bind($parent_user_id, $user_id = '', $phone = '')
    {

        if ($phone) {
            $res = $this->user_register($phone);
            if (!checkRes($res)) {
                return $res;
            }
            $user_id = $res['user_id'] ?? '';
        }

        if (empty($user_id)) {
            return ['code' => false, 'msg' => '客户不存在'];
        }

        $XeDistributor = XeDistributor::query()->where('xe_user_id', $parent_user_id)->first();
        if (!$XeDistributor) {
            return ['code' => false, 'msg' => '推广员不存在'];
        }

        $XeDistributorCustomer = XeDistributorCustomer::query()
            ->where('xe_user_id', $parent_user_id)
            ->where('sub_user_id', $user_id)
            ->first();

        if ($XeDistributorCustomer) {
            return ['code' => true, 'msg' => '成功', 'created_at' => $XeDistributorCustomer->bind_time];
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $user_id,
            'parent_user_id' => $parent_user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.bind/1.0.0', $paratms);
        if ($res['body']['code'] != 0) {
            $this->err_msg = $res['body']['msg'];
            return ['code' => false, 'msg' => $this->err_msg];
        }

        $XeDistributorCustomer = new XeDistributorCustomer();
        $XeDistributorCustomer->xe_user_id = $parent_user_id;
        $XeDistributorCustomer->sub_user_id = $user_id;
        $XeDistributorCustomer->status = 1;
        $XeDistributorCustomer->status_text = '绑定中';
        $XeDistributorCustomer->remain_days = 365;
        $XeDistributorCustomer->bind_time = times();
        $XeDistributorCustomer->expired_at = times(strtotime('+1 years'));
        $XeDistributorCustomer->refresh_time = times();
        $XeDistributorCustomer->save();

        return ['code' => true, 'msg' => '成功', 'created_at' => times()];

    }

    /**
     * 修改/解除绑定关系
     */
    public function distributor_member_change($user_id, $former_parent_user_id, $parent_user_id = '')
    {

        if (empty($user_id)) {
            return '客户不存在';
        }

        $XeDistributor = XeDistributor::query()->where('xe_user_id', $former_parent_user_id)->first();
        if (!$XeDistributor) {
            return '推广员不存在';
        }

        $XeOldDistributorCustomer = XeDistributorCustomer::query()
            ->where('xe_user_id', $former_parent_user_id)
            ->where('sub_user_id', $user_id)
            ->where('status', 1)
            ->first();

        if (!$XeOldDistributorCustomer) {
            return '原推广员未绑定客户';
        }

        $paratms = [
            'access_token' => $this->get_token(),
            'user_id' => $user_id,
            'parent_user_id' => $parent_user_id,
            'former_parent_user_id' => $former_parent_user_id,
        ];

        $res = self::curlPost('https://api.xiaoe-tech.com/xe.distributor.member.change/1.0.0', $paratms);
        if ($res['body']['code'] != 0) {
            $this->err_msg = $res['body']['msg'];
            return $this->err_msg;
        }

        if ($parent_user_id) {
            $XeDistributorCustomer = new XeDistributorCustomer();
            $XeDistributorCustomer->xe_user_id = $parent_user_id;
            $XeDistributorCustomer->sub_user_id = $user_id;
            $XeDistributorCustomer->status = 1;
            $XeDistributorCustomer->status_text = '绑定中';
            $XeDistributorCustomer->remain_days = 365;
            $XeDistributorCustomer->bind_time = times();
            $XeDistributorCustomer->expired_at = times(strtotime('+1 years'));
            $XeDistributorCustomer->refresh_time = times();
            $XeDistributorCustomer->save();
        }

        $XeOldDistributorCustomer->remain_days = 0;
        $XeOldDistributorCustomer->status = 0;
        $XeOldDistributorCustomer->status_text = '已解绑';
        $XeOldDistributorCustomer->save();

        return true;

    }


    /**
     * 发送get请求
     * @param
     * @return
     */
    public static function curlGet($url, $queryparas = array(), $timeout = 2, $header = array(), $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }

    /**
     * 发送post请求
     * @param
     * @return
     */
    public static function curlPost($url, $postdata = array(), $queryparas = array(), $header = array(), $timeout = 20, $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
        if (!empty($header)) {
            $header_str = implode('', $header);
            if (strpos($header_str, "application/x-www-form-urlencoded") !== false) {
                $postdata = http_build_query($postdata);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            }
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }

    /**
     * 发送Del请求
     * @param
     * @return
     */
    public static function curlDel($url, $queryparas = array(), $postdata = array(), $header = array(), $timeout = 2, $proxy = array())
    {
        if (!empty($queryparas)) {
            if (is_array($queryparas)) {
                $postData = http_build_query($queryparas);
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $postData;
            } else if (is_string($queryparas)) {
                $url .= strpos($url, '?') ? '' : '?';
                $url .= $queryparas;
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if (!empty($header) && is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($proxy)) {
            curl_setopt($ch, CURLOPT_PROXYAUTH, 1);
            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 0);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

        $output = curl_exec($ch);
        if (is_array(json_decode($output, true))) {
            $output = json_decode($output, true);
        }

        $result['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body'] = $output;

        curl_close($ch);
        return $result;
    }
}
