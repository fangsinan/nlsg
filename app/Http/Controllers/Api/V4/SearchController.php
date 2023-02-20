<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Live;
use App\Models\LiveUrl;
use App\Models\MallGoods;
use App\Models\OfflineProducts;
use App\Models\Search;
use App\Models\Wiki;
use App\Models\Works;
use App\Models\Xfxs\XfxsVip;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * @api {get} /api/v4/search/index   全局搜索 热词
     * @apiName index
     * @apiVersion 1.0.0
     * @apiGroup search
     * @apiParam {string} flag   类型(商品:only_goods)
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": [
    {
    "id": 9,
    "keywords": "爸爸",
    "user_id": 1,
    "num": 6,
    "created_at": "2020-06-28 11:14:44",
    "updated_at": "2020-06-28 11:14:53"
    },
    {
    "id": 6,
    "keywords": "如何培",
    "user_id": 1,
    "num": 2,
    "created_at": "2020-06-28 11:01:53",
    "updated_at": "2020-06-28 11:02:06"
    },
    {
    "id": 8,
    "keywords": "孩子",
    "user_id": 0,
    "num": 2,
    "created_at": "2020-06-28 11:02:16",
    "updated_at": "2020-06-28 11:16:14"
    },
    {
    "id": 7,
    "keywords": "haizi",
    "user_id": 0,
    "num": 1,
    "created_at": "2020-06-28 11:02:13",
    "updated_at": "2020-06-28 11:02:13"
    }
    ]
    }
    */
    public function index(Request $request)
    {
        $flag = $request->input('flag','');
        if($flag == 'only_goods'){
            $hot_search = \App\Models\ConfigModel::getData(20);
            $hot_search = json_decode($hot_search);
            //$hot_search = explode(',',$hot_search);
        }else{
            $hot_search = Search::groupBy('keywords')->orderBy('num','desc')->limit(6)->get();
        }
        return $this->success($hot_search);
    }

    /**
     * @api {get} /api/v4/search/search   全局搜索
     * @apiName search
     * @apiVersion 1.0.0
     * @apiGroup search
     *
     * @apiParam {int} keywords   关键字
     * @apiParam {string} flag   类型(商品:only_goods)
     *
     * @apiSuccess {string} result json
     * @apiSuccessExample Success-Response:
    {
    "code": 200,
    "msg": "成功",
    "data": {
    "column": {
    "res": [],
    "count": 0
    },
    "works": {
    "res": [
    {
    "id": 18,
    "type": 2,
    "title": "如何培养高情商孩子",
    "user_id": 211172
    }
    ],
    "count": 1
    },
    "lecture": {
    "res": [],
    "count": 0
    },
    "listen_book": {
    "res": [],
    "count": 0
    },
    "goods": {
    "res": [
    {
    "id": 66,
    "name": "培养大器的孩子",
    "subtitle": "走出教育误区让孩子成为自己想成为的人",
    "original_price": "58.00",
    "price": "51.04",
    "picture": "/wechat/mall/goods/9210_1533089066.jpg"
    },
    {
    "id": 67,
    "name": "经营孩子的智慧",
    "subtitle": "激发孩子心中无限潜能让孩子成为一个充满智慧的人",
    "original_price": "50.00",
    "price": "44.00",
    "picture": "/wechat/mall/goods/4697_1533088898.jpg"
    },
    {
    "id": 122,
    "name": "孩子，你会更优秀（全套4册）",
    "subtitle": "德国教育家写给6~9岁孩子的心理疗愈童话",
    "original_price": "88.00",
    "price": "77.44",
    "picture": "/wechat/mall/goods/10000_1536646764.jpeg"
    },
    {
    "id": 184,
    "name": "钢铁是怎样练成的",
    "subtitle": "培养孩子在风暴中练就钢铁意志和崇高品德",
    "original_price": "35.80",
    "price": "31.50",
    "picture": "/wechat/mall/goods/8323_1533612770.png"
    },
    {
    "id": 202,
    "name": "小王子",
    "subtitle": "法国经典名著 滋养孩子心灵的精神财富",
    "original_price": "22.00",
    "price": "19.36",
    "picture": "/wechat/mall/goods/4517_1533625832.png"
    },
    {
    "id": 205,
    "name": "伊索寓言",
    "subtitle": "古希腊经典名著 培养孩子博爱、善良和真诚的品质",
    "original_price": "25.00",
    "price": "22.00",
    "picture": "/wechat/mall/goods/8155_1533626464.png"
    },
    {
    "id": 209,
    "name": "儿童财商绘本(全10册)",
    "subtitle": "帮助孩子建立正确的金钱观念 从容面对金钱问题",
    "original_price": "180.00",
    "price": "158.40",
    "picture": "/wechat/mall/goods/625_1544239955.png"
    },
    {
    "id": 262,
    "name": "蒙台梭利教育精华",
    "subtitle": "让孩子自信又独立",
    "original_price": "39.90",
    "price": "35.11",
    "picture": "/wechat/mall/goods/3835_1542853822.jpeg"
    },
    {
    "id": 328,
    "name": "我不要上幼儿园",
    "subtitle": "了解孩子的内心世界，增进亲子情感的更好途径 [3-6岁]",
    "original_price": "35.00",
    "price": "30.80",
    "picture": "/wechat/mall/goods/454_1545200190.png"
    },
    {
    "id": 333,
    "name": "儿童情商社交游戏绘本",
    "subtitle": "经典游戏力大奖版权引进绘本童书大师给孩子的25堂情商课",
    "original_price": "375.00",
    "price": "375.00",
    "picture": "/nlsg/goods/20191106174941142518.jpg"
    },
    {
    "id": 348,
    "name": "忍住！别插手",
    "subtitle": "让孩子从3岁开始学习独立的自我管理课",
    "original_price": "108.00",
    "price": "95.04",
    "picture": "/wechat/mall/goods/190313/4328_1547707310.png"
    },
    {
    "id": 370,
    "name": "如何读懂孩子的行为",
    "subtitle": "理解并解决孩子各种行为问题的方法",
    "original_price": "32.00",
    "price": "28.16",
    "picture": "/nlsg/goods/20191101173308844950.png"
    },
    {
    "id": 371,
    "name": "教室里的正面管教",
    "subtitle": "培养孩子人生技能造就理想班级氛围的“黄金准则”",
    "original_price": "30.00",
    "price": "30.00",
    "picture": "/wechat/mall/goods/190313/5896_1552013600.png"
    },
    {
    "id": 372,
    "name": "十几岁孩子的正面管教",
    "subtitle": "养育青春期十几岁孩子的“黄金准则”",
    "original_price": "35.00",
    "price": "30.80",
    "picture": "/wechat/mall/goods/190313/6206_1552013731.png"
    },
    {
    "id": 373,
    "name": "3～6岁孩子的正面管教",
    "subtitle": "家庭教育畅销书养育3～6岁孩子的“黄金准则”",
    "original_price": "42.00",
    "price": "36.96",
    "picture": "/wechat/mall/goods/190313/9795_1552013918.png"
    },
    {
    "id": 374,
    "name": "正面管教A-Z",
    "subtitle": "以实例讲解不惩罚不娇纵管教孩子的“黄金准则”",
    "original_price": "45.00",
    "price": "39.60",
    "picture": "/wechat/mall/goods/190313/6563_1552014044.png"
    },
    {
    "id": 377,
    "name": "正面管教",
    "subtitle": "如何不惩罚、不娇纵地有效管教孩子",
    "original_price": "38.00",
    "price": "33.44",
    "picture": "/wechat/mall/goods/190313/3405_1552386629.png"
    },
    {
    "id": 378,
    "name": "孩子，把你的手给我",
    "subtitle": "",
    "original_price": "32.00",
    "price": "28.16",
    "picture": "/nlsg/goods/20191101173401613891.png"
    },
    {
    "id": 379,
    "name": "如何培养孩子的社会能力",
    "subtitle": "教孩子学会解决冲突和与人相处的技巧",
    "original_price": "30.00",
    "price": "26.40",
    "picture": "/wechat/mall/goods/190313/2941_1552386834.png"
    },
    {
    "id": 391,
    "name": "艺术启蒙绘画套装",
    "subtitle": "123件不同绘画材料定制套盒满足孩子的绘画天赋",
    "original_price": "199.00",
    "price": "189.05",
    "picture": "/wechat/mall/goods/5460_1554891417.png"
    },
    {
    "id": 394,
    "name": "科学盒子基础版",
    "subtitle": "一个盒子12个科学实验包 成就每个孩子的科学梦",
    "original_price": "199.00",
    "price": "189.05",
    "picture": "/wechat/mall/goods/3254_1555654752.png"
    },
    {
    "id": 398,
    "name": "家庭版蒙氏教具-感官系列",
    "subtitle": "感官是心灵的门户为孩子打开认识世界的大门",
    "original_price": "729.00",
    "price": "692.55",
    "picture": "/wechat/mall/goods/2080_1559282072.png"
    },
    {
    "id": 415,
    "name": "儿童绘画与心理发展（6~9岁）",
    "subtitle": "帮助孩子释放不良情绪提升亲子关系质量",
    "original_price": "49.80",
    "price": "43.82",
    "picture": "/nlsg/goods/20190906095752891447.png"
    },
    {
    "id": 416,
    "name": "儿童绘画与心理发展（9~12岁）",
    "subtitle": "读懂儿童绘画走进孩子心里让亲子关系融洽和谐",
    "original_price": "49.80",
    "price": "43.82",
    "picture": "/nlsg/goods/20190906152020393177.png"
    },
    {
    "id": 421,
    "name": "与原生家庭和解",
    "subtitle": "童年早期的生活和教养经历是决定孩子一生快乐与否的关键",
    "original_price": "42.00",
    "price": "36.96",
    "picture": "/nlsg/goods/20190906161418147159.png"
    },
    {
    "id": 424,
    "name": "正面管教儿童心理学",
    "subtitle": "一部家长对孩子感到迷茫困惑不知所措时的应急手册",
    "original_price": "35.00",
    "price": "30.80",
    "picture": "/nlsg/goods/20190907095339193806.png"
    },
    {
    "id": 428,
    "name": "轻松做父母只需100天",
    "subtitle": "100天掌握孩子管教那些事儿让你轻松做父母",
    "original_price": "49.80",
    "price": "49.80",
    "picture": "/nlsg/goods/20190924100547509956.png"
    },
    {
    "id": 431,
    "name": "戒掉孩子的拖延症",
    "subtitle": "从孩子拖延的诱因出发28种拖延类型逐一解决",
    "original_price": "39.80",
    "price": "35.02",
    "picture": "/nlsg/goods/20191015140711849806.png"
    },
    {
    "id": 432,
    "name": "妈妈心态决定孩子状态",
    "subtitle": "一本书解决孩子成长中的心理问题轻松摆脱育儿焦虑",
    "original_price": "45.00",
    "price": "39.60",
    "picture": "/nlsg/goods/20191015141138501974.png"
    },
    {
    "id": 433,
    "name": "如何培养孩子的沟通力",
    "subtitle": "8大关键步骤培养让孩子受益一生的高情商竞争力",
    "original_price": "45.00",
    "price": "45.00",
    "picture": "/nlsg/goods/20191015141416747915.png"
    },
    {
    "id": 434,
    "name": "如何培养孩子的学习力",
    "subtitle": "5大关键步骤培养让孩子受益一生的学习思维",
    "original_price": "45.00",
    "price": "45.00",
    "picture": "/nlsg/goods/20191015142050532443.png"
    },
    {
    "id": 436,
    "name": "如何培养孩子的专注力",
    "subtitle": "6大关键步骤培养让孩子受益一生的专注力",
    "original_price": "45.00",
    "price": "45.00",
    "picture": "/nlsg/goods/20191015142748189211.png"
    },
    {
    "id": 438,
    "name": "自私的父母",
    "subtitle": "改善与父母的亲子关系与自己的孩子更好地相处",
    "original_price": "38.00",
    "price": "33.44",
    "picture": "/nlsg/goods/20191015143444811573.png"
    },
    {
    "id": 461,
    "name": "0~3岁孩子的正面管教",
    "subtitle": "影响孩子一生的头三年养育0～3岁孩子的“黄金准则”",
    "original_price": "42.00",
    "price": "36.96",
    "picture": "/nlsg/goods/20191123133332508621.png"
    },
    {
    "id": 472,
    "name": "儿童教育心理学",
    "subtitle": "如何正确参与孩子的成长理解孩子的心理情绪",
    "original_price": "38.00",
    "price": "33.44",
    "picture": "/nlsg/goods/20191226180419234794.jpg"
    },
    {
    "id": 486,
    "name": "写给孩子的数学三书",
    "subtitle": "奇妙的数学趣味百科365知识文化读物",
    "original_price": "99.00",
    "price": "99.00",
    "picture": "/nlsg/goods/20200304193842998885.jpg"
    }
    ],
    "count": 36
    }
    }
    }
     */
    public function search(Request $request)
    {
        $keywords = $request->input('keywords','');
        $user_id = $request->input('user_id',0);
        $flag = $request->input('flag','');
        $app_project_type = $request->input('app_project_type',1);

        if($keywords === ''){
            return $this->error(0,'关键字为空');
        }

        $keywords = $this->filterEmoji($keywords);

        if($flag == 'only_goods'){
            //商品
            $res['goods'] = MallGoods::search($keywords);
        }else{
            //搜索专栏
            $res['column'] = ['res' => [], 'count'=> 0 ];//Column::search($keywords,1);
            //课程
            $res['works'] = Works::search($keywords,0,$app_project_type);
            //讲座
            $res['lecture'] = Column::search($keywords,2);
            //听书
            $res['listen_book'] = Works::search($keywords,1,$app_project_type);
            //百科
            $res['Wiki'] = Wiki::search($keywords);
            //用户
            //商品
            $res['goods'] = MallGoods::search($keywords);
            //线下门票
            $res['products'] = OfflineProducts::search($keywords);
            $res['vip']['res'][] = ['id'=>1,'type' => 6, 'text'=>'幸福360会员','img'=>'/nlsg/works/20210105102849884378.png','price'=>360.00];
            $res['live'] = Live::search($keywords,$user_id,$app_project_type);
            $res['live_urls'] = LiveUrl::search($keywords);
            $res['camps'] = Column::search($keywords,3,$app_project_type);
            $res['xfxs_vip']['res'] = [];
            if($app_project_type == 2){
                // 幸福学社合伙人
                $res['xfxs_vip']['res'][] = ['id'=>1,'type' => 106, 'text'=>'幸福学社合伙人','img'=>'/nlsg/works/20210105102849884378.png','price'=>XfxsVip::NEW_PRICE];
            }

        }


        //搜索入库
        $SearchData = Search::firstOrCreate([
            'keywords'  =>$keywords,
            'user_id'   =>$user_id,
        ]);
        if(!$SearchData->wasRecentlyCreated){
            //搜索数自增
            Search::where('id', $SearchData->id)->increment('num');
        }

        return $this->success($res);
    }

    function filterEmoji($str) {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);
        return $str;
    }


}
