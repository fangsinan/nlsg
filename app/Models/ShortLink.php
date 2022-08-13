<?php

namespace App\Models;


use App\Models\DouDian\DouDianOrderList;
use App\Models\DouDian\DouDianProductList;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class ShortLink extends Base
{
    protected $table = 'a_short_link';

    protected $fillable = [
        'name',
        'url',
        'status',
        'admin_id',
        'code',
        'created_at',
        'updated_at',
    ];

    public function backendUser()
    {
        return $this->belongsTo(BackendUser::class, 'admin_id', 'id');
    }

    //DouDianOrder Model
    /*public function orderList(): HasMany {
        return $this->hasMany(DouDianOrderList::class,'parent_order_id','order_id');
    }

    //DouDianOrderList model
    public function productInfo(): HasOne {
        return $this->hasOne(DouDianProductList::class, 'product_id', 'product_id');
    }

    public function ceshi()
    {

        $query->with([
            'orderList' => function ($q) {
                $q->select([
                    'id', 'order_id', 'parent_order_id', 'create_time', 'update_time',
                    DB::raw('FROM_UNIXTIME(create_time,"%Y-%m-%d %H:%i") as create_time_date'),
                    DB::raw('FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i") as update_time_date'),
                ]);
            },
            'orderList.productInfo' => function ($q) {
                $q->select([
                    'id', 'product_id', 'img', 'name', 'product_type',
                ]);
            }
        ]);

        //虚拟,实物
        $query->whereHas('orderList.productInfo', function ($q) {
            $q->where('product_type', '=', 3);
        });


        //商品名称
        $query->when($product_name, function ($q, $product_name) {
            $q->wherehas('orderList.productInfo', function ($q) use ($product_name) {
                $q->where('name', 'like', '%' . $product_name . '%');
            });
        });

    }*/

}
