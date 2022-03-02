<?php


namespace App\Servers;

use App\Http\Controllers\Api\V4\CreatePosterController;
use App\Models\Area;
use App\Models\ChannelOrder;
use App\Models\ConfigModel;
use App\Models\CreatePost;
use App\Models\ExpressCompany;
use App\Models\ExpressInfo;
use App\Models\History;
use App\Models\Live;
use App\Models\LiveCountDown;
use App\Models\LiveOnlineUser;
use App\Models\MallGoods;
use App\Models\Order;
use App\Models\PayRecordDetail;
use App\Models\Subscribe;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\VipRedeemUser;
use App\Models\VipUser;
use App\Models\VipUserBind;
use App\Models\VipWorksList;
use App\Models\WorksListOfSub;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class removeDataServers
{
    public function removeGoods()
    {
//        $copy_flag = '_copy1';
        $copy_flag = '';

        if (0) {
            //商品评论
            $old_comment = DB::connection('mysql_old_zs')
                ->table('nlsg_mall_comment')
                ->where('id', '<=', 1808)
                ->get()->toArray();
            $comment_data = [];
            foreach ($old_comment as $v) {
                $temp_comment = [];
                $temp_comment['id'] = $v->id;
                $temp_comment['user_id'] = $v->user_id;
                $temp_comment['content'] = $v->content;
                $temp_comment['picture'] = $v->picture;
                $temp_comment['order_id'] = $v->order_id;
                $temp_comment['order_detail_id'] = $v->order_detail_id;
                $temp_comment['goods_id'] = $v->goods_id;
                $temp_comment['sku_number'] = $v->sku_number;
                $temp_comment['star'] = $v->star;
                $temp_comment['status'] = $v->status;
                $temp_comment['reply_comment'] = $v->reply_comment;
                $temp_comment['reply_user_id'] = $v->reply_user_id;
                if (!empty($v->reply_time)) {
                    $temp_comment['replied_at'] = date('Y-m-d H:i:s');
                } else {
                    $temp_comment['replied_at'] = null;
                }
                $comment_data[] = $temp_comment;
            }
            $r5 = DB::connection('mysql_new_zs')
                ->table('nlsg_mall_comment' . $copy_flag)->insert($comment_data);
            dd($r5);
        }

        if (0) {
            //单独补全sku
            $list = DB::connection('mysql_old_zs')
                ->table('nlsg_mall_sku')
                ->get()
                ->toArray();
            $sku_data = [];
            $sku_value_data = [];
            foreach ($list as $v) {
                $check = DB::table('nlsg_mall_sku')
                    ->where('sku_number', '=', $v->sku_number)
                    ->first();
                if ($check) {
                    continue;
                }
                $temp_sku = [];
                $temp_sku['id'] = $v->id;
                $temp_sku['goods_id'] = $v->goods_id;
                $temp_sku['sku_number'] = $v->sku_number;
                $temp_sku['picture'] = $v->picture;
                $temp_sku['original_price'] = $v->original_price;
                $temp_sku['price'] = $v->price;
                $temp_sku['cost'] = $v->cost;
                $temp_sku['promotion_cost'] = $v->promotion_cost;
                $temp_sku['stock'] = $v->stock;
                $temp_sku['warning_stock'] = $v->warning_stock;
                $temp_sku['status'] = $v->status;
                $temp_sku['erp_enterprise_code'] = $v->erp_enterprise_code ?? '';
                $temp_sku['erp_goods_code'] = $v->erp_goods_code ?? '';
                $sku_data[] = $temp_sku;

                $temp_sku_json = $v->sku_json;
                $temp_sku_json = json_decode($temp_sku_json);
                foreach ($temp_sku_json as $kk => $vv) {
                    $temp_sku_value = [];
                    $temp_sku_value['goods_id'] = $v->goods_id;
                    $temp_sku_value['sku_id'] = $v->id;
                    $temp_sku_value['key_name'] = $kk;
                    $temp_sku_value['value_name'] = $vv;
                    $sku_value_data[] = $temp_sku_value;
                }
            }

            $r2 = DB::table('nlsg_mall_sku')->insert($sku_data);
            $r3 = DB::table('nlsg_mall_sku_value')->insert($sku_value_data);
            dd([$r2, $r3]);
        }

        if (0) {
            //商品信息
            $old_picture = DB::connection('mysql_old')
                ->table('nlsg_mall_picture')
                ->where('status', '=', 1)
                ->get()->toArray();
            $picture_data = [];
            foreach ($old_picture as $v) {
                $temp_picture = [];
                $temp_picture['url'] = $v->url;
                $temp_picture['goods_id'] = $v->goods_id;
                $temp_picture['status'] = 1;
                $temp_picture['is_main'] = $v->is_main;
                $picture_data[] = $temp_picture;
            }

            $old_sku = DB::connection('mysql_old')
                ->table('nlsg_mall_sku')
                ->groupBy('goods_id', 'status', 'sku_json')
                ->get()
                ->toArray();

            $sku_data = [];
            $sku_value_data = [];
            foreach ($old_sku as $v) {
                $temp_sku = [];
                $temp_sku['id'] = $v->id;
                $temp_sku['goods_id'] = $v->goods_id;
                $temp_sku['sku_number'] = $v->sku_number;
                $temp_sku['picture'] = $v->picture;
                $temp_sku['original_price'] = $v->original_price;
                $temp_sku['price'] = $v->price;
                $temp_sku['cost'] = $v->cost;
                $temp_sku['promotion_cost'] = $v->promotion_cost;
                $temp_sku['stock'] = $v->stock;
                $temp_sku['warning_stock'] = $v->warning_stock;
                $temp_sku['status'] = $v->status;
                $temp_sku['erp_enterprise_code'] = $v->erp_enterprise_code;
                $temp_sku['erp_goods_code'] = $v->erp_goods_code;
                $sku_data[] = $temp_sku;

                $temp_sku_json = $v->sku_json;
                $temp_sku_json = json_decode($temp_sku_json);
                foreach ($temp_sku_json as $kk => $vv) {
                    $temp_sku_value = [];
                    $temp_sku_value['goods_id'] = $v->goods_id;
                    $temp_sku_value['sku_id'] = $v->id;
                    $temp_sku_value['key_name'] = $kk;
                    $temp_sku_value['value_name'] = $vv;
                    $sku_value_data[] = $temp_sku_value;
                }

            }

            $old_goods = DB::connection('mysql_old')
                ->table('nlsg_mall_goods')
                ->get()->toArray();
            $goods_data = [];
            foreach ($old_goods as $v) {
                $temp = [];
                $temp['id'] = $v->id;
                switch ($v->category_id) {
                    case 40:
                        $temp['category_id'] = 60;
                        break;
                    case 41:
                        $temp['category_id'] = 61;
                        break;
                    case 42:
                    case 43:
                        $temp['category_id'] = 62;
                        break;
                    case 45:
                    case 46:
                        $temp['category_id'] = 63;
                        break;
                    case 47:
                    case 48:
                        $temp['category_id'] = 64;
                        break;
                    case 53:
                    case 56:
                        $temp['category_id'] = 71;
                        break;
                    case 58:
                    case 51:
                        $temp['category_id'] = 68;
                        break;
                    default:
                        $temp['category_id'] = 0;
                }
                $temp['name'] = $v->name;
                $temp['subtitle'] = $v->subtitle;
                $temp['picture'] = $v->picture;
                $temp['freight_id'] = 14;
                $temp['number'] = $v->number;
                $temp['original_price'] = $v->original_price;
                $temp['price'] = $v->price;
                $temp['sales_num_virtual'] = 0;
                $temp['sales_num'] = $v->sales_num;
                $temp['keywords'] = '';
                $temp['content'] = $v->content;
                $temp['view_num'] = $v->view_num;
                $temp['collection_num'] = $v->collection_num;
                $temp['status'] = $v->status;
                $goods_data[] = $temp;
            }
            $r1 = DB::table('nlsg_mall_goods' . $copy_flag)->insert($goods_data);
            $r2 = DB::table('nlsg_mall_sku' . $copy_flag)->insert($sku_data);
            $r3 = DB::table('nlsg_mall_sku_value' . $copy_flag)->insert($sku_value_data);
            $r4 = DB::table('nlsg_mall_picture' . $copy_flag)->insert($picture_data);
        }
    }

    //修改商品价格和规格价格不匹配的临时方法
    public function updateGoodsSkuPrice()
    {
        $list = MallGoods::query()
            ->with(['sku_list'])
            ->select(['id', 'name', 'original_price', 'price'])
            ->get()->toArray();

        $res = [];

        foreach ($list as $v) {
            if (empty($v['sku_list'])) {
                continue;
            }
            $op = $v['original_price'];
            $p = $v['price'];

            $op_list = array_column($v['sku_list'], 'original_price');
            $p_list = array_column($v['sku_list'], 'price');
            sort($op_list);
            sort($p_list);

            if (!in_array($op, $op_list) || !in_array($p, $p_list)) {
                $g = MallGoods::find($v['id']);
                $temp_res = [];
                $temp_res['goods_id'] = $v['id'];
                $new_op = array_shift($op_list);
                $new_p = array_shift($p_list);
                $temp_res['update'] = $g->original_price . '-' . $new_op . '|' . $g->price . '-' . $new_p;
                $res[] = $temp_res;
                $g->original_price = $new_op;
                $g->price = $new_p;
                $g->save();
            }
        }

        dd($res);
    }

    //批量添加机器人
    public function addRobot()
    {
        //id 8000-11000  两千个虚拟用户位
        $begin_num = [137, 186, 139, 151, 159, 188, 131, 189, 138, 139];
        $i = 8000;
        while ($i <= 11000) {
            $now = date('Y-m-d H:i:s');
            $temp_num = rand(10000000, 99999999);
            $num = $begin_num[rand(0, 3)] . $temp_num;
            $num = substr_replace($num, '****', 3, 4);

//            $model = new User();
//            $model->id = $i;
//            $model->phone = $i;
//            $model->nickname = $num;
//            $model->created_at = $now;
//            $model->updated_at = $now;
//            $model->is_robot = 1;
//            $res = $model->save();

            $temp_data = [];
            $temp_data['id'] = $i;
            $temp_data['phone'] = $i;
            $temp_data['nickname'] = $num;
            $temp_data['created_at'] = $temp_data['updated_at'] = $now;
            $temp_data['is_robot'] = 1;
            $res = DB::connection('mysql_new_zs')
                ->table('nlsg_user')->insert($temp_data);
            if ($res) {
                $i++;
            }
        }
    }

    //发货记录迁移

    public function vip()
    {
        $list = VipUser::query()
            ->where('level', '=', 2)
            ->where('status', '=', 1)
            ->where('is_default', '=', 1)
            ->where('is_open_360', '=', 0)
            ->with(['orderHistory', 'codeHistory'])
            ->get()->toArray();

        foreach ($list as $v) {
            if (!empty($v['order_history']) || !empty($v['code_history'])) {
                $update_data = [];
                $update_data['is_open_360'] = 1;

                $begin_time = '2020-09-01';
                if (!empty($v['order_history']['created_at']) && $begin_time < $v['order_history']['created_at']) {
                    $begin_time = $v['order_history']['created_at'];
                }

                if (!empty($v['code_history']['updated_at']) && $begin_time < $v['code_history']['updated_at']) {
                    $begin_time = $v['code_history']['updated_at'];
                }
                $update_data['time_begin_360'] = $begin_time;
                $update_data['time_end_360'] = date('Y-m-d 23:59:59', strtotime(" +1 years", strtotime($begin_time)));

                DB::connection('mysql_new_zs')
                    ->table('nlsg_vip_user')->where('id', '=', $v['id'])
                    ->update($update_data);
            }
        }
    }

    public function addressExpress()
    {
        $this->removeAddress();//迁移收货地址
        $this->removeExpress();//迁移快递信息
    }

    //地址和快递信息

    public function removeAddress()
    {
        $list = DB::connection('mysql_old_zs')
            ->table('nlsg_mall_address')
            ->where('id', '<=', 4998)
            ->where('is_del', '=', 0)
            ->get()->toArray();

        $area = Area::get()->toArray();

        $add_data = [];
        foreach ($list as &$v) {
            $v->province_code = 0;
            $v->city_code = 0;
            $v->county_code = 0;

            foreach ($area as $vv) {
                if ($v->province == $vv['name'] || $v->province == $vv['fullname']) {
                    $v->province_code = $vv['id'];
                }
                if ($v->city == $vv['name'] || $v->city == $vv['fullname']) {
                    $v->city_code = $vv['id'];
                }
                if ($v->county == $vv['name'] || $v->county == $vv['fullname']) {
                    $v->county_code = $vv['id'];
                }
            }
            if ($v->county_code == 0) {
                $v->detail = $v->county . $v->detail;
            }

            $temp_data = [];
            $temp_data['id'] = $v->id;
            $temp_data['name'] = $v->name;
            $temp_data['phone'] = $v->phone;
            $temp_data['province'] = $v->province_code;
            $temp_data['city'] = $v->city_code;
            $temp_data['area'] = $v->county_code;
            $temp_data['user_id'] = $v->user_id;
            $temp_data['is_default'] = $v->is_default;
            $temp_data['details'] = $v->detail;
            $temp_data['created_at'] = date('Y-m-d H:i:s', $v->ctime);
            $add_data[] = $temp_data;
        }
        $add_data = array_chunk($add_data, 50);
        foreach ($add_data as $av) {
            DB::connection('mysql_new_zs')->table('nlsg_mall_address')->insert($av);
        }
    }

    public function removeExpress()
    {
        $express_data = ExpressCompany::query()->get()->toArray();

        $data = DB::connection('mysql_old_zs')
            ->table('nlsg_mall_order')
            ->where('id', '<=', 11752)
            ->where('express_company', '<>', '')
            ->where('express_number', '<>', '')
            ->select(['express_company', 'express_number', 'deliver_goods_time', 'receive_goods_time', 'ctime'])
            ->get()
            ->toArray();

        $add_data = [];
        foreach ($data as &$v) {
            foreach ($express_data as $vv) {
                if (strtolower($v->express_company) == strtolower($vv['code'])) {
                    $temp_add_data = [];

                    $temp_add_data['express_id'] = $vv['id'];
                    $temp_add_data['express_num'] = trim($v->express_number);


                    if (empty($v->deliver_goods_time)) {
                        $temp_add_data['created_at'] = date('Y-m-d H:i:s', $v->ctime);
                    } else {
                        $temp_add_data['created_at'] = date('Y-m-d H:i:s', $v->deliver_goods_time);
                    }

                    if (empty($v->receive_goods_time)) {
                        $temp_add_data['delivery_status'] = 1;
                        $temp_history = [
                            "number" => trim($v->express_number),
                            "type" => $vv['code'],
                            "typename" => $vv['name'],
                            "logo" => $vv['logo'],
                            "delivery_status" => 1,
                            "express_phone" => $vv['phone'],
                            "list" => [
                                [
                                    "time" => date('Y-m-d H:i:s', $v->deliver_goods_time),
                                    "status" => '商家已发货'
                                ]
                            ]
                        ];
                    } else {
                        $temp_add_data['delivery_status'] = 4;
                        $temp_history = [
                            "number" => trim($v->express_number),
                            "type" => $vv['code'],
                            "typename" => $vv['name'],
                            "logo" => $vv['logo'],
                            "delivery_status" => 1,
                            "express_phone" => $vv['phone'],
                            "list" => [
                                [
                                    "time" => date('Y-m-d H:i:s', $v->receive_goods_time),
                                    "status" => '客户已签收'
                                ],
                                [
                                    "time" => date('Y-m-d H:i:s', $v->deliver_goods_time),
                                    "status" => '商家已发货'
                                ]
                            ]
                        ];
                    }
                    $temp_add_data['history'] = json_encode($temp_history);
                    $add_data[] = $temp_add_data;
                }
            }
        }

        $add_data = array_chunk($add_data, 100);
        foreach ($add_data as $ad_v) {
            $res = DB::connection('mysql_new_zs')->table('nlsg_express_info')->insert($ad_v);
            var_dump($res);
        }

    }

    //商城订单迁移

    public function removeMallOrders()
    {
        set_time_limit(0);
        $i = 1;
        $w = true;
        //$flag = '_v3';
        $flag = '';
        while ($w) {
            $data = $this->getOrderData($i, 50, $flag);
            $i++;
            if ($data === false) {
                $w = false;
            }
        }
    }

    public function getOrderData($page = 1, $size = 50, $flag = '')
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);
        $begin_order_id = 11755;//11755

        $old_order = DB::connection('mysql_old_zs')
            ->table('nlsg_mall_order')
            ->where('id', '>', $begin_order_id)
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->orderBy('id', 'desc')
            ->get()->toArray();

        $old_id_list = array_column($old_order, 'id');

        $old_details = DB::connection('mysql_old_zs')
            ->table('nlsg_mall_order_detail')
            ->where('order_id', '>', $begin_order_id)
            ->whereIn('order_id', $old_id_list)
            ->get()
            ->toArray();

        foreach ($old_order as &$v) {
            $temp_details = [];
            foreach ($old_details as $vv) {
                if ($v->id == $vv->order_id) {
                    $temp_details[] = $vv;
                }
            }
            $v->details = $temp_details;
        }

        $order_data = [];
        $order_detail_data = [];
        $order_child_data = [];

        foreach ($old_order as $ov) {
            $temp_order = [];
            $temp_order['id'] = $ov->id;
            $temp_order['ordernum'] = $ov->ordernum;
            $temp_order['user_id'] = $ov->user_id;
            $temp_order['order_type'] = 1;
            $temp_order['status'] = $ov->status;
            $temp_order['cost_price'] = $ov->cost_price;
            $temp_order['freight'] = $ov->freight;
            $temp_order['vip_cut'] = $ov->vip_cut;
            $temp_order['coupon_id'] = $ov->coupon_id;
            $temp_order['coupon_money'] = $ov->coupon_money;
            $temp_order['coupon_freight_id'] = 0;
            $temp_order['special_price_cut'] = $ov->special_price_cut;
            $temp_order['price'] = $ov->price;
            $temp_order['pay_price'] = $ov->pay_price;
            if (!empty($ov->pay_time)) {
                $temp_order['pay_time'] = date('Y-m-d H:i:s', $ov->pay_time);
            } else {
                $temp_order['pay_time'] = null;
            }
            $temp_order['pay_type'] = $ov->pay_type;
            $temp_order['os_type'] = $ov->os_type;
            $temp_order['messages'] = $ov->messages;
            $temp_order['remark'] = $ov->remark;
            if ($ov->address_method) {
                $temp_order['post_type'] = 2;
            } else {
                $temp_order['post_type'] = 1;
            }
            $temp_order['address_id'] = 0;
            $temp_order['address_history'] = json_encode([
                'id' => 0,
                "name" => $ov->address_name,
                "phone" => $ov->address_phone,
                "details" => $ov->address_detail,
                "is_default" => 0,
                "province" => 0,
                "city" => 0,
                "area" => 0,
                "province_name" => $ov->address_province,
                "city_name" => $ov->address_city,
                "area_name" => $ov->address_county,
            ]);
            $temp_order['bill_type'] = $ov->bill_title_type;
            $temp_order['bill_title'] = $ov->bill_title;
            $temp_order['bill_number'] = $ov->bill_number;
            $temp_order['bill_format'] = $ov->bill_format;
            $temp_order['active_flag'] = $ov->active_flag;
            $temp_order['created_at'] = date('Y-m-d H:i:s', $ov->ctime);
            $temp_order['updated_at'] = $now_date;
            $temp_order['is_stop'] = $ov->is_stop;
            $temp_order['stop_by'] = $ov->stop_by;
            if (!empty($ov->stop_at)) {
                $temp_order['stop_at'] = date('Y-m-d H:i:s', $ov->stop_at);
            } else {
                $temp_order['stop_at'] = null;
            }
            $temp_order['stop_reason'] = $ov->stop_reason;
            $temp_order['is_del'] = $ov->is_del;
            if (!empty($ov->del_at)) {
                $temp_order['del_at'] = date('Y-m-d H:i:s', $ov->del_at);
            } else {
                $temp_order['del_at'] = null;
            }
            if (!empty($ov->receive_goods_time)) {
                $temp_order['receipt_at'] = date('Y-m-d H:i:s', $ov->receive_goods_time);
            } else {
                $temp_order['receipt_at'] = null;
            }
            $order_data[] = $temp_order;

            foreach ($ov->details as $odv) {
                $temp_details = [];
                $temp_details['id'] = $odv->id;
                $temp_details['order_id'] = $odv->order_id;
                $temp_details['order_child_id'] = $odv->order_child_id;
                $temp_details['user_id'] = $odv->user_id;
                $temp_details['status'] = $odv->status;
                $temp_details['goods_id'] = $odv->goods_id ?? 0;
                $temp_details['sku_number'] = $odv->sku_number ?? '';
                $temp_details['num'] = $odv->num ?? 1;
                if ($temp_details['num'] < 1) {
                    $temp_details['num'] = 1;
                }
                $temp_details['after_sale_used_num'] = 0;
                $temp_details['comment_id'] = $odv->comment_id ?? 0;
                $temp_details['inviter'] = $odv->twitter_id ?? 0;
                $temp_details['created_at'] = date('Y-m-d H:i:s', $odv->ctime);
                $temp_details['updated_at'] = $now_date;
                $temp_details['t_money'] = 0;
                $temp_details['special_price_type'] = 0;
                $temp_check_sku = DB::connection('mysql_new_zs')
                    ->table('nlsg_mall_sku')
                    ->where('sku_number', '=', $odv->sku_number)
                    ->first();

                $sku_json = json_decode($odv->sku_json, true);

                $temp_sku_json = [];

                if (is_array($sku_json)) {
                    foreach ($sku_json as $kk => $vv) {
                        $t = [];
                        $t['key_name'] = $kk;
                        $t['value_name'] = $vv;
                        $temp_sku_json[] = $t;
                    }
                }

                $temp_details['sku_history'] = json_encode([
                    'actual_num' => $odv->num ?? 0,
                    'actual_price' => $odv->price ?? 0,
                    'original_price' => $odv->price ?? 0,
                    'sku_value' => $temp_sku_json,
                    'stock' => $temp_check_sku->stock ?? 0,
                ]);
                $order_detail_data[] = $temp_details;
            }

            if ($ov->status > 1 && !empty($ov->express_company) && !empty($ov->express_number)) {
                foreach ($ov->details as $odv) {
                    $temp_order_child_data = [];
                    $temp_order_child_data['order_id'] = $ov->id;
                    $temp_order_child_data['order_detail_id'] = $odv->id;
                    $temp_order_child_data['created_at'] = date('Y-m-d H:i:s', $ov->ctime);
                    if (!empty($ov->receive_goods_time)) {
                        $temp_order_child_data['status'] = 2;
                        $temp_order_child_data['receipt_at'] = date('Y-m-d H:i:s', $ov->receive_goods_time);
                    } else {
                        $temp_order_child_data['status'] = 1;
                        $temp_order_child_data['receipt_at'] = null;
                    }

                    $get_express_info = ExpressInfo::where('express_num', '=', trim($ov->express_number))
                        ->select(['id'])->first();
                    $temp_order_child_data['express_info_id'] = $get_express_info->id ?? 0;
                    $order_child_data[] = $temp_order_child_data;
                }
            }
        }

        //DB::beginTransaction();

        if (!empty($order_data)) {
            DB::connection('mysql_new_zs')
                ->table('nlsg_mall_order' . $flag)->insert($order_data);
        }

        if (!empty($order_detail_data)) {
            DB::connection('mysql_new_zs')
                ->table('nlsg_mall_order_detail' . $flag)->insert($order_detail_data);
        }

        if (!empty($order_child_data)) {
            DB::connection('mysql_new_zs')
                ->table('nlsg_mall_order_child' . $flag)->insert($order_child_data);
        }

        if (empty($order_data)) {
            return false;
        } else {
            return true;
        }
    }

    public function removeMallOrdersOld()
    {
        $now_date = date('Y-m-d H:i:s');

        if (0) {
            $order_list = Db::connection('mysql_old')
                ->table('nlsg_mall_order')
                ->where('status', '>', 10)
                ->where('express_company', '<>', '')
                ->where('express_number', '<>', '')
                ->select(['express_company', 'express_number'])
                ->get()
                ->toArray();
            dd($order_list);
        }

        if (1) {
            $old_order = DB::connection('mysql_old')
                ->table('nlsg_mall_order')
                ->where('user_id', '=', 168934)
//                ->where('status', '<>', 0)
//                ->where('status','=',30)
//                ->where('express_number','<>','')
                ->get()
                ->toArray();

            $order_data = [];
            $order_child_data = [];

            foreach ($old_order as $v) {

                if ($v->status > 10 && !empty($v->express_company) && !empty($v->express_number)) {
                    //有发货信息,需要写入child表
                    $get_all_details_id = DB::connection('mysql_old')
                        ->table('nlsg_mall_order_detail')
                        ->where('order_id', '=', $v->id)
                        ->select(['id'])
                        ->get()->toArray();
                    $get_all_details_id = array_column($get_all_details_id, 'id');

                    foreach ($get_all_details_id as $di_v) {
                        $temp_di_v = [];

                        $temp_di_v['order_id'] = $v->id;
                        $temp_di_v['order_detail_id'] = $di_v;

                        $temp_di_v['express_info_id'] = 0;

                        $temp_di_v['created_at'] = date('Y-m-d H:i:s', $v->deliver_goods_time);
                        $temp_di_v['updated_at'] = date('Y-m-d H:i:s', $v->deliver_goods_time);
                        if (!empty($v->receive_goods_time)) {
                            $temp_di_v['status'] = 2;
                            $temp_di_v['receipt_at'] = date('Y-m-d H:i:s', $v->receive_goods_time);
                        } else {
                            $temp_di_v['status'] = 1;
                            $temp_di_v['receipt_at'] = null;
                        }

                        $order_child_data[] = $temp_di_v;
                    }
                }

                $temp_order = [];
                $temp_order['id'] = $v->id;
                $temp_order['ordernum'] = $v->ordernum;
                $temp_order['user_id'] = $v->user_id;
                $temp_order['order_type'] = 1;
                $temp_order['status'] = $v->status;
                $temp_order['cost_price'] = $v->cost_price;
                $temp_order['freight'] = $v->freight;
                $temp_order['vip_cut'] = $v->vip_cut;
                $temp_order['coupon_id'] = $v->coupon_id;
                $temp_order['coupon_money'] = $v->coupon_money;
                $temp_order['coupon_freight_id'] = 0;
                $temp_order['special_price_cut'] = $v->special_price_cut;
                $temp_order['price'] = $v->price;
                $temp_order['pay_price'] = $v->pay_price;
                if (!empty($v->pay_time)) {
                    $temp_order['pay_time'] = date('Y-m-d H:i:s', $v->pay_time);
                } else {
                    $temp_order['pay_time'] = null;
                }
                $temp_order['pay_type'] = $v->pay_type;
                $temp_order['os_type'] = $v->os_type;
                $temp_order['messages'] = $v->messages;
                $temp_order['remark'] = $v->remark;
                if ($v->address_method) {
                    $temp_order['post_type'] = 2;
                } else {
                    $temp_order['post_type'] = 1;
                }
                $temp_order['address_id'] = 0;
                $temp_order['address_history'] = json_encode([
                    'id' => 0,
                    "name" => $v->address_name,
                    "phone" => $v->address_phone,
                    "details" => $v->address_detail,
                    "is_default" => 0,
                    "province" => 0,
                    "city" => 0,
                    "area" => 0,
                    "province_name" => $v->address_province,
                    "city_name" => $v->address_city,
                    "area_name" => $v->address_county,
                ]);
                $temp_order['bill_type'] = $v->bill_title_type;
                $temp_order['bill_title'] = $v->bill_title;
                $temp_order['bill_number'] = $v->bill_number;
                $temp_order['bill_format'] = $v->bill_format;
                $temp_order['active_flag'] = $v->active_flag;
                $temp_order['created_at'] = date('Y-m-d H:i:s', $v->ctime);
                $temp_order['updated_at'] = $now_date;
                $temp_order['is_stop'] = $v->is_stop;
                $temp_order['stop_by'] = $v->stop_by;
                if (!empty($v->stop_at)) {
                    $temp_order['stop_at'] = date('Y-m-d H:i:s', $v->stop_at);
                } else {
                    $temp_order['stop_at'] = null;
                }
                $temp_order['stop_reason'] = $v->stop_reason;
                $temp_order['is_del'] = $v->is_del;
                if (!empty($v->del_at)) {
                    $temp_order['del_at'] = date('Y-m-d H:i:s', $v->del_at);
                } else {
                    $temp_order['del_at'] = null;
                }
                if (!empty($v->receive_goods_time)) {
                    $temp_order['receipt_at'] = date('Y-m-d H:i:s', $v->receive_goods_time);
                } else {
                    $temp_order['receipt_at'] = null;
                }
                $order_data[] = $temp_order;
            }
        }


        if (1) {
            $old_details = DB::connection('mysql_old')
                ->table('nlsg_mall_order_detail')
                ->where('user_id', '=', 168934)
                ->get()
                ->toArray();
            $details_data = [];
            foreach ($old_details as $v) {
                $temp_details = [];
                $temp_details['id'] = $v->id;
                $temp_details['order_id'] = $v->order_id;
                $temp_details['order_child_id'] = $v->order_child_id;
                $temp_details['user_id'] = $v->user_id;
                $temp_details['status'] = $v->status;
                $temp_details['goods_id'] = $v->goods_id;
                $temp_details['sku_number'] = $v->sku_number;
                $temp_details['num'] = $v->num;
                $temp_details['after_sale_used_num'] = 0;
                $temp_details['comment_id'] = $v->comment_id;
                $temp_details['inviter'] = $v->twitter_id;
                $temp_details['created_at'] = date('Y-m-d H:i:s', $v->ctime);
                $temp_details['updated_at'] = $now_date;
                $temp_details['t_money'] = 0;
                $temp_details['special_price_type'] = 0;
                $temp_details['inviter_history'] = '';
                $temp_check_sku = DB::connection('mysql_old')
                    ->table('nlsg_mall_sku')
                    ->where('sku_number', '=', $v->sku_number)
                    ->first();

                $sku_json = json_decode($v->sku_json, true);

                $temp_sku_json = [];

                if (is_array($sku_json)) {
                    foreach ($sku_json as $kk => $vv) {
                        $t = [];
                        $t['key_name'] = $kk;
                        $t['value_name'] = $vv;
                        $temp_sku_json[] = $t;
                    }
                }

                $temp_details['sku_history'] = json_encode([
                    'actual_num' => $v->num ?? 0,
                    'actual_price' => $temp_check_sku->price ?? 0,
                    'original_price' => $temp_check_sku->original_price ?? 0,
                    'sku_value' => $temp_sku_json,
                    'stock' => $temp_check_sku->stock ?? 0,
                ]);
                $details_data[] = $temp_details;
            }

        }

        dd([$details_data, $order_data]);

    }

    public function redeemCode()
    {
        $page = 1;
        $size = 1000;

        $old_data = DB::connection('mysql_old_zs')
            ->table('nlsg_coupon')
            ->where('id', '<=', 102130)
            ->where('user_id', '>', 0)
            ->whereIn('status', [1, 2])
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get()->toArray();
        $now_date = date('Y-m-d H:i:s');

        $add_data = [];

        foreach ($old_data as $v) {
            $temp_data = [];
            $temp_data['id'] = $v->id;
            $temp_data['name'] = $v->name;
            $temp_data['number'] = $v->number;
            $temp_data['type'] = $v->type;
            $temp_data['user_id'] = $v->user_id;
            $temp_data['status'] = $v->status;
            $temp_data['price'] = $v->money;
            $temp_data['full_cut'] = $v->fullcut_price;
            $temp_data['explain'] = $v->explain;
            $temp_data['order_id'] = $v->order_id;
            $temp_data['flag'] = $v->flag;
            $temp_data['get_way'] = $v->get_way;
            $temp_data['cr_id'] = $v->cr_id;
            $temp_data['created_at'] = $v->ctime > 0 ? (date('Y-m-d H:i:s', $v->ctime)) : ($now_date);
            $temp_data['begin_time'] = date('Y-m-d H:i:s', $v->starttime);
            $temp_data['end_time'] = date('Y-m-d H:i:s', $v->deadline);
            $temp_data['used_time'] = $v->use_time > 0 ? (date('Y-m-d H:i:s', $v->use_time)) : null;
            $add_data[] = $temp_data;
        }

        DB::connection('mysql_new_zs')->table('nlsg_coupon')->insert($add_data);

    }

    public function douyinLiveError()
    {
        exit();
        $begin_id = 3345;
        $end_id = 3822;

        $list = ChannelOrder::query()
            ->where('id', '>=', $begin_id)
            ->where('id', '<=', $end_id)
            ->where('sku', '=', '3412364163681537433')
            ->where('status', '=', 1)
            ->where('created_at', '>', '2021-03-02')
            ->get();

        $add_data = [];
        foreach ($list as $v) {
            $temp_data = [];
            $temp_data['type'] = 3;
            $temp_data['user_id'] = $v->user_id;
            $temp_data['relation_id'] = 17;
            $temp_data['pay_time'] = $v->success_at;
            $temp_data['status'] = 1;
            $temp_data['channel_order_id'] = $v->order_id;
            $temp_data['channel_order_sku'] = $v->sku;

            $add_data[] = $temp_data;
        }

        DB::beginTransaction();
        $res = DB::table('nlsg_subscribe')->insert($add_data);

        dd($res);
    }

    public function addVipWorksToSub()
    {
        $sql = 'SELECT user_id,username,`level`,start_time,expire_time,is_open_360,time_begin_360,time_end_360,
floor((UNIX_TIMESTAMP(expire_time) - UNIX_TIMESTAMP(start_time)) / 31536000 ) as l_1,
floor((UNIX_TIMESTAMP(time_end_360) - UNIX_TIMESTAMP(time_begin_360)) / 31536000 ) as l_2
from nlsg_vip_user where status = 1 and is_default = 1
and (`level` = 1 or (`level` = 2 and is_open_360 = 1))';

        $list = DB::select($sql);

        $now_data = date('Y-m-d H:i:s');
        //2是作品 6是讲座
        $works_list = [
//            ['type' => 2, 'id' => 638],
//            ['type' => 2, 'id' => 658],
            ['type' => 2, 'id' => 527],
        ];

        $add_data = [];

        foreach ($list as $v) {
            foreach ($works_list as $wlv) {
                $temp_data = [];
                $temp_data['type'] = $wlv['type'];
                $temp_data['user_id'] = $v->user_id;
                $temp_data['relation_id'] = $wlv['id'];
                $temp_data['pay_time'] = $now_data;
                if ($v->level == 1) {
                    $temp_data['start_time'] = $v->start_time;
                    $temp_data['end_time'] = $v->expire_time;
                } else {
                    $temp_data['start_time'] = $v->time_begin_360;
                    $temp_data['end_time'] = $v->time_end_360;
                }
                $temp_data['give'] = 3;
                $add_data[] = $temp_data;
            }
        }

//        DB::beginTransaction();
//        $res = DB::table('nlsg_subscribe')->insert($add_data);
//        foreach ($add_data as $ad){
//            DB::table('nlsg_subscribe')->insert($ad);
//        }
//        DB::rollBack();

        dd(__LINE__);

    }

    public function douyinAddCD()
    {
        $sku = [
            '3467290641875230890',
            '3412364163681537433'
        ];

//        $list = DB::table('nlsg_subscribe as s')
//            ->join('nlsg_user as u', 's.user_id', '=', 'u.id')
//            ->whereIn('s.channel_order_sku', $sku)
//            ->select(['s.user_id', 'u.phone'])
//            ->get()->toArray();

        $list = DB::table('nlsg_channel_order')
            ->whereIn('sku', $sku)
            ->where('status', '=', 1)
            ->where('pay_time', '>', '2021-03-01 00:00:00')
            ->get()->toArray();


        $add_data = [];

//        DB::beginTransaction();

        foreach ($list as $v) {
            $check = Subscribe::where('user_id', '=', $v->user_id)
                ->where('type', '=', 3)
                ->where('relation_id', '=', 17)
                ->first();
            if (empty($check)) {
                $temp_data = [];
                $temp_data['type'] = 3;
                $temp_data['user_id'] = $v->user_id;
                $temp_data['relation_id'] = 17;
                $temp_data['pay_time'] = $v->pay_time;
                $temp_data['status'] = 1;
                $temp_data['give'] = 15;
                $temp_data['channel_order_id'] = $v->order_id;
                $temp_data['channel_order_sku'] = $v->sku;
                $add_data[] = $temp_data;
            } else {
//                if ($check->channel_order_sku != '3467290641875230890'){
//                    $check->channel_order_sku = '3467290641875230890';
//                    $check->save();
//                }
            }

//            $check = DB::table('nlsg_live_count_down')
//                ->where('user_id', '=', $v->user_id)
//                ->where('phone', '=', $v->phone)
//                ->where('live_id', '=', 8)
//                ->first();
//            if (empty($check)) {
//                $temp_data = [];
//                $temp_data['live_id'] = 8;
//                $temp_data['user_id'] = $v->user_id;
//                $temp_data['phone'] = $v->phone;
//                $add_data[] = $temp_data;
//            }
        }


        dd($add_data);
//        DB::table('nlsg_live_count_down')->insert($add_data);
//        DB::table('nlsg_subscribe')->insert($add_data);
//        DB::commit();
//        dd($add_data);

    }

    public function del_bind_not_vip()
    {

        $now_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d 17:59:59', strtotime('+1 years'));
        $list = DB::table('nlsg_live_count_down as cd')
            ->join('nlsg_vip_user as vu', 'cd.new_vip_uid', '=', 'vu.user_id')
            ->where('cd.created_at', '<=', '2021-01-22 23:00:00')
            ->where('vu.level', '=', 2)
            ->where('vu.status', '=', 1)
            ->where('vu.is_default', '=', 1)
            ->select(['vu.username', 'cd.phone'])
            ->get()
            ->toArray();

        //DB::beginTransaction();

        $add_data = [];
        foreach ($list as $v) {
            $temp_add_data = [];
            $temp_add_data['parent'] = $v->username;
            $temp_add_data['son'] = $v->phone;
            $temp_add_data['life'] = 5;
            $add_data[] = $temp_add_data;
        }

        foreach ($add_data as $vv) {
            try {
                DB::table('nlsg_vip_user_bind')->insert($vv);
                echo PHP_EOL, $vv['son'], '成功', PHP_EOL;
            } catch (\Exception $e) {
                echo PHP_EOL, $vv['son'], '错误', PHP_EOL;
            }
        }


        //dd($add_data);
    }

    public function check_1360_job()
    {
        $now = time();
        $now_date = date('Y-m-d H:i:s', $now);

        $list = DB::table('nlsg_order as o')
            ->join('nlsg_user as u', 'o.user_id', '=', 'u.id')
            //->where('o.user_id','=',281277)
            ->where('o.pay_time', '>', '2021-01-21 12:00:00')
            ->where('o.pay_time', '<', '2021-01-23 12:00:00')
            ->where('o.type', '=', 14)
            ->where('o.live_id', '=', 1)
            ->where('o.relation_id', '=', 4)
            ->where('o.status', '=', 1)
            ->where('o.pay_price', '>', 1)
            ->where('o.is_shill', '=', 0)
            ->select(['o.id', 'o.user_id', 'u.phone', 'o.pay_price', 'o.ordernum'])
            ->get()
            ->toArray();

        $temp_list = [];
        foreach ($list as $v) {
            //获取邀约人信息
            $temp_inviter = DB::table('nlsg_live_count_down as cd')
                ->join('nlsg_vip_user as v', 'cd.new_vip_uid', '=', 'v.user_id')
                ->where('cd.user_id', '=', $v->user_id)
                ->where('cd.live_id', '=', 1)
                ->where('v.status', '=', 1)
                ->where('v.is_default', '=', 1)
                ->where('v.expire_time', '>', $now_date)
                ->select('v.id as vip_id', 'v.username', 'v.user_id', 'v.level',
                    'v.inviter', 'v.inviter_vip_id',
                    'v.source', 'v.source_vip_id')
                ->first();

            //获取保护人信息
            $temp_parent = DB::table('nlsg_vip_user_bind as ub')
                ->join('nlsg_vip_user as v', 'ub.parent', '=', 'v.username')
                ->where('ub.son', '=', $v->phone)
                ->where('v.status', '=', 1)
                ->where('v.is_default', '=', 1)
                ->where('v.expire_time', '>', $now_date)
                ->select('v.id as vip_id', 'v.username', 'v.user_id', 'v.level',
                    'v.inviter', 'v.inviter_vip_id',
                    'v.source', 'v.source_vip_id')
                ->first();

            if (empty($temp_inviter) && empty($temp_parent)) {
                //continue;
                $v->t_vip_id = 0;
                $v->t_name = 0;
                $v->t_uid = 0;
                $v->t_level = 0;
                $v->t_inviter = 0;
                $v->t_inviter_vip_id = 0;
                $v->t_source = 0;
                $v->t_source_vip_id = 0;
                $temp_list[] = $v;
            } else {
                continue;
                if (!empty($temp_parent)) {
                    $v->t_vip_id = $temp_parent->vip_id;
                    $v->t_name = $temp_parent->username;
                    $v->t_uid = $temp_parent->user_id;
                    $v->t_level = $temp_parent->level;
                    $v->t_inviter = $temp_parent->inviter;
                    $v->t_inviter_vip_id = $temp_parent->inviter_vip_id;
                    $v->t_source = $temp_parent->source;
                    $v->t_source_vip_id = $temp_parent->source_vip_id;
                } else {
                    $v->t_vip_id = $temp_inviter->vip_id;
                    $v->t_name = $temp_inviter->username;
                    $v->t_uid = $temp_inviter->user_id;
                    $v->t_level = $temp_inviter->level;
                    $v->t_inviter = $temp_inviter->inviter;
                    $v->t_inviter_vip_id = $temp_inviter->inviter_vip_id;
                    $v->t_source = $temp_inviter->source;
                    $v->t_source_vip_id = $temp_inviter->source_vip_id;
                }
                $temp_list[] = $v;
            }
        }

        DB::beginTransaction();
        //先校验用户是否已经开通360
        foreach ($temp_list as $v) {
            $check_open_vip = VipUser::where('user_id', '=', $v->user_id)
                ->where('level', '=', 1)
                ->where('status', '=', 1)
                ->where('is_default', '=', 1)
                ->first();

            if (empty($check_open_vip)) {
                $check_open_zs = VipUser::where('user_id', '=', $v->user_id)
                    ->where('level', '=', 2)
                    ->where('status', '=', 1)
                    ->where('is_default', '=', 1)
                    ->first();

                if (empty($check_open_zs)) {
                    echo PHP_EOL, $v->phone, '没有开通360', PHP_EOL;
                } else {
                    $v->vip_id = $check_open_zs->id;
                    if ($check_open_zs->is_open_360 == 0) {
                        echo PHP_EOL, $v->phone, '没有开通360', PHP_EOL;
                    }
                }
            } else {
                echo PHP_EOL, $v->phone, '修改了归属', PHP_EOL;
                $v->vip_id = $check_open_vip->id;

                if ($v->t_uid != $check_open_vip->inviter) {
                    $check_open_vip->inviter = $v->t_uid;
                    $check_open_vip->inviter_vip_id = $v->t_vip_id;
                    if ($v->t_level == 1) {
                        $check_open_vip->source = $v->t_source;
                        $check_open_vip->source_vip_id = $v->t_source_vip_id;
                    } else {
                        $check_open_vip->source = $v->t_uid;
                        $check_open_vip->source_vip_id = $v->t_vip_id;
                    }
                    $check_open_vip->save();
                }
            }
        }

        //校验收益表
        foreach ($temp_list as $v) {
            $check_prd = PayRecordDetail::where('ordernum', '=', $v->ordernum)
                ->where('type', '=', 11)
                ->first();
            if (empty($check_prd)) {
                echo $v->ordernum, '没有收益是对的', PHP_EOL;
                if (0) {
                    $pdModel = new PayRecordDetail();
                    $pdModel->type = 11;
                    $pdModel->ordernum = $v->ordernum;
                    $pdModel->ctime = $now;
                    if ($v->t_level == 1) {
                        $pdModel->price = 108;
                    } else {
                        $pdModel->price = 180;
                    }
                    $pdModel->user_id = $v->t_uid;
                    $pdModel->user_vip_id = $v->t_vip_id;
                    $pdModel->vip_id = $v->vip_id;
                    $pdModel->save();
                    echo $v->ordernum, '添加收益', PHP_EOL;
                }
            } else {
                PayRecordDetail::where('ordernum', '=', $v->ordernum)
                    ->where('type', '=', 11)
                    ->delete();
                echo $v->ordernum, '删除收益', PHP_EOL;
//                if ($v->t_uid != $check_prd->user_id) {
//                    $check_prd->user_id = $v->t_uid;
//                    $check_prd->user_vip_id = $v->t_vip_id;
//                    $check_prd->vip_id = $v->vip_id;
//                    $check_prd->save();
//                    echo $v->ordernum, '修改收益', PHP_EOL;
//                }
            }
        }


        dd($temp_list);


    }

    public function add_live_to_bind()
    {
        $now_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d 23:59:59', strtotime('+1 years'));

        $list = DB::table('nlsg_live_count_down as cd')
            ->join('nlsg_user as u', 'cd.user_id', '=', 'u.id')
            ->join('nlsg_user as u2', 'cd.new_vip_uid', '=', 'u2.id')
            ->leftJoin('nlsg_vip_user_bind as ub', 'ub.son', '=', 'u.phone')
            ->where('cd.created_at', '<', '2021-01-22 23:59:59')
            ->select(['u.phone as son', 'u2.phone as parent', 'ub.id as ubid'])
            ->groupBy('u.phone')
            ->get()
            ->toArray();

        $add_data = [];
        foreach ($list as $v) {
            if ($v->parent == $v->son) {
                continue;
            }

            if (empty($v->ubid)) {
                $temp_data = [];
                $temp_data['parent'] = $v->parent;
                $temp_data['son'] = $v->son;
                $temp_data['life'] = 5;
                $temp_data['begin_at'] = $now_date;
                $temp_data['end_at'] = $end_date;
                $add_data[] = $temp_data;
            }
        }


        //DB::beginTransaction();

        $res = DB::table('nlsg_vip_user_bind')->insert($add_data);

        dd([count($list), count($add_data), $res]);
    }

    public function runPoster()
    {
        $list = Subscribe::where('type', '=', 3)
            ->where('relation_id', '=', 1)
            ->select(['user_id'])
            ->groupBy('user_id')
            ->get()->toArray();

        $cpC = new CreatePosterController();
        $expire_num = 86400;

        foreach ($list as $v) {
            //post_type=21&relation_id=1&uid=168934&live_id=1&live_info_id=1
            $post_type = 21;
            $gid = 1;
            $uid = $v['user_id'];
            $live_id = 1;
            $live_info_id = 1;
            $level = User::getLevel($v['user_id']);
            $save_path = base_path() . '/public/image/';//存储路径
            if (!file_exists($save_path)) {
                mkdir($save_path, 0777, true);
            }
            $cache_key_name = 'poster_' . $uid . '_' . $post_type . '_' . $live_id . '_' . $live_info_id . '_' . $gid;
            $source_name = 'zhibo.png';
            $source = storage_path() . '/app/public/PosterMaterial/' . $source_name;
            $init = [
                'path' => $save_path,
                'source' => $source,
            ];

            $cp = new CreatePost($init);
            if (empty($g_t_id)) {
                $draw = $cpC->getDraw($uid, $post_type, $gid, $level, 0, $live_id, $live_info_id);
            } else {
                $draw = $cpC->getDraw($uid, $post_type, $gid, $level, $g_t_id);
            }
            //dd($draw);
            $temp_del_path = $draw['QR']['path'];
            $res = $cp::draw($draw);
            if (!empty($draw['QR']['path'])) {
                unlink($temp_del_path);
            }
            $file_path = $save_path . $res;
            if ($fp = fopen($file_path, "rb", 0)) {
                $base64 = $cpC->imgToBase64($file_path);
                $res = ConfigModel::base64Upload(101, $base64);
                fclose($fp);
                //unlink($file_path);
                Cache::put($cache_key_name, $res, $expire_num);
            }
            sleep(3);
        }

    }

    public function changeVipSource()
    {
        $sql = "SELECT id,user_id,inviter,inviter_vip_id,source,source_vip_id
from nlsg_vip_user where created_at > '2021-01-22 00:00:00' and inviter > 0 and inviter = source";

        $list = DB::select($sql);

        foreach ($list as $v) {
            $check_inviter = VipUser::where('id', '=', $v->inviter_vip_id)->first();
            if ($check_inviter->level == 1) {
                DB::table('nlsg_vip_user')
                    ->where('id', '=', $v->id)
                    ->update([
                        'source' => $check_inviter->source,
                        'source_vip_id' => $check_inviter->source_vip_id
                    ]);
            }
        }

    }

    public function do_1360_job()
    {
        $now_date = date('Y-m-d H:i:s');
        $ctime = time();

        $sql = "SELECT o.ordernum,o.user_id,u.phone,o.pay_price,c.new_vip_uid t_id,bind.parent from nlsg_order as o
LEFT JOIN nlsg_live_count_down as c on o.user_id = c.user_id and c.live_id = 1
LEFT JOIN nlsg_user as u on o.user_id = u.id
LEFT JOIN nlsg_vip_user_bind as bind on u.phone = bind.son

where o.id > 408257 and o.pay_time > '2021-01-22 12:00:00' and o.type = 14 and o.live_id = 1 and o.relation_id =4
and o.status = 1 and o.pay_price > 1";

        $list = DB::select($sql);

        foreach ($list as $v) {
            $temp_user_vip_info = DB::table('nlsg_vip_user')
                ->where('user_id', '=', $v->user_id)
                ->where('expire_time', '>', $now_date)
                ->where('status', '=', 1)
                ->where('is_default', '=', 1)
                ->first();
            if (empty($temp_user_vip_info)) {
                $v->user_level = 0;
                $v->user_vip_id = 0;
            } else {
                $v->user_level = $temp_user_vip_info->level;
                $v->user_vip_id = $temp_user_vip_info->id;
            }

            $temp_vip_info = DB::table('nlsg_vip_user')
                ->where('user_id', '=', $v->t_id)
                ->where('expire_time', '>', $now_date)
                ->where('status', '=', 1)
                ->where('is_default', '=', 1)
                ->first();
            if (empty($temp_vip_info)) {
                $v->t_level = 0;
                $v->t_vip_id = 0;
            } else {
                $v->t_level = $temp_vip_info->level;
                $v->t_vip_id = $temp_vip_info->id;
            }
            if (empty($v->parent)) {
                $v->parent_level = 0;
                $v->parent_vip_id = 0;
                $v->parent_uid = 0;
            } else {
                $temp_vip_info = DB::table('nlsg_vip_user')
                    ->where('username', '=', $v->parent)
                    ->where('expire_time', '>', $now_date)
                    ->where('status', '=', 1)
                    ->where('is_default', '=', 1)
                    ->first();
                if (empty($temp_vip_info)) {
                    $v->parent_level = 0;
                    $v->parent_vip_id = 0;
                    $v->parent_uid = 0;
                } else {
                    $v->parent_level = $temp_vip_info->level;
                    $v->parent_vip_id = $temp_vip_info->id;
                    $v->parent_uid = $temp_vip_info->user_id;
                }
            }
        }

        DB::beginTransaction();

        foreach ($list as $v) {
            //课程与兑换卡
            VipRedeemUser::subWorksOrGetRedeemCode($v->user_id);
            switch (intval($v->user_level)) {
                case 0:
                    //不是360  开通
                    $pdModel = new PayRecordDetail();
                    $pdModel->type = 11;
                    $pdModel->ordernum = $v->ordernum;
                    $pdModel->ctime = $ctime;
                    if ($v->parent_level > 0 && $v->parent_vip_id > 0) {
                        $source_info = VipUser::whereId($v->parent_vip_id)->first();
                        $temp_source_id = $source_info->user_id;
                        $temp_source_vip_id = $source_info->id;

                        $pdModel->user_id = $v->parent_uid;
                        $pdModel->user_vip_id = $v->parent_vip_id;
                        if ($v->parent_level == 1) {
                            $pdModel->price = 108;
                        } else {
                            $pdModel->price = 180;
                        }
                        //如果有绑定的并且绑定是vip就走绑定
                    } elseif ($v->t_level > 0 && $v->t_vip_id > 0) {
                        //没有绑定并且推荐人是vip就走推荐
                        $source_info = VipUser::whereId($v->t_vip_id)->first();
                        $temp_source_id = $source_info->user_id;
                        $temp_source_vip_id = $source_info->id;

                        $pdModel->user_id = $v->t_id;
                        $pdModel->user_vip_id = $v->t_vip_id;
                        if ($v->t_level == 1) {
                            $pdModel->price = 108;
                        } else {
                            $pdModel->price = 180;
                        }
                    } else {
                        $temp_source_id = 0;
                        $temp_source_vip_id = 0;
                    }

                    //判断当前需要添加的用户 是不是已经有会员
                    $check_add_user_vip = VipUser::where('user_id', '=', $v->user_id)
                        ->where('level', '=', 1)
                        ->where('status', '=', 1)
                        ->where('is_default', '=', 1)
                        ->first();
                    if (empty($check_add_user_vip)) {
                        $vip_add_data['user_id'] = $v->user_id;
                        $vip_add_data['nickname'] = substr_replace($v->phone, '****', 3, 4);
                        $vip_add_data['username'] = $v->phone;
                        $vip_add_data['level'] = 1;
                        $vip_add_data['inviter'] = $temp_source_id;
                        $vip_add_data['inviter_vip_id'] = $temp_source_vip_id;

                        if (!isset($source_info->level)) {
                            $vip_add_data['source'] = 0;
                            $vip_add_data['source_vip_id'] = 0;
                        } else {
                            if ($source_info->level == 1) {
                                $vip_add_data['source'] = $source_info->source ?? 0;
                                $vip_add_data['source_vip_id'] = $source_info->source_vip_id ?? 0;
                            } else {
                                $vip_add_data['source'] = $source_info->user_id ?? 0;
                                $vip_add_data['source_vip_id'] = $source_info->id ?? 0;
                            }
                        }


                        $vip_add_data['is_default'] = 1;
                        $vip_add_data['created_at'] = $now_date;
                        $vip_add_data['start_time'] = $now_date;
                        $vip_add_data['updated_at'] = $now_date;
                        $vip_add_data['expire_time'] = date('Y-m-d 23:59:59', strtotime('+1 year'));
                        $add_res = DB::table('nlsg_vip_user')->insertGetId($vip_add_data);
                    } else {
                        $check_add_user_vip->expire_time = date('Y-m-d 23:59:59', strtotime($check_add_user_vip->expire_time . ' +1 year'));
                        $check_add_user_vip->save();
                    }

                    $pdModel->vip_id = $add_res;
                    if (($v->parent_level > 0 && $v->parent_vip_id > 0) || ($v->t_level > 0 && $v->t_vip_id > 0)) {
                        $pdModel->save();
                    } else {
                        $pdModel = null;
                    }
                    break;
                case 1:
                    //是360 延长
                    $this_vip = VipUser::whereId($v->user_vip_id)->first();
                    $this_vip->expire_time = date('Y-m-d 23:59:59', strtotime($this_vip->expire_time . ' +1 year'));
                    $this_vip->save();
                    break;
                case 2:
                    //钻石 修改
                    $this_vip = VipUser::whereId($v->user_vip_id)->first();
                    $this_vip->is_open_360 = 1;
                    if (empty($this_vip->time_begin_360)) {
                        $this_vip->time_begin_360 = $now_date;
                    }
                    if (empty($this_vip->time_end_360)) {
                        $this_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime('+1 year'));
                    } else {
                        $this_vip->time_end_360 = date('Y-m-d 23:59:59', strtotime($this_vip->time_end_360 . ' +1 year'));
                    }
                    $this_vip->save();

                    $pdModel = new PayRecordDetail();
                    $pdModel->type = 11;
                    $pdModel->ordernum = $v->ordernum;
                    $pdModel->ctime = $ctime;
                    $pdModel->user_id = $this_vip->user_id;
                    $pdModel->user_vip_id = $this_vip->id;
                    $pdModel->vip_id = $this_vip->id;
                    $pdModel->price = 180;
                    $pdModel->save();
                    break;
            }
        }
        dd($list);
    }

    public function douyinLiveOrder()
    {
        $list = DB::table('wwtest as wt')
            ->leftJoin('nlsg_user as u', 'wt.phone', '=', 'u.phone')
            ->select(['wt.phone', 'u.id as user_id'])
            ->get()->toArray();

        foreach ($list as $v) {
            if (!is_numeric($v->user_id)) {
                $userModel = new User();
                $userModel->phone = $v->phone;
                $userModel->nickname = substr_replace($v->phone, '****', 3, 4);
                $userModel->save();
                $v->user_id = $userModel->id;
            }
        }

        $now_date = date('Y-m-d H:i:s');
        $add_data = [];
        foreach ($list as $v) {
            $w = 1;
            while ($w < 3) {
                $temp_data = [];
                $temp_data['type'] = 3;
                $temp_data['user_id'] = $v->user_id;
                $temp_data['relation_id'] = $w;
                $temp_data['pay_time'] = $now_date;
                $temp_data['status'] = 1;
                $temp_data['give'] = 3;
                $add_data[] = $temp_data;
                $w++;
            }
        }
        DB::table('nlsg_subscribe')->insert($add_data);
        dd($add_data);
    }

    public function countUserData()
    {
        if (0) {
            $sql = 'select from_uid as uid from nlsg_user_follow
                UNION
                SELECT to_uid as uid from nlsg_user_follow';

            $list = DB::select($sql);

            $list = array_column($list, 'uid');
            $list = array_unique($list);

            foreach ($list as $v) {
                $from_count = UserFollow::where('from_uid', '=', $v)->where('status', '=', 1)->count();
                $to_count = UserFollow::where('to_uid', '=', $v)->where('status', '=', 1)->count();

                DB::table('nlsg_user')
                    ->where('id', '=', $v)
                    ->update([
                        'follow_num' => $from_count,
                        'fan_num' => $to_count
                    ]);
            }
        }

        if (0) {
            $list = History::where('is_del', '=', 0)
                ->select(['user_id'])
                ->groupBy('user_id')
                ->get()->toArray();

            foreach ($list as $v) {
                $count = History::where('user_id', '=', $v['user_id'])
                    ->groupBy('relation_id', 'relation_type')
                    ->count();
                DB::table('nlsg_user')
                    ->where('id', '=', $v['user_id'])
                    ->update([
                        'history_num' => $count
                    ]);
            }

            dd($list);
        }

    }

    public function normalCodeRun($page, $size)
    {
        $old_data = DB::connection('mysql_old_zs')
            ->table('nlsg_redeem_code')
            ->where('id', '>', 407373)
            ->limit($size)
            ->offset(($page - 1) * $size)
            ->get();
        if ($old_data->isEmpty()) {
            return false;
        }
        $old_data = $old_data->toArray();
        $add_data = [];
        foreach ($old_data as $v) {
            $temp_add_data = [];
            $temp_add_data['id'] = $v->id;
            $temp_add_data['number'] = $v->number;
            $temp_add_data['code'] = $v->code;
            $temp_add_data['name'] = $v->name;
            $temp_add_data['status'] = $v->status;
            $temp_add_data['phone'] = $v->phone;
            $temp_add_data['user_id'] = $v->user_id ?? 0;
            if ($v->status === 1) {
                //已使用
                $temp_add_data['to_user_id'] = $v->user_id;
            } else {
                $temp_add_data['to_user_id'] = 0;
            }
            $temp_add_data['service_id'] = $v->service_id ?? 0;
            $temp_add_data['is_new_code'] = $v->is_new_code ?? 0;
            $temp_add_data['new_group'] = $v->new_group ?? 0;
            $temp_add_data['can_use'] = $v->can_use ?? 0;
            $temp_add_data['redeem_type'] = $v->redeem_type;
            $temp_add_data['goods_id'] = $v->goods_id;
            $temp_add_data['add_admin_id'] = $v->add_admin_id;
            $temp_add_data['os_type'] = $v->os_type;

            if (empty($v->ctime)) {
                $temp_add_data['created_at'] = date('Y-m-d H:i:s');
            } else {
                $temp_add_data['created_at'] = date('Y-m-d H:i:s', $v->ctime);
            }

            if (empty($v->exchange_time)) {
                $temp_add_data['exchange_time'] = null;
            } else {
                $temp_add_data['exchange_time'] = date('Y-m-d H:i:s', $v->exchange_time);
            }

            if (empty($v->start_time)) {
                $temp_add_data['start_at'] = null;
            } else {
                $temp_add_data['start_at'] = date('Y-m-d H:i:s', $v->start_time);
            }

            if (empty($v->end_time)) {
                $temp_add_data['end_at'] = null;
            } else {
                $temp_add_data['end_at'] = date('Y-m-d H:i:s', $v->end_time);
            }

            $add_data[] = $temp_add_data;
        }
        DB::connection('mysql_new_zs')->table('nlsg_redeem_code')->insert($add_data);
    }

    //迁移兑换券
    public function normalCode()
    {
        $page = 1;
        $size = 200;

        $flag = true;
        while ($flag) {
            $res = $this->normalCodeRun($page, $size);
            if ($res === false) {
                $flag = false;
            }
            $page++;
        }

    }

    //老兑换券的视频转讲座
    public function editCode()
    {
        $w_list = DB::connection('mysql_new_zs')
            ->table('nlsg_column')
            ->where('type', '=', 2)
            ->where('works_id', '>', 0)
            ->select(['id', 'name', 'works_id'])
            ->get()->toArray();

        $w_ids = array_column($w_list, 'works_id');

        $r = DB::connection('mysql_new_zs')
            ->table('nlsg_redeem_code')
            ->whereIn('goods_id', $w_ids)
            ->where('is_new_code', '=', 1)
            ->where('status', '=', 0)
            ->where('can_use', '<>', 3)
            ->where('redeem_type', '=', 2)
            ->select(['goods_id'])
            ->groupBy('goods_id')
            ->get()->toArray();

        $to_edit_goods_id = array_column($r, 'goods_id');

        foreach ($to_edit_goods_id as $v) {
            $change_id = 0;
            foreach ($w_list as $vv) {
                if ($vv->works_id === $v) {
                    $change_id = $vv->id;
                }
            }
            if (empty($change_id)) {
                continue;
            }

            DB::connection('mysql_new_zs')
                ->table('nlsg_redeem_code')
                ->where('goods_id', '=', $v)
                ->where('redeem_type', '=', 2)
                ->where('can_use', '<>', 3)
                ->where('is_new_code', '=', 1)
                ->update([
                    'goods_id' => $change_id,
                    'redeem_type' => 3
                ]);
        }

    }

    public static function getKernelLock(int $job_id, int $flag)
    {
        $cache_key_name = 'kernel_lock_' . $job_id;
        $counter = Cache::get($cache_key_name);
        $expire_num = 60;
        if ($flag == 2) {
            Cache::put($cache_key_name, 1, $expire_num);
        } elseif ($flag == 3) {
            Cache::pull($cache_key_name);
        } else {
            if ($counter < 1) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function worksListOfSub()
    {
        $now_date = date('Y-m-d H:i:s');
        $job_key = 1844;

        $check_job = self::getKernelLock($job_key, 1);
        if ($check_job === false) {
            return true;
        }

        $run = true;
        while ($run) {
            self::getKernelLock($job_key, 2);
            $list = DB::table('works_list_of_sub')
                ->whereIn('works_type', [2, 6, 3, 7])
                ->where('status', '=', 1)
                ->limit(3000)
                ->get();

            if ($list->isEmpty()) {
                self::getKernelLock($job_key, 3);
                $run = false;
            }

            foreach ($list as $v) {
                DB::beginTransaction();

                if (empty($v->user_id) && empty($v->phone)) {
                    DB::table('works_list_of_sub')
                        ->where('id', '=', $v->id)
                        ->update([
                            'status' => 3
                        ]);
                    DB::commit();
                    continue;
                }

                if (!empty($v->phone)) {
                    $temp_user = User::firstOrCreate([
                        'phone' => $v->phone
                    ], [
                        'nickname' => substr_replace($v->phone, '****', 3, 4),
                    ]);
                } else {
                    $temp_user = User::firstOrCreate([
                        'id' => $v->user_id
                    ]);
                }

                if (empty($v->twitter_phone)) {
                    $temp_t_user_id = 0;
                } else {
                    $temp_t_u = User::query()->where('phone', '=', $v->twitter_phone)->first();
                    if (empty($temp_t_u)) {
                        $temp_t_user_id = 0;
                    } else {
                        $temp_t_user_id = $temp_t_u->id;
                    }
                }

                $temp_user_id = $temp_user->id;
                $temp_user_phone = $temp_user->phone;

                $query = Subscribe::where('user_id', '=', $temp_user_id)
//                    ->where('created_at', '>', '2021-01-05 00:00:00')
                    ->where('relation_id', '=', $v->works_id)
                    ->where('type', '=', $v->works_type)
                    ->where('status', '=', 1);
//                    ->where('id','>',300000);

                if ($v->works_type != 3) {
                    $query->where('end_time', '>=', $now_date);
                }

                $check = $query->select(['id'])->first();

                $add_sub_data = [];

                if (empty($check)) {
                    $temp_data = [];
                    $temp_data['type'] = $v->works_type;
                    $temp_data['user_id'] = $temp_user_id;
                    $temp_data['relation_id'] = $v->works_id;
                    $temp_data['pay_time'] = $now_date;
                    $temp_data['status'] = 1;
                    $temp_data['give'] = 3;
                    $temp_data['twitter_id'] = $temp_t_user_id;
                    $temp_data['is_flag'] = $v->flag_name;
                    if ($v->flag_name = '抖音') {
                        $temp_data['channel_order_sku'] = '3460976881036350000';
                    } else {
                        $temp_data['channel_order_sku'] = '';
                    }

                    if ($v->works_type != 3) {
                        $temp_data['start_time'] = $now_date;
                        $temp_data['end_time'] = date('Y-m-d 23:59:59', strtotime("+$v->years years"));
                    } else {
                        $temp_data['start_time'] = $now_date;
                        $temp_data['end_time'] = $now_date;
                    }
                    $add_sub_data[] = $temp_data;
                } else {
                    if ($v->works_type != 3) {
                        $temp_end_time = date('Y-m-d 23:59:59', strtotime("$check->end_time +$v->years  years"));
                        $check->end_time = $temp_end_time;
                        $edit_res = $check->save();
                        if ($edit_res === false) {
                            DB::rollBack();
                            break;
                        }
                    }
                }

                //如果是直播,需要记录liveCountDown和live预约人数
                if ($v->works_type === 3) {
                    $check_cd = LiveCountDown::query()
                        ->where('user_id', '=', $temp_user_id)
                        ->where('live_id', '=', $v->works_id)
                        ->select(['id'])
                        ->first();
                    if (empty($check_cd)) {
                        $cd_data['live_id'] = $v->works_id;
                        $cd_data['user_id'] = $temp_user_id;
                        $cd_data['phone'] = $temp_user_phone;
                        $cd_res = DB::table('nlsg_live_count_down')->insert($cd_data);
                        if (!$cd_res) {
                            DB::rollBack();
                            continue;
                        }
                    }

                    //添加关系保护
                    $check_bind = VipUserBind::getBindParent($v->phone);
                    if ($check_bind === 0) {
                        //没有绑定记录,则绑定
                        $bind_data = [];
                        if (!empty($v->twitter_phone)) {
                            $bind_data = [
                                'parent' => $v->twitter_phone,
                                'son' => $v->phone,
                                'life' => 2,
                                'begin_at' => date('Y-m-d H:i:s'),
                                'end_at' => date('Y-m-d 23:59:59', strtotime('+1 years')),
                                'channel' => 1,
                                'status' => 1
                            ];
                        } else {
                            if ($v->flag_name === '抖音') {
                                $bind_data = [
                                    'parent' => '18512378959',
                                    'son' => $v->phone,
                                    'life' => 2,
                                    'begin_at' => date('Y-m-d H:i:s'),
                                    'end_at' => date('Y-m-d 23:59:59', strtotime('+1 years')),
                                    'channel' => 3,
                                    'status' => 1
                                ];
                            }
                        }

                        if (!empty($bind_data)) {
                            DB::table('nlsg_vip_user_bind')->insert($bind_data);
                        }
                    }
                }

                $edit_res = DB::table('works_list_of_sub')
                    ->where('id', '=', $v->id)
                    ->update([
                        'user_id' => $temp_user_id,
                        'phone' => $temp_user_phone,
                        'status' => 2,
                    ]);
                if ($edit_res == false) {
                    DB::rollBack();
                    continue;
                }
                if (!empty($add_sub_data)) {
                    $add_res = DB::table('nlsg_subscribe')->insert($add_sub_data);
                    if ($add_res === false) {
                        DB::rollBack();
                        continue;
                    }

                    Live::where('id', '=', $v->works_id)->increment('order_num', count($add_sub_data));
                }
                DB::commit();
            }
        }

        return true;
    }

    public function worksListOfDelSub()
    {
        $model_name = 'works_list_of_del_sub';
        $now_date = date('Y-m-d H:i:s');
        $job_key = 2043;

        $check_job = self::getKernelLock($job_key, 1);

        if ($check_job === false) {
            return true;
        }

        $run = true;
        while ($run) {
            self::getKernelLock($job_key, 2);
            $list = DB::table($model_name)
                ->whereIn('works_type', [2, 6, 3, 7])
                ->where('status', '=', 1)
                ->limit(3000)
                ->get();

            if ($list->isEmpty()) {
                self::getKernelLock($job_key, 3);
                $run = false;
            }

            foreach ($list as $v) {
                DB::beginTransaction();

                $check_phone = User::query()->where('phone','=',$v->phone)->first();
                if (empty($check_phone)){
                    DB::table($model_name)
                        ->where('id', '=', $v->id)
                        ->update([
                            'status' => 3,
                        ]);
                    DB::commit();
                    continue;
                }

                $temp_user_id = $check_phone->id;
                $temp_user_phone = $check_phone->phone;

                $check = Subscribe::query()
                    ->where('user_id', '=', $temp_user_id)
                    ->where('relation_id', '=', $v->works_id)
                    ->where('type', '=', $v->works_type)
                    ->where('status', '=', 1)
                    ->first();

                if (!empty($check)) {
                    $check->status = 0;
                    $edit_res = $check->save();
                    if ($edit_res === false) {
                        DB::rollBack();
                        break;
                    }
                }

                DB::table($model_name)
                    ->where('id', '=', $v->id)
                    ->update([
                        'user_id' => $temp_user_id,
                        'phone' => $temp_user_phone,
                        'status' => 2,
                    ]);

                DB::commit();
            }
        }

        return true;
    }

    public function subListSms()
    {
//        $while_flag = true;
//        while ($while_flag) {
        //现在只有情商课的sms
        $list = WorksListOfSub::where('works_type', '=', 2)
            ->where('status', '=', 2)
            ->where('works_id', '=', 404)
            ->where('is_sendsms', '=', 1)
            ->select(['id', 'phone'])
            ->limit(300)
            ->get();

        if ($list->isEmpty()) {
//                $while_flag = false;
            return true;
        } else {
            $list = $list->toArray();
            $phone = array_column($list, 'phone');
            $phone = array_unique($phone);
            $phone = implode(',', $phone);
            $id_list = array_column($list, 'id');
            $easySms = app('easysms');
            $easySms->send($phone, [
                'template' => 'SMS_218130053',

                //'template' => 'SMS_218033474',
            ], ['aliyun']);
            WorksListOfSub::whereIn('id', $id_list)->update(['is_sendsms' => 2]);
        }
//        }
        return true;
    }

    public function liveOrderAddVipDind()
    {
        $sql = "SELECT
	o.id,
	o.user_id,
	u.phone,
	o.twitter_id,
	tjr.phone AS tphone,
	o.pay_time,
	o.is_ascription,
	o.ascription_time,
	ub.parent,
	ub.son
FROM
	nlsg_order AS o
	LEFT JOIN nlsg_user AS u ON u.id = o.user_id
	LEFT JOIN nlsg_user AS tjr ON tjr.id = o.twitter_id
	LEFT JOIN nlsg_vip_user_bind AS ub ON u.phone = ub.son
WHERE
	o.pay_time > '2021-01-21 00:00:00'
	AND o.type = 10
	AND o.`status` = 1
	AND o.twitter_id > 0
	AND o.pay_price > 1
	AND ISNULL( ub.son )
GROUP BY
	o.user_id
ORDER BY
	o.id DESC";

        $list = DB::select($sql);

        $begin_date = date('2021-03-27 14:00:00');
        $end_date = date('Y-m-d 23:59:59', strtotime('+1 years'));

        foreach ($list as $v) {
            DB::beginTransaction();
            $bind_data = [
                'parent' => $v->tphone,
                'son' => $v->phone,
                'life' => 2,
                'begin_at' => $v->pay_time,
                'end_at' => date('Y-m-d 23:59:59', strtotime("$v->pay_time +1 years")),
                'channel' => 2
            ];

            $b_res = DB::table('nlsg_vip_user_bind')->insert($bind_data);
            $o_res = Order::where('id', '=', $v->id)->update([
                'is_ascription' => 1,
                'ascription_time' => $begin_date
            ]);
            if ($b_res && $o_res) {
                echo '添加:', $v->tphone, '->', $v->phone, PHP_EOL;
                Db::commit();
            } else {
                DB::rollBack();
            }
        }

        exit('完毕');
    }

    public function mysqlTest()
    {

    }

    public function liveOnlineUserList()
    {
        $redis = Redis::connection();
        $data = $redis->LPOP('online_user_list');

        print_r($data);

        $data = json_decode($data, true);
        $check = LiveOnlineUser::where('online_time_str', '=', $data['online_time_str'])
            ->where('user_id', '=', $data['user_id'])
            ->where('live_id', '=', $data['live_id'])
            ->where('live_son_flag', '=', $data['live_son_flag'])
            ->first();
        if (empty($check)) {
            $res = DB::table('nlsg_live_online_user')->insert($data);
            if (!$res) {
                $redis->rpush('online_user_list', $data);
            }
        }
    }

    public function lours()
    {
        $date = date('Y-m-d H:i:s');
//        $log = DB::table('nlsg_live_online_user_clean_log')
//            ->orderBy('id', 'desc')
//            ->select(['id', 'begin_id', 'end_id'])
//            ->first();
//
//        $new_log = DB::table('nlsg_live_online_user')
//            ->orderBy('id', 'desc')
//            ->select(['id'])
//            ->first();
//
//        $log_end_id = $log->end_id ?? 1;
//        $new_end_id = $new_log->id;
//
//        if ($new_end_id <= $log_end_id) {
//            return true;
//        }
//
//        $begin_id = $log_end_id;
//        $end_id = $new_end_id;
//
//        DB::table('nlsg_live_online_user_clean_log')->insert([
//            'begin_id' => $begin_id,
//            'end_id' => $end_id,
//            'created_at' => $date,
//            'updated_at' => $date
//        ]);

        $lours = new LiveOnlineUserRemoveServers(1, 500);
        foreach ($lours as $v) {
            try {
                unset($v['online_time']);
                DB::table('nlsg_live_online_user_clean')->insert($v);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    public function checkVipSubTime(){

//        $sql = "SELECT vu.id as vuid,sub.id as subid,vu.user_id,vu.`level`,vu.status as vu_status,sub.status as sub_status,
//       vu.start_time,vu.expire_time,vu.is_open_360,vu.time_begin_360,vu.time_end_360,sub.type sub_type,sub.relation_id,
//       sub.start_time sub_start_time,sub.end_time as sub_end_time,CONCAT(sub.type,'_',sub.relation_id) as wtid
//from nlsg_vip_user as vu
//join nlsg_subscribe as sub on vu.user_id = sub.user_id
//
//where sub.order_id = 0 and sub.relation_id in (404,419,567,568,569,570,574,577,586,588,440,441,450,452,630,508,510,512,513,644,638)
//and  CONCAT(sub.type,'_',sub.relation_id) in ('2_404', '2_419', '2_567', '2_568', '2_569', '2_570', '2_574', '2_577', '2_586', '2_588', '6_440', '6_441', '6_450', '6_452', '2_630', '6_508', '6_510', '6_512', '6_513', '2_644', '2_638')
//and vu.expire_time <> sub.end_time limit 1";
//
//        $list = DB::select($sql);
//
//
//        foreach ($list as $v){
//            $tempSub = Subscribe::query()->where('id','=',$v->subid)->first();
//            if ($v->vu_status === 0 && $v->sub_status === 1){
//                $tempSub->status = 0;
//            }
//
//            if ($v->vu_status === 1 && $v->sub_status ===0){
//                $tempSub->status = 1;
//            }
//
//            $temp_end_time = '';
//            if ($v->vu_status === 1){
//                if ($v->level === 1){
//                    $temp_end_time = $v->expire_time;
//                }else{
//                    if ($v->is_open_360 === 1){
//                        $temp_end_time = $v->time_end_360;
//                    }
//                }
//            }
//
//            if (empty($temp_end_time)){
//                $tempSub->status = 0;
//            }else{
//                $tempSub->end_time = $temp_end_time;
//            }
////            $tempSub->save();
//        }
//        dd($list);


        $max_id = 12000;
//        $works_list = VipWorksList::query()->select(['id','type','works_id'])->get()->toArray();
//        $works_id_list = VipWorksList::query()->pluck('works_id')->toArray();


        $this_time_id = 1;

        while ($this_time_id <= $max_id){
            $vip = VipUser::query()
                ->where('id','=',$this_time_id)
                ->select(['id','user_id','username','level','start_time','expire_time','status',
                    'is_default','order_id','is_open_360','time_begin_360','time_end_360'])
                ->first();
            if (!empty($vip)){
                $vip = $vip->toArray();
                //sub表的type 2作品 6讲座
                //vip works表的type 课程类型 1专栏表  2作品表

                //如果是有效的,修改sub表
                if ($vip['status'] === 1){
                    $temp_end_time = '';
                    if ($vip['level'] === 1){
                        $temp_end_time = $vip['expire_time'];
                    }else{
                        $temp_end_time = $vip['time_end_360'];
                    }
                    if (!empty($temp_end_time)){

                        $temp_sql = "UPDATE nlsg_subscribe set end_time = '".$temp_end_time.
                            "' where user_id = ".$vip['user_id']." and order_id = 0 and status = 1 and
CONCAT(type,'_',relation_id) in ('2_404', '2_419', '2_567', '2_568', '2_569', '2_570', '2_574', '2_577', '2_586', '2_588', '6_440', '6_441', '6_450', '6_452', '2_630', '6_508', '6_510', '6_512', '6_513', '2_644', '2_638')";
                        DB::select($temp_sql);
                    }
                }else{
                    //不是有效的,查看是否还有默认的
                    $status_check = VipUser::query()
                        ->where('user_id','=',$vip['user_id'])
                        ->where('status','=',1)
                        ->where('is_default','=',1)
                        ->first();
                    //没有,按照这个修改
                    if (empty($status_check)){
                        $temp_works_sql = "UPDATE nlsg_subscribe set status = 0
where user_id = ".$vip['user_id']." and order_id = 0 and status = 1 and
CONCAT(type,'_',relation_id) in ('2_404', '2_419', '2_567', '2_568', '2_569', '2_570', '2_574', '2_577', '2_586', '2_588', '6_440', '6_441', '6_450', '6_452', '2_630', '6_508', '6_510', '6_512', '6_513', '2_644', '2_638')";
                        DB::select($temp_works_sql);
                    }
                }

            }
            $this_time_id++;
            echo $this_time_id,PHP_EOL;
        }


    }

}
