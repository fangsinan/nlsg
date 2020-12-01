<?php


namespace App\Servers;

use Illuminate\Support\Facades\DB;

class removeDataServers
{
    public function removeGoods()
    {

        $now_date = date('Y-m-d H:i:s');

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
                $temp_sku_value['key_value'] = $vv;
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


        DB::table('nlsg_mall_goods')->insert($goods_data);
        DB::table('nlsg_mall_sku')->insert($sku_data);
        DB::table('nlsg_mall_sku_value')->insert($sku_value_data);
        DB::table('nlsg_mall_picture')->insert($picture_data);

    }
}
