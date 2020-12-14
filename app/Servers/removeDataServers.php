<?php


namespace App\Servers;

use App\Models\MallGoods;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class removeDataServers
{
    public function removeGoods()
    {
//        $copy_flag = '_copy1';
        $copy_flag = '';

        $old_comment = DB::connection('mysql_old')
            ->table('nlsg_mall_comment')
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
        $r5 = DB::table('nlsg_mall_comment' . $copy_flag)->insert($comment_data);

        dd([$r1, $r2, $r3, $r4, $r5]);
    }

    public function removeMallOrders()
    {
        $now_date = date('Y-m-d H:i:s');
        $old_order = DB::connection('mysql_old')
            ->table('nlsg_mall_order')
            ->get()->toArray();
        $order_data = [];
        foreach ($old_order as $v) {
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


        $old_details = DB::connection('mysql_old')
            ->table('nlsg_mall_order_detail')
            ->get()->toArray();
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

            $sku_json = json_decode($v->sku_json);
            $temp_sku_json = [];

            if (is_array($sku_json)) {
                foreach ($sku_json as $kk => $vv) {
                    $t = new class {
                    };
                    $v->key_name = $kk;
                    $v->value_name = $vv;
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

        dd($details_data);

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
    public function addRobot(){
        //id 8000-11000  两千个虚拟用户位
        $begin_num = [137,186,139,151];
        $i = 8000;
        while ($i <= 11000){
            $now = date('Y-m-d H:i:s');
            $temp_num = rand(10000000,99999999);
            $num = $begin_num[rand(0,3)].$temp_num;
            $num = substr_replace($num, '****', 3, 4);

            $model = new User();
            $model->id = $i;
            $model->phone = $i;
            $model->nickname = $num;
            $model->created_at = $now;
            $model->updated_at = $now;
            $model->is_robot = 1;
            $res = $model->save();
            if ($res){
                $i++;
            }
        }
    }

}
