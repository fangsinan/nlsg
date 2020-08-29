<?php


namespace App\Servers;

use Illuminate\Support\Facades\DB;

class removeDataServers
{
    public function removeGoods()
    {
        $old_goods = DB::connection('mysql_old')
            ->table('nlsg_mall_goods')
            ->get()
            ->toArray();

        $goods_data = [];
        foreach ($old_goods as $v) {
            $temp = [];
            $temp['id'] = '';
            $temp['category_id'] = '';
            $temp['name'] = '';
            $temp['subtitle'] = '';
            $temp['picture'] = '';
            $temp['freight_id'] = '';
            $temp['number'] = '';
            $temp['original_price'] = '';
            $temp['price'] = '';
            $temp['sales_num_virtual'] = '';
            $temp['sales_num'] = '';
            $temp['keywords'] = '';
            $temp['content'] = '';
            $temp['view_num'] = '';
            $temp['collection_num'] = '';
            $temp['status'] = '';
            $temp['created_at'] = '';
            $temp['updated_at'] = '';
            $goods_data[] = $temp;
        }

        dd($goods_data);

    }
}
