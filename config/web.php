<?php

return [


    'Ali' =>[
        'ACCESS_KEY_ALI'=>env('ACCESS_KEY_ALI'),
        'SECRET_KEY_ALI'=>env('SECRET_KEY_ALI'),
        'BUCKET_ALI'=>env('BUCKET_ALI'),
        'IMAGES_URL' => env('IMAGES_URL'), //阿里图片地址
    ],
    'is_new_time' => date('Y-m-d H:i:s',strtotime("-1 week")),

    //提现
    'Withdrawals'=>[
        'url'=>'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers',
        'GetMoneyStatus'=>'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo',
        'Single_Quota'=>20000,//给同一个实名用户付款，单笔单日限额2W/2W
        'PayMoney_Sum'=>1000000,//一个商户同一日付款总额限额100W
        'Single_Min'=>1,//单笔最小金额默认为1元
        'Time_Interval'=>15,//给同一个用户付款时间间隔不得低于15秒
        'Pay_Count'=>10,//每个用户每天最多可付款10次，可以在商户平台--API安全进行设置
        'Pay_Taxes'=>800,//交税起征点 自然月为准
        'Pay_Taxes_Proportion'=>0.2,//交税税点 超出的20%
        'Pay_Daynum'=>15,//提现天数
        'Min_Price'=>10,//最低可提现10元
        'Test_User'=>[],//测试账号id
    ],

    //能量币类型
    'coin_arr' => [
        'merchant.NLSGApplePay.6nlb' => 6,
        'merchant.NLSGApplePay.30nlb' => 30,
        'merchant.NLSGApplePay.68nlb' => 68,
        'merchant.NLSGApplePay.98nlb' => 98,
        'merchant.NLSGApplePay.128nlb' => 128,
        'merchant.NLSGApplePay.198nlb' => 198,
        'merchant.NLSGApplePay.298nlb' => 298,
        'merchant.NLSGApplePay.488nlb' => 488,
        'merchant.NLSGApplePay.998nlb' => 998,
        'merchant.NLSGApplePay.1398nlb' => 1398,
        'merchant.NLSGApplePay.2998nlb' => 2998,
    ],
    "Im_config" => [
        'admin' => "administrator",

    ],

];