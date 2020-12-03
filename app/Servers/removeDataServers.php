<?php


namespace App\Servers;

use Illuminate\Support\Facades\DB;

class removeDataServers
{
    public function removeGoods()
    {
        $copy_flag = '_copy1';

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
            ->get()->toArray();

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
        $old_order = DB::connection('mysql_old')
            ->table('nlsg_mall_order')
            ->get()->toArray();
        dd($old_order);
        $order_data = [];
        foreach ($old_order as $v) {
            $temp_order = [];
            $temp_order['id'] = $v->id;
            $temp_order['ordernum'] = $v->id;
            $temp_order['user_id'] = $v->id;
            $temp_order['order_type'] = $v->id;
            $temp_order['status'] = $v->id;
            $temp_order['cost_price'] = $v->id;
            $temp_order['freight'] = $v->id;
            $temp_order['vip_cut'] = $v->id;
            $temp_order['coupon_id'] = $v->id;
            $temp_order['coupon_money'] = $v->id;
            $temp_order['coupon_freight_id'] = $v->id;
            $temp_order['special_price_cut'] = $v->id;
            $temp_order['price'] = $v->id;
            $temp_order['pay_price'] = $v->id;
            $temp_order['pay_time'] = $v->id;
            $temp_order['pay_type'] = $v->id;
            $temp_order['os_type'] = $v->id;
            $temp_order['messages'] = $v->id;
            $temp_order['remark'] = $v->id;
            $temp_order['post_type'] = $v->id;
            $temp_order['address_id'] = $v->id;
            $temp_order['address_history'] = $v->id;
            $temp_order['bill_type'] = $v->id;
            $temp_order['bill_title'] = $v->id;
            $temp_order['bill_number'] = $v->id;
            $temp_order['bill_format'] = $v->id;
            $temp_order['active_flag'] = $v->id;
            $temp_order['created_at'] = $v->id;
            $temp_order['updated_at'] = $v->id;
            $temp_order['is_stop'] = $v->id;
            $temp_order['stop_at'] = $v->id;
            $temp_order['stop_by'] = $v->id;
            $temp_order['stop_reason'] = $v->id;
            $temp_order['is_del'] = $v->id;
            $temp_order['del_at'] = $v->id;
            $temp_order['sp_id'] = $v->id;
            $temp_order['dead_time'] = $v->id;
            $temp_order['receipt_at'] = $v->id;
            $temp_order['live_id'] = $v->id;
            $temp_order['live_info_id'] = $v->id;

            $order_data[] = $temp_order;
        }

//        $old_details = DB::connection('mysql_old')
//            ->table('nlsg_mall_order_detail')
//            ->get()->toArray();


    }

}
