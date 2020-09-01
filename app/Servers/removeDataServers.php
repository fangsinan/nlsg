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
            $temp['id'] = $v->id;
            $temp['category_id'] = $v->category_id;
            $temp['name'] = $v->name;
            $temp['subtitle'] = $v->subtitle;
            $temp['picture'] = $v->picture;
            $temp['freight_id'] = 14;
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
