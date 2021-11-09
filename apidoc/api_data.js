define({ "api": [
  {
    "type": "get",
    "url": "/api/v4/vip/all_works",
    "title": "所有作品列表",
    "version": "4.0.0",
    "name": "_api_v4_vip_all_works",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/all_works"
      }
    ],
    "description": "<p>所有作品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "category_id",
            "description": "<p>分类id(全部空或者0)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "category",
            "description": "<p>分类数据</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>分类数据</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.works_type",
            "description": "<p>课程类型(1 视频 2音频)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.column_info.type",
            "description": "<p>类型(1专栏  2讲座)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.column_info.column_type",
            "description": "<p>专栏类型(1多课程   2单个课程)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1605173913,\n\"data\": {\n\"category\": [\n{\n\"id\": 1,\n\"name\": \"家庭关系\"\n},\n{\n\"id\": 21,\n\"name\": \"婚姻情感\"\n},\n{\n\"id\": 22,\n\"name\": \"家庭育儿\"\n},\n{\n\"id\": 24,\n\"name\": \"人文历史\"\n},\n{\n\"id\": 25,\n\"name\": \"个人成长\"\n}\n],\n\"list\": [\n{\n\"id\": 568,\n\"works_type\": 2,\n\"title\": \"家庭情境教育工具卡\",\n\"subtitle\": \"经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！\",\n\"cover_img\": \"/nlsg/works/20200304023146969654.jpg\",\n\"detail_img\": \"/nlsg/works/20200304023153543701.jpg\",\n\"price\": \"0.00\",\n\"column_id\": 61,\n\"category_relation\": [\n{\n\"id\": 1031,\n\"work_id\": 568,\n\"category_id\": 24,\n\"category_name\": {\n\"id\": 24,\n\"name\": \"人文历史\"\n}\n},\n{\n\"id\": 1040,\n\"work_id\": 568,\n\"category_id\": 24,\n\"category_name\": {\n\"id\": 24,\n\"name\": \"人文历史\"\n}\n}\n],\n\"column_info\": {\n\"id\": 61,\n\"type\": 1,\n\"column_type\": 1\n}\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "post",
    "url": "/api/v4/vip/code_create",
    "title": "生成兑换券",
    "version": "4.0.0",
    "name": "_api_v4_vip_code_create",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/code_create"
      }
    ],
    "description": "<p>生成兑换券</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "num",
            "description": "<p>数量</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "post",
    "url": "/api/v4/vip/code_get",
    "title": "领取兑换券",
    "version": "4.0.0",
    "name": "_api_v4_vip_code_get",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/code_get"
      }
    ],
    "description": "<p>领取兑换券</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>记录id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "get",
    "url": "/api/v4/vip/code_list",
    "title": "兑换券列表和详情",
    "version": "4.0.0",
    "name": "_api_v4_vip_code_list",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/code_list"
      }
    ],
    "description": "<p>兑换券和详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "id",
            "description": "<p>如果传id,就是单条</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3",
              "4",
              "5"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>状态(1未使用 2已使用 3赠送中 4已送出 5已使用加已送出)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "ob",
            "description": "<p>排序(t_asc时间正序,t_desc时间逆序)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>记录id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "redeem_code_id",
            "description": "<p>兑换码id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3",
              "4"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1未使用 2已使用 3赠送中 4已送出)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": true,
            "field": "qr_code",
            "description": "<p>二维码(完整url,当指定id且状态为1未使用时返回)</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "code_info",
            "description": "<p>详情</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "code_info.name",
            "description": "<p>兑换券名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "code_info.number",
            "description": "<p>兑换券编码</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "user_info",
            "description": "<p>用户详情</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "statistics",
            "description": "<p>生成配额</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "statistics.can_use",
            "description": "<p>可用配额</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1604988837,\n\"data\": [\n{\n\"id\": 10,\n\"redeem_code_id\": 10,\n\"status\": 1,\n\"created_at\": \"2020-09-22 12:18:06\",\n\"price\": 360,\n\"code_info\": {\n\"id\": 10,\n\"name\": \"360幸福大使\",\n\"number\": \"20265016893400009\"\n}\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "put",
    "url": "/api/v4/vip/code_send",
    "title": "赠送兑换券",
    "version": "4.0.0",
    "name": "_api_v4_vip_code_send",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/code_send"
      }
    ],
    "description": "<p>赠送兑换券</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>记录id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "put",
    "url": "/api/v4/vip/code_take_back",
    "title": "取消赠送兑换券",
    "version": "4.0.0",
    "name": "_api_v4_vip_code_take_back",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/code_take_back"
      }
    ],
    "description": "<p>取消赠送兑换券</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>记录id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "put",
    "url": "/api/v4/vip/code_use",
    "title": "使用兑换券",
    "version": "4.0.0",
    "name": "_api_v4_vip_code_use",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/code_use"
      }
    ],
    "description": "<p>使用兑换券</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>记录id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "get",
    "url": "/api/v4/vip/explain",
    "title": "说明",
    "version": "4.0.0",
    "name": "_api_v4_vip_explain",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/explain"
      }
    ],
    "description": "<p>说明</p>",
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "get",
    "url": "/api/v4/vip/home_page",
    "title": "会员详情页",
    "version": "4.0.0",
    "name": "_api_v4_vip_home_page",
    "group": "360会员",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/vip/home_page"
      }
    ],
    "description": "<p>会员详情页</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "card_data",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "card_data.nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "card_data.headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "card_data.level",
            "description": "<p>级别(1:360 2:钻石)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "card_data.expire_time",
            "description": "<p>到期时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "card_data.surplus_days",
            "description": "<p>剩余天数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "card_data.price",
            "description": "<p>价钱</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "card_data.is_open",
            "description": "<p>当前是否开通360(1开了 0没开)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "card_data.is_login",
            "description": "<p>当前是否登陆状态(1是 0不是)</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "author",
            "description": "<p>讲师</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "works_list",
            "description": "<p>课程列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "works_list.list",
            "description": "<p>课程列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works_list.list.works_type",
            "description": "<p>课程类型(1 视频 2音频)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works_list.list.type",
            "description": "<p>类型(1专栏  2讲座)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works_list.list.column_type",
            "description": "<p>专栏类型(1多课程   2单个课程)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "detail_image",
            "description": "<p>详情长图</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1605160146,\n\"data\": {\n\"card_data\": {\n\"nickname\": \"chandler\",\n\"headimg\": \"https://image.nlsgapp.com/image/202009/13f952e04c720a550193e5655534be86.jpg\",\n\"level\": 2,\n\"expire_time\": \"2020-11-20 23:59:59\",\n\"surplus_days\": 8,\n\"price\": \"360\",\n\"is_open\": 1\n},\n\"author\": {\n\"cover_img\": \"http://image.nlsgapp.com/nlsg/works/20201112134526746289.png\",\n\"list\": [\n{\n\"id\": 161904,\n\"nickname\": \"王琨\",\n\"headimg\": \"/wechat/authorpt/wk.png\",\n\"intro_for_360\": \"\"\n}\n]\n},\n\"works_list\": {\n\"cover_img\": \"http://image.nlsgapp.com/nlsg/works/20201112134456641863.png\",\n\"list\": [\n{\n\"id\": 568,\n\"works_type\": 2,\n\"title\": \"家庭情境教育工具卡\",\n\"subtitle\": \"经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！\",\n\"cover_img\": \"/nlsg/works/20200304023146969654.jpg\",\n\"detail_img\": \"/nlsg/works/20200304023153543701.jpg\",\n\"price\": \"0.00\",\n\"type\": 1,\n\"column_type\": 1\n}\n]\n},\n\"detail_image\": \"http://image.nlsgapp.com/nlsg/works/20201110171938316421.png\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/VipController.php",
    "groupTitle": "360会员"
  },
  {
    "type": "get",
    "url": "api/v4/user/fan",
    "title": "关注他的人",
    "version": "4.0.0",
    "group": "Api",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id 【我的 不用传user_id】</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "成功响应:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 6,\n\"from_uid\": 211172,\n\"to_uid\": 1,\n\"to_user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"is_follow\": 0\n},\n{\n\"id\": 9,\n\"from_uid\": 168934,\n\"to_uid\": 1,\n\"to_user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"is_follow\": 0\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "Api",
    "name": "GetApiV4UserFan"
  },
  {
    "type": "get",
    "url": "api/v4/user/feedback",
    "title": "我要吐槽",
    "version": "4.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>10:使用建议 11:内容漏缺 12:购物相关 13:物流配送 14:客服体验 15:节约相关</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容  不能大于200字</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pic",
            "description": "<p>图片url(数组格式)</p>"
          }
        ]
      }
    },
    "group": "Api",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n   \"code\": 200,\n   \"msg\" : '成功',\n   \"data\": []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "Api",
    "name": "GetApiV4UserFeedback"
  },
  {
    "type": "get",
    "url": "api/v4/user/follower",
    "title": "他关注的人",
    "version": "4.0.0",
    "group": "Api",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id [我的 不用传user_id】</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "成功响应:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 4,\n\"from_uid\": 1,\n\"to_uid\": 211172,\n\"from_user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"is_follow\": 0\n},\n{\n\"id\": 10,\n\"from_uid\": 1,\n\"to_uid\": 2,\n\"from_user\": {\n\"id\": 2,\n\"nickname\": \"刘尚\",\n\"headimg\": \"/wechat/works/headimg/70/2017102911145924225.png\"\n},\n\"is_follow\": 0\n},\n{\n\"id\": 12,\n\"from_uid\": 1,\n\"to_uid\": 168934,\n\"from_user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"is_follow\": 0\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "Api",
    "name": "GetApiV4UserFollower"
  },
  {
    "type": "post",
    "url": "api/v4/like",
    "title": "点赞",
    "version": "4.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          },
          {
            "group": "Parameter",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1.想法 2.百科</p>"
          }
        ]
      }
    },
    "group": "Api",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>token</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n   \"code\": 200,\n   \"msg\" : '成功',\n   \"data\": {\n\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LikeController.php",
    "groupTitle": "Api",
    "name": "PostApiV4Like"
  },
  {
    "type": "post",
    "url": "api/v4/unlike",
    "title": "取消点赞",
    "version": "4.0.0",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          },
          {
            "group": "Parameter",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1.想法 2.百科</p>"
          }
        ]
      }
    },
    "group": "Api",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>token</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n   \"code\": 200,\n   \"msg\" : '成功',\n   \"data\": {\n\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LikeController.php",
    "groupTitle": "Api",
    "name": "PostApiV4Unlike"
  },
  {
    "type": "post",
    "url": "api/v4/auth/cancel_user",
    "title": "注销账号",
    "version": "4.0.0",
    "name": "cancel_user",
    "group": "Auth",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/auth/clear_user"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>type1 第一次提交  2二次提交 3 取消注销</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AuthController.php",
    "groupTitle": "Auth"
  },
  {
    "type": "POST",
    "url": "api/v4/auth/login",
    "title": "登录",
    "version": "4.0.0",
    "name": "login",
    "group": "Auth",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/auth/login"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "code",
            "description": "<p>验证码</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "inviter",
            "description": "<p>推荐人id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>token</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n  \"token\": \"eyJ0eXAiOiJKV1QiLCJhbGv1yTnhQxjIvle_zFN5mI_zzTQUBhSgwI\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AuthController.php",
    "groupTitle": "Auth"
  },
  {
    "type": "get",
    "url": "api/v4/auth/logout",
    "title": "退出",
    "version": "4.0.0",
    "name": "logout",
    "group": "Auth",
    "success": {
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n   \"code\": 200,\n   \"msg\" : '成功',\n   \"data\": {\n\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AuthController.php",
    "groupTitle": "Auth"
  },
  {
    "type": "get",
    "url": "api/v4/auth/sms",
    "title": "发送验证码",
    "version": "4.0.0",
    "name": "sendSms",
    "group": "Auth",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/auth/sendsms"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AuthController.php",
    "groupTitle": "Auth"
  },
  {
    "type": "post",
    "url": "api/v4/auth/visitorLogin",
    "title": "游客登录",
    "version": "4.0.0",
    "name": "visitorLogin",
    "group": "Auth",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/auth/visitorLogin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "unionid",
            "description": "<p>设备号</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "sig",
            "description": "<p>sig</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AuthController.php",
    "groupTitle": "Auth"
  },
  {
    "type": "get",
    "url": "api/v4/auth/wechat",
    "title": "微信授权",
    "version": "4.0.0",
    "name": "wechat",
    "group": "Auth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "optional": false,
            "field": "code",
            "description": "<p>授权码</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token",
            "description": "<p>token</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n   \"code\": 200,\n   \"msg\" : '成功',\n   \"data\": {\n\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AuthController.php",
    "groupTitle": "Auth"
  },
  {
    "type": "get",
    "url": "api/v4/auth/wechat_info",
    "title": "微信授权",
    "version": "4.0.0",
    "name": "wechat_info",
    "group": "Auth",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "optional": false,
            "field": "code",
            "description": "<p>授权码</p>"
          },
          {
            "group": "Parameter",
            "optional": false,
            "field": "type",
            "description": "<p>1 获取openid 默认1  0 微信信息</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "openid",
            "description": "<p>openid</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n   \"code\": 200,\n   \"msg\" : '成功',\n   \"data\": {\n\n    }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AuthController.php",
    "groupTitle": "Auth"
  },
  {
    "type": "get",
    "url": "/api/v4/column/collection",
    "title": "收藏[专栏、课程、商品]",
    "name": "collection",
    "version": "1.0.0",
    "group": "Column",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>type 1专栏  2课程  3商品  4书单 5百科 6听书 7讲座  8训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "target_id",
            "description": "<p>对应id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "info_id",
            "description": "<p>如果是课程 需要传当前章节</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ColumnController.php",
    "groupTitle": "Column"
  },
  {
    "type": "get",
    "url": "/api/v4/column/get_column_detail",
    "title": "讲座(训练营)详细信息",
    "name": "get_column_detail",
    "version": "1.0.0",
    "group": "Column",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "column_id",
            "description": "<p>专栏id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id  默认0</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"column_info\": {\n\"id\": 1,\n\"name\": \"王琨专栏\",\n\"type\": 1,\n\"user_id\": 211172,\n\"message\": \"\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"online_time\": 0,\n\"works_update_time\": 0,\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"details_pic\": \"\",\n\"is_end\": 0,\n\"subscribe_num\": 0,\n\"teacher_name\": \"房爸爸\",\n\"is_sub\": 0\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ColumnController.php",
    "groupTitle": "Column"
  },
  {
    "type": "get",
    "url": "/api/v4/column/get_column_list",
    "title": "专栏-专栏|讲座首页列表",
    "name": "get_column_list",
    "version": "1.0.0",
    "group": "Column",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "page",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order",
            "description": "<p>1默认倒序 2正序</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>1专栏  2讲座   3训练营</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 1,\n\"name\": \"王琨专栏\",   标题\n\"type\": 1,              //类型 1专栏  2讲座\n\"user_id\": 211172,\n\"message\": \"\",                  //介绍\n\"original_price\": \"0.00\",   //原价\n\"price\": \"0.00\",            // 金额\n\"online_time\": 0,\n\"works_update_time\": 0,             //更新时间\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",  //封面图\n\"details_pic\": \"\"               //详情图\n\"is_new\": 0               //是否最新\n\"is_sub\": 0               //是否购买【订阅】\n\"work_name\": 0            //最新章节\n\"subscribe_num\": 0            //在学人数\n\"info_num\": 0            //总章节数量「针对讲座」\n},\n{\n\"id\": 2,\n\"name\": \"张宝萍专栏\",\n\"type\": 1,\n\"user_id\": 1,\n\"message\": \"\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"online_time\": 0,\n\"works_update_time\": 0,\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"details_pic\": \"\"\n},\n{\n\"id\": 3,\n\"name\": \"王复燕专栏\",\n\"type\": 1,\n\"user_id\": 211171,\n\"message\": \"\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"online_time\": 0,\n\"works_update_time\": 0,\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"details_pic\": \"\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ColumnController.php",
    "groupTitle": "Column"
  },
  {
    "type": "get",
    "url": "/api/v4/column/get_column_works",
    "title": "专栏-专栏详情[课程列表(单\\多课程列表)]",
    "name": "get_column_works",
    "version": "1.0.0",
    "group": "Column",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "column_id",
            "description": "<p>专栏id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id  默认0</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"column_info\": {\n\"id\": 1,\n\"name\": \"王琨专栏\",\n\"type\": 1,\n\"user_id\": 211172,\n\"message\": \"\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"online_time\": 0,\n\"works_update_time\": 0,\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"details_pic\": \"\",  //详情图\n\"is_end\": 0,            //是否完结  1完结\n\"subscribe_num\": 0,     //订阅数\n\"teacher_name\": \"房\",      //老师姓名\n\"is_sub\": 0             //是否订阅\n\"is_follow\": 0             //是否关注\n},\n\"works_data\": [         //多课程\n{\n\"id\": 16,\n\"type\": 1,\n\"title\": \"如何经营幸福婚姻\",            //课程\n\"cover_img\": \"/nlsg/works/20190822150244797760.png\",   //课程封面\n\"detail_img\": \"/nlsg/works/20191023183946478177.png\",   //课程详情\n\"message\": null,\n\"is_pay\": 1,        //是否精品课\n\"is_end\": 1,        //是否完结\n\"is_free\": 0,       //是否免费 1是\n\"subscribe_num\": 287,       关注数\n\"is_sub\": 0         用户是否购买\n},\n],\n\"outline_data\": [],         //单课程  大纲\n\"historyData\": [],          //历史章节\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ColumnController.php",
    "groupTitle": "Column"
  },
  {
    "type": "get",
    "url": "/api/v4/column/get_lecture_list",
    "title": "讲座目录  针对讲座和训练营[讲座与课程一对一]",
    "name": "get_lecture_list",
    "version": "1.0.0",
    "group": "Column",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "lecture_id",
            "description": "<p>讲座id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id  默认0</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order",
            "description": "<p>asc和 desc  默认asc</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"works_data\": {\n\"id\": 16,\n\"title\": \"如何经营幸福婚姻\",  //标题\n\"subtitle\": \"\",             //副标题\n\"cover_img\": \"/nlsg/works/20190822150244797760.png\",   //封面\n\"detail_img\": \"/nlsg/works/20191023183946478177.png\",   //详情图\n\"content\": \"<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>\",\n\"view_num\": 1295460,     //浏览数\n\"price\": \"29.90\",\n\"subscribe_num\": 287,       关注数\n\"is_free\": 0,\n\"is_end\": 1,\n\"info_num\": 2       //现有章节数\n\"history_ount\": 2%       //总进度\n},\n\"info\": [\n{\n\"id\": 2,\n\"type\": 1,\n\"title\": \"02坚毅品格的重要性\",\n\"section\": \"第二章\",       //章节数\n\"introduce\": \"第二章\",     //章节简介\n\"view_num\": 246,        //观看数\n\"duration\": \"03:47\",\n\"free_trial\": 0,     //是否可以免费试听\n\"href_url\": \"\",\n\"time_leng\": \"10\",      //观看 百分比\n\"time_number\": \"5\"      //观看 分钟数\n},\n{\n\"id\": 3,\n\"type\": 2,\n\"title\": \"03培养坚毅品格的方法\",\n\"section\": \"第三章\",\n\"introduce\": \"第三章\",\n\"view_num\": 106,\n\"duration\": \"09:09\",\n\"free_trial\": 0,\n\"href_url\": \"\",\n\"time_leng\": \"10\",\n\"time_number\": \"5\"\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ColumnController.php",
    "groupTitle": "Column"
  },
  {
    "type": "get",
    "url": "api/v4/column/get_lecture_study_list",
    "title": "在学列表",
    "name": "get_lecture_study_list",
    "version": "1.0.0",
    "group": "Column",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "lecture_id",
            "description": "<p>讲座id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"data\": [\n{\n\"id\": 3,\n\"user_id\": 211172,\n\"user_info\": {\n\"id\": 211172,\n\"level\": 0,\n\"username\": \"15650701817\",\n\"nickname\": \"能量时光\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n}\n],\n\"last_page\": 1,\n\"per_page\": 20,\n\"total\": 1\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ColumnController.php",
    "groupTitle": "Column"
  },
  {
    "type": "get",
    "url": "/api/v4/column/get_recommend",
    "title": "相关推荐[专栏|课程]",
    "name": "get_recommend",
    "version": "1.0.0",
    "group": "Column",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "target_id",
            "description": "<p>详情对应的id 专栏id或课程id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1.专栏 2.课堂 3. 讲座 4.书单 5. 百科 6.社区 7.直播 8.好物  9听书</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "position",
            "description": "<p>位置 1.首页 2专栏详情  3 课程详情    4精选书单详情  5听书详情   6讲座详情</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\ncode: 200,\nmsg: \"成功\",\nnow: 1606557279,\ndata: [\n{\nid: 18,\nname: \"邱柏森专栏\",\ncolumn_type: 1,\ntitle: \"美国正面管教协会家长/学校双证讲师\",\nsubtitle: \"教练式正面管教 落地有效\",\nmessage: \"能量时光，只做家庭教育一件事。大家好，感谢大家关注王琨专栏。今天开始将给大家分享《智慧育儿，听琨来说》系列课程，当然想要听到更为精彩、更为全面的内容，欢迎大家在课程下面留下您精彩的评论。下面我将继续深挖家庭教育优质的课题，持续将优质的家庭教育内容提供给大家。\",\nprice: \"79.50\",\ncover_pic: \"/wechat/works/video/161627/2017121117542896850.jpg\",\nchapter_num: 5,\nis_free: 0,\nis_new: 1,\nrecommend_type: 1\n},\n{\nid: 17,\nname: \"能量时光\",\ncolumn_type: 1,\ntitle: \"让知识变得有温度\",\nsubtitle: \"让知识变得有温度\",\nmessage: \"\",\nprice: \"0.00\",\ncover_pic: \"/wechat/works/video/1/2017082810100337412.jpg\",\nchapter_num: 0,\nis_free: 1,\nis_new: 1,\nrecommend_type: 1\n},\n{\nid: 573,\ncolumn_id: 21,\ntype: 2,\nuser_id: 167861,\ntitle: \"女人情商100讲\",\ncover_img: \"/nlsg/works/20200331175459533892.jpg\",\nsubtitle: \"经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！\",\nprice: \"49.90\",\nis_free: 0,\nis_pay: 1,\nworks_update_time: null,\nchapter_num: 82,\nsub_num: 424,\nuser: {\nid: 167861,\nnickname: \"吴岩\",\nheadimg: \"/wechat/works/video/161627/2017121117553852488.jpg\"\n},\nis_new: 0,\nis_sub: 0,\nrecommend_type: 2\n},\n{\nid: 572,\ncolumn_id: 4,\ntype: 2,\nuser_id: 161904,\ntitle: \"《琨说：改变你人生的金句名言》\",\ncover_img: \"/nlsg/works/20200325181759219566.jpg\",\nsubtitle: \"经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！\",\nprice: \"9.90\",\nis_free: 0,\nis_pay: 1,\nworks_update_time: null,\nchapter_num: 6,\nsub_num: 18,\nuser: {\nid: 161904,\nnickname: \"王琨\",\nheadimg: \"/wechat/authorpt/wk.png\"\n},\nis_new: 0,\nis_sub: 0,\nrecommend_type: 2\n},\n{\nid: 570,\ncolumn_id: 23,\ntype: 2,\nuser_id: 168303,\ntitle: \"青春期叛逆孩子解救营\",\ncover_img: \"/nlsg/works/20200317132810420958.jpg\",\nsubtitle: \"经历过职场迷茫和彷徨的岁月，了解年轻人心中的情怀和现实之间的差异，所以《优秀的人，都敢对自己下狠手》中，没有无聊的励志和温情的鸡汤，而是真实的打拼和真诚的建议！\",\nprice: \"49.90\",\nis_free: 0,\nis_pay: 1,\nworks_update_time: null,\nchapter_num: 13,\nsub_num: 45,\nuser: {\nid: 168303,\nnickname: \"泺仪\",\nheadimg: \"/wechat/authorpt/ly.png\"\n},\nis_new: 0,\nis_sub: 0,\nrecommend_type: 2\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ColumnController.php",
    "groupTitle": "Column"
  },
  {
    "type": "post",
    "url": "api/v4/comment/destroy",
    "title": "删除想法",
    "version": "4.0.0",
    "name": "destroy",
    "group": "Comment",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/comment/destroy"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CommentController.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "api/v4/comment/forward/user",
    "title": "想法-转发列表",
    "version": "4.0.0",
    "name": "forward_user",
    "group": "Comment",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/comment/forward/user"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.nickname",
            "description": "<p>用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.headimg",
            "description": "<p>用户头像</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": [\n{\n\"id\": 7,\n\"user_id\": 168934,\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CommentController.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "api/v4/comment/list",
    "title": "想法的列表",
    "version": "4.0.0",
    "name": "index",
    "group": "Comment",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/comment/list?id=1&type=1"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1.专栏 2.讲座 3.听书 4.精品课 5.百科 6.训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>模块id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "info_id",
            "description": "<p>次级id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "order",
            "description": "<p>默认1  最新是2</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "self",
            "description": "<p>只看作者 1  默认0</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>发表的内容</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "forward_num",
            "description": "<p>转发数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "share_num",
            "description": "<p>分享数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "like_num",
            "description": "<p>喜欢数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "reply_num",
            "description": "<p>评论数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_like",
            "description": "<p>是否点赞 1 是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_quality",
            "description": "<p>是否精选</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>发布的用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.nickname",
            "description": "<p>用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.headimg",
            "description": "<p>用户头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "attach",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "attach.img",
            "description": "<p>图片地址</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply",
            "description": "<p>回复</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.content",
            "description": "<p>回复的内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.from_user",
            "description": "<p>评论者</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.to_user",
            "description": "<p>被回复者</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "quote",
            "description": "<p>引用</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "quote.content",
            "description": "<p>引用的内容</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": {\n\"current_page\": 1,\n\"data\": [\n{\n\"id\": 1,\n\"pid\": 0,\n\"user_id\": 168934,\n\"relation_id\": 1,\n\"content\": \"测试\",\n\"type\": 1,\n\"forward_num\": 0,\n\"share_num\": 0,\n\"like_num\": 0,\n\"reply_num\": 3,\n\"is_quality\": 0,\n\"created_at\": \"2020-06-10 07:25:04\",\n\"updated_at\": \"2020-07-07 18:55:59\",\n\"status\": 1,\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n},\n\"quote\": {\n\"pid\": 1,\n\"content\": \"说的不错啊\"\n},\n\"attach\": [\n{\n\"id\": 1,\n\"relation_id\": 1,\n\"img\": \"/wechat/mall/goods/3476_1533614056.png\"\n},\n{\n\"id\": 2,\n\"relation_id\": 1,\n\"img\": \"/wechat/mall/goods/3476_1533614056.png\"\n}\n],\n\"reply\": [\n{\n\"id\": 1,\n\"comment_id\": 1,\n\"from_uid\": 168934,\n\"to_uid\": 211172,\n\"content\": \"修改新内容\",\n\"from_user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n},\n\"to_user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\"\n}\n},\n{\n\"id\": 2,\n\"comment_id\": 1,\n\"from_uid\": 211172,\n\"to_uid\": 168934,\n\"content\": \"你也不错\",\n\"from_user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\"\n},\n\"to_user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n},\n{\n\"id\": 5,\n\"comment_id\": 1,\n\"from_uid\": 1,\n\"to_uid\": 168934,\n\"content\": \"不错\",\n\"from_user\": {\n\"id\": 1,\n\"nickname\": \"刘先森\"\n},\n\"to_user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n}\n]\n}\n],\n\"first_page_url\": \"http://v4.com/api/v4/comment/index?page=1\",\n\"from\": 1,\n\"last_page\": 7,\n\"last_page_url\": \"http://v4.com/api/v4/comment/index?page=7\",\n\"next_page_url\": \"http://v4.com/api/v4/comment/index?page=2\",\n\"path\": \"http://v4.com/api/v4/comment/index\",\n\"per_page\": 1,\n\"prev_page_url\": null,\n\"to\": 1,\n\"total\": 7\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CommentController.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "api/v4/comment/like/user",
    "title": "想法-喜欢列表",
    "version": "4.0.0",
    "name": "like_user",
    "group": "Comment",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/comment/like/user"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.nickname",
            "description": "<p>用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.headimg",
            "description": "<p>用户头像</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": [\n{\n\"id\": 6,\n\"user_id\": 1,\n\"user\": {\n\"id\": 1,\n\"nickname\": \"刘先森\",\n\"headimg\": \"https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png\"\n}\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CommentController.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "api/v4/comment/show",
    "title": "评论详情",
    "version": "4.0.0",
    "name": "show",
    "group": "Comment",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/comment/show?id=1&page=1"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>回复分页</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>评论的内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.nickname",
            "description": "<p>评论的用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.headimg",
            "description": "<p>评论的用户头像</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "forward_num",
            "description": "<p>转发数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "share_num",
            "description": "<p>分享数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "like_num",
            "description": "<p>喜欢数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "reply_num",
            "description": "<p>回复数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_quality",
            "description": "<p>是否精选</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "attach",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "attach.img",
            "description": "<p>图片地址</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column",
            "description": "<p>|works|wiki   type =1、2、6 \b专栏、讲座和训练营 返回 title、subtitle、cover_pic， type =3、4 听书和精品课 works 返回title subtitle、cover_img ， type=5 百科 wiki 返回name、cover</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply",
            "description": "<p>回复</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.content",
            "description": "<p>回复内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.from_user",
            "description": "<p>回复者     【 张三 form_user 回复 李四 to_user 】</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.from_user.nickname",
            "description": "<p>回复者昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.from_user.headimg",
            "description": "<p>回复者头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.to_user",
            "description": "<p>被回复者</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.to_user.nickname",
            "description": "<p>被回复者昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply.to_user.headimg",
            "description": "<p>被回复者头像</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\ndata\": {\n\"id\": 1,\n\"pid\": 0,\n\"user_id\": 168934,\n\"relation_id\": 1,\n\"content\": \"测试\",\n\"forward_num\": 0,\n\"share_num\": 0,\n\"like_num\": 0,\n\"reply_num\": 3,\n\"reward_num\": 3,\n\"reply\": [\n{\n\"id\": 1,\n\"from_uid\": 168934,\n\"to_uid\": 211172,\n\"from_user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"to_user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n},\n{\n\"id\": 2,\n\"from_uid\": 211172,\n\"to_uid\": 168934,\n\"from_user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"to_user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n}\n],\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"quote\": null,\n\"attach\": [\n{\n\"id\": 1,\n\"relation_id\": 1,\n\"img\": \"/wechat/mall/goods/3476_1533614056.png\"\n},\n{\n\"id\": 2,\n\"relation_id\": 1,\n\"img\": \"/wechat/mall/goods/3476_1533614056.png\"\n}\n],\n\"reward\": [\n{\n\"id\": 59,\n\"user_id\": 1,\n\"relation_id\": 1,\n\"user\": {\n\"id\": 1,\n\"nickname\": \"刘先森\",\n\"headimg\": \"https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png\"\n}\n},\n{\n\"id\": 60,\n\"user_id\": 211172,\n\"relation_id\": 1,\n\"user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n}\n]\n}\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CommentController.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "api/v4/comment/store",
    "title": "发表想法",
    "version": "4.0.0",
    "name": "store",
    "group": "Comment",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>模块id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "info_id",
            "description": "<p>次级id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "pid",
            "description": "<p>转发评论id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>发布的内容</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "img",
            "description": "<p>多个图片  格式 a.png,b.png,c.png</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>模块类型  类型 1.专栏 2.讲座 3.听书 4.精品课 5.百科 6.训练营</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CommentController.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "api/v4/comment/update",
    "title": "更新想法",
    "version": "4.0.0",
    "name": "update",
    "group": "Comment",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>模块id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>发表的内容</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CommentController.php",
    "groupTitle": "Comment"
  },
  {
    "type": "get",
    "url": "api/admin_v4/deal/get_order_info",
    "title": "获取成交订单",
    "version": "4.0.0",
    "name": "getOrderInfo",
    "group": "Deal",
    "filename": "../app/Http/Controllers/Admin/V4/DealController.php",
    "groupTitle": "Deal"
  },
  {
    "type": "post",
    "url": "/api/v4/home/redeem_code",
    "title": "兑换码",
    "version": "4.0.0",
    "name": "_api_v4_home_redeem_code",
    "group": "Home",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/home/redeem_code"
      }
    ],
    "description": "<p>兑换码</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "code",
            "description": "<p>兑换码</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "os_type",
            "description": "<p>系统( 1 安卓 2ios 3微信)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Home"
  },
  {
    "type": "get",
    "url": "/api/v4/home/redeem_code_list",
    "title": "兑换码列表",
    "version": "4.0.0",
    "name": "_api_v4_home_redeem_code_list",
    "group": "Home",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/home/redeem_code_list"
      }
    ],
    "description": "<p>兑换码列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>筛选(不传都饭,1是已使用,0是未使用)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Home"
  },
  {
    "type": "get",
    "url": "api/v4/wiki/show",
    "title": "百科-详情",
    "version": "4.0.0",
    "name": "banner",
    "group": "Index",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>百科id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "view_num",
            "description": "<p>浏览数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "like_num",
            "description": "<p>收藏数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comment_num",
            "description": "<p>评论数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n     \"data\": {\n\"name\": \"室内空气污染对孩子的危害\",\n\"content\": \"社会的进步，工业的发展，导致污染越来越严重，触目惊心\",\n\"cover\": \"/wechat/mall/goods/3264_1512448129.jpg\",\n\"view_num\": 10,\n\"like_num\": 2,\n\"comment_num\": 5\n}\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WikiController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/banner",
    "title": "首页-轮播图",
    "version": "4.0.0",
    "name": "banner",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "pic",
            "description": "<p>图片地址</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>链接地址</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OKr\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n              \"id\": 274,\n              \"pic\": \"https://image.nlsgapp.com/nlsg/banner/20191118184425289911.jpg\",\n              \"title\": \"电商弹窗课程日历套装\",\n              \"url\": \"/mall/shop-detailsgoods_id=448&time=201911091925\"\n          },\n          {\n              \"id\": 296,\n              \"pic\": \"https://image.nlsgapp.com/nlsg/banner/20191227171346601666.jpg\",\n              \"title\": \"心里学\",\n              \"url\": \"/mall/shop-details?goods_id=479\"\n          }\n   ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/book",
    "title": "首页-听书推荐",
    "version": "4.0.0",
    "name": "book",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_works",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_works.type",
            "description": "<p>2听书 4讲座</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_works.works",
            "description": "<p>讲座和听书</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":[\n{\n\"id\": 1,\n\"title\": \"世界名著必读，历经岁月经典依旧陪伴成长\",\n\"subtitle\": \"强烈推荐\",\n\"cover\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"num\"  :  5,\n\"works\": [\n{\n                     \"works_id\": 18,\n\"user_id\": 168934,\n\"title\": \"不要羞辱你的孩子 他的心很脆弱\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416324725316.jpg\",\n\"pivot\": {\n\"lists_id\": 1,\n\"works_id\": 30\n},\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n},\n{\n\"user_id\": 168934,\n\"title\": \"小孩子做噩梦怎么办？九成父母都没当回事\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416393315731.jpg\",\n\"pivot\": {\n\"lists_id\": 1,\n\"works_id\": 31\n},\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n}\n]\n}\n        ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/camp",
    "title": "首页-训练营",
    "version": "4.0.0",
    "name": "camp",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_new",
            "description": "<p>是否新上架 1是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover_pic",
            "description": "<p>封面</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n   ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/column",
    "title": "首页-大咖专栏",
    "version": "4.0.0",
    "name": "column",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>专栏名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_new",
            "description": "<p>是否新上架 1是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover_pic",
            "description": "<p>封面</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n              \"id\": 1,\n              \"name\": \"王琨专栏\",\n              \"title\": \"顶尖导师 经营能量\",\n              \"subtitle\": \"顶尖导师 经营能量\",\n              \"message\": \"\",\n              \"price\": \"99.00\",\n              \"is_new\": 1,\n              \"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\"\n          }\n\n   ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/course",
    "title": "首页-课程集合",
    "version": "4.0.0",
    "name": "course",
    "group": "Index",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/course"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>听书作品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>作品标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>作品封面</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":[\n{\n\"id\": 1,\n\"title\": \"世界名著必读，历经岁月经典依旧陪伴成长\",\n\"subtitle\": \"强烈推荐\",\n\"cover\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"num\"  :  5,\n\"works\": [\n{\n\"user_id\": 168934,\n\"title\": \"不要羞辱你的孩子 他的心很脆弱\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416324725316.jpg\",\n\"pivot\": {\n\"lists_id\": 1,\n\"works_id\": 30\n},\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n},\n{\n\"user_id\": 168934,\n\"title\": \"小孩子做噩梦怎么办？九成父母都没当回事\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416393315731.jpg\",\n\"pivot\": {\n\"lists_id\": 1,\n\"works_id\": 31\n},\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n}\n}\n]\n}\n        ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/editor",
    "title": "首页-主编推荐",
    "version": "4.0.0",
    "name": "editor",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reason",
            "description": "<p>推荐理由</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "relation_id",
            "description": "<p>跳转id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "relation_type",
            "description": "<p>1.课程 2.听书 3.专栏 4.讲座</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>课程</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.chapter_num",
            "description": "<p>章节数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.subscibe_num",
            "description": "<p>学习数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.nickname",
            "description": "<p>用户昵称</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": [\n{\n\"id\": 17,\n\"relation_id\": \"16\",\n\"relation_type\": 1,\n\"reason\": \"欣赏是一种享受，是一种实实在在的享受\",\n\"works\": {\n\"id\": 16,\n\"user_id\": 168934,\n\"title\": \"如何经营幸福婚姻\",\n\"subtitle\": \"\",\n\"cover_img\": \"/nlsg/works/20190822150244797760.png\",\n\"price\": \"29.90\",\n\"chapter_num\": 0,\n\"subscribe_num\": 287,\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n}\n},\n{\n\"id\": 18,\n\"relation_id\": \"2\",\n\"relation_type\": 4,\n\"reason\": \"值得学习\",\n\"works\": {\n\"id\": 2,\n\"user_id\": 1,\n\"name\": \"张宝萍专栏\",\n\"title\": \"国家十百千万工程心灵导师\",\n\"subtitle\": \"心灵导师 直击人心\",\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"price\": \"0.00\",\n\"user\": {\n\"id\": 1,\n\"nickname\": \"刘先森\"\n}\n}\n},\n{\n\"id\": 19,\n\"relation_id\": \"1\",\n\"relation_type\": 3,\n\"reason\": \"很好\",\n\"works\": {\n\"id\": 1,\n\"user_id\": 211172,\n\"name\": \"王琨专栏\",\n\"title\": \"顶尖导师 经营能量\",\n\"subtitle\": \"顶尖导师 经营能量\",\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"price\": \"99.00\",\n\"user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\"\n}\n}\n},\n{\n\"id\": 21,\n\"relation_id\": \"18\",\n\"relation_type\": 2,\n\"reason\": \"欣赏是一种享受，是一种实实在在的享受\",\n\"works\": {\n\"id\": 18,\n\"user_id\": 211172,\n\"title\": \"如何培养高情商孩子\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161910/1639_1525340866.png\",\n\"price\": \"0.00\",\n\"chapter_num\": 0,\n\"subscribe_num\": 0,\n\"user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\",\n\"headimg\" : \"test.png\"\n}\n}\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/event",
    "title": "商城活动标识",
    "version": "4.0.0",
    "name": "event",
    "group": "Index",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/event"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "pic",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>h5跳转链接</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>1 h5 2 app商品</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "obj_id",
            "description": "<p>商品id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/free",
    "title": "免费专区",
    "version": "4.0.0",
    "name": "free",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>课程</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works.is_new",
            "description": "<p>是否为new</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works.chapter_num",
            "description": "<p>课程章节数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "book",
            "description": "<p>听书</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "book.title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "book.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "book.cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "book.is_new",
            "description": "<p>是否为new</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "book.chapter_num",
            "description": "<p>听书章节数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":{\n\"works\": [\n{\n\"id\": 20,\n\"user_id\": 1,\n\"title\": \"理解孩子行为背后的原因\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161627/2017061411282192073.jpg\",\n\"is_new\": 1,\n\"user\": {\n\"id\": 1,\n\"nickname\": \"刘先森\"\n}\n}\n],\n\"book\": [\n{\n\"id\": 30,\n\"user_id\": 168934,\n\"title\": \"不要羞辱你的孩子 他的心很脆弱\",\n\"subtitle\": \"家庭教育\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416324725316.jpg\",\n\"is_new\": 1,\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n}\n]\n}\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/goods",
    "title": "首页-精选好物",
    "version": "4.0.0",
    "name": "goods",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "picture",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>现价</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n              \"id\": 48,\n              \"name\": \" 香港Mcomb儿童专用智能牙刷\",\n              \"picture\": \"/wechat/mall/mall/goods/8671_1519697106.png\",\n              \"original_price\": \"220.00\",\n              \"price\" : 220\n          }\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/announce",
    "title": "首页-公告",
    "version": "4.0.0",
    "name": "index",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n     \"code\": 200,\n     \"msg\" : '成功',\n     \"data\": {\n         \"id\": 1,\n         \"content\": \"测试\"\n      }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/lives",
    "title": "首页-直播推荐",
    "version": "4.0.0",
    "name": "live",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "describe",
            "description": "<p>描述</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_num",
            "description": "<p>预约人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>预约价格</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n              \"id\": 1,\n              \"title\": \"第85期《经营能量》直播\",\n              \"describe\": \"经营能量\",\n              \"cover_img\": \"/live/look_back/live-1-9.jpg\",\n              \"start_time\": null,\n              \"end_time\": null,\n              \"live_status\": \"已结束\"\n          }\n\n   ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/rank",
    "title": "首页-热门榜单",
    "version": "4.0.0",
    "name": "rank",
    "group": "Index",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/rank"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>听书作品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>作品标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>作品封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods",
            "description": "<p>商品排行榜</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods.list_goods.id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods.list_goods.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods.list_goods.price",
            "description": "<p>商品价格</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": {\n\"works\": [\n{\n\"id\": 8,\n\"title\": \"热门课程榜单\",\n\"works\": [\n{\n\"works_id\": 30,\n\"user_id\": 168934,\n\"title\": \"不要羞辱你的孩子 他的心很脆弱\",\n\"subtitle\": \"家庭教育\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416324725316.jpg\",\n\"chapter_num\": 8,\n\"subscribe_num\": 0,\n\"is_free\": 1,\n\"price\": \"0.00\",\n\"pivot\": {\n\"lists_id\": 8,\n\"works_id\": 30\n},\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n},\n{\n\"works_id\": 31,\n\"user_id\": 168934,\n\"title\": \"小孩子做噩梦怎么办？九成父母都没当回事\",\n\"subtitle\": \"家庭教育\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416393315731.jpg\",\n\"chapter_num\": 5,\n\"subscribe_num\": 0,\n\"is_free\": 1,\n\"price\": \"0.00\",\n\"pivot\": {\n\"lists_id\": 8,\n\"works_id\": 31\n},\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n},\n{\n\"works_id\": 32,\n\"user_id\": 1,\n\"title\": \"时间就像你手中的冰淇淋\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416424169642.jpg\",\n\"chapter_num\": 0,\n\"subscribe_num\": 0,\n\"is_free\": 0,\n\"price\": \"0.00\",\n\"pivot\": {\n\"lists_id\": 8,\n\"works_id\": 32\n},\n\"user\": {\n\"id\": 1,\n\"nickname\": \"刘先森\"\n}\n}\n]\n}\n],\n\"wiki\": [\n{\n\"id\": 9,\n\"title\": \"热门百科榜单\",\n\"content\": null,\n\"list_works\": [\n{\n\"id\": 16,\n\"lists_id\": 9,\n\"works_id\": 1,\n\"wiki\": {\n\"id\": 1,\n\"name\": \"室内空气污染对孩子的危害\",\n\"content\": \"社会的进步，工业的发展，导致污染越来越严重，触目惊心\",\n\"view_num\": 10,\n\"like_num\": 2,\n\"comment_num\": 5\n}\n},\n{\n\"id\": 17,\n\"lists_id\": 9,\n\"works_id\": 2,\n\"wiki\": {\n\"id\": 2,\n\"name\": \"世界名著必读岁月经典\",\n\"content\": \"每个时代都有极其红极广受好评\",\n\"view_num\": 5,\n\"like_num\": 6,\n\"comment_num\": 5\n}\n}\n]\n}\n]\n \"goods\": [\n{\n\"id\": 10,\n\"title\": \"热门商品榜单\",\n\"num\": 2,\n\"cover\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"goods\": [\n{\n\"works_id\": 48,\n\"name\": \" 香港Mcomb儿童专用智能牙刷\",\n\"price\": \"220.00\",\n\"pivot\": {\n\"lists_id\": 10,\n\"works_id\": 48\n}\n},\n{\n\"works_id\": 58,\n\"name\": \"得力 儿童益智绘画套装\",\n\"price\": \"90.00\",\n\"pivot\": {\n\"lists_id\": 10,\n\"works_id\": 58\n}\n},\n{\n\"works_id\": 60,\n\"name\": \"汉字奇遇-识字启蒙卡片\",\n\"price\": \"198.00\",\n\"pivot\": {\n\"lists_id\": 10,\n\"works_id\": 60\n}\n}\n]\n}\n]\n}\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/recommend",
    "title": "首页-每日琨说",
    "version": "4.0.0",
    "name": "recommend",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "subscribe_num",
            "description": "<p>订阅数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>课程标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "work_info",
            "description": "<p>章节</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "work_info.duration",
            "description": "<p>时长</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "work_info.title",
            "description": "<p>章节标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "work_info.view_num",
            "description": "<p>学习人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "work_info.is_new",
            "description": "<p>是否更新</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>作者</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.headimg",
            "description": "<p>作者头像</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":{\n\"id\": 34,\n\"subscribe_num\": 34234,\n\"title\": \"每日琨说\",\n\"work_info\": [\n{\n\"duration\": \"06:14\",\n\"id\": 15,\n\"is_new\": 1,\n\"online_time\": \"2020-08-10 08:10:00\",\n\"pid\": 34,\n\"rank\": 7,\n\"title\": \"006 | 父母和孩子为什么有沟通障碍？\",\n\"view_num\": 27426\n},\n{\n\"duration\": \"10:29\",\n\"id\": 14,\n\"is_new\": 0,\n\"online_time\": \"2020-05-10 08:10:00\",\n\"pid\": 34,\n\"rank\": 6,\n\"title\": \"005 | 六个字就可以让家族富过三代？\",\n\"view_num\": 30097\n}\n]\n\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/version",
    "title": "版本更新",
    "version": "4.0.0",
    "name": "version",
    "group": "Index",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/version"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "version",
            "description": "<p>版本号 4.0.0</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>更新内容</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/wiki",
    "title": "首页-小百科",
    "version": "4.0.0",
    "name": "wiki",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "view_num",
            "description": "<p>阅读数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "like_num",
            "description": "<p>收藏数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "comment_num",
            "description": "<p>评论数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n                 \"id\": 1,\n                  \"name\": \"室内空气污染对孩子的危害\",\n                  \"content\": \"社会的进步，工业的发展，导致污染越来越严重，触目惊心\",\n                  \"cover\": \"/wechat/mall/goods/3264_1512448129.jpg\",\n                  \"view_num\": 10,\n                  \"like_num\": 2,\n                  \"comment_num\": 5\n          },\n          {\n                 \"id\": 2,\n                 \"name\": \"世界名著必读岁月经典\",\n                 \"content\": \"每个时代都有极其红极广受好评\",\n                 \"cover\": \"/wechat/mall/mall/goods/389_1519697199.png\",\n                 \"view_num\": 5,\n                 \"like_num\": 6,\n                 \"comment_num\": 5\n          }\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "api/v4/index/works",
    "title": "首页-精选课程",
    "version": "4.0.0",
    "name": "works",
    "group": "Index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "is_new",
            "description": "<p>是否为新上架 1 是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "is_free",
            "description": "<p>是否为限免   1 是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>用户</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "user.id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.nickname",
            "description": "<p>用户昵称</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n              \"id\": 16,\n              \"user_id\": 168934,\n              \"title\": \"如何经营幸福婚姻\",\n              \"cover_img\": \"/nlsg/works/20190822150244797760.png\",\n              \"subtitle\": \"\",\n              \"price\": \"29.90\",\n              \"user\": {\n                 \"id\": 168934,\n                 \"nickname\": \"chandler\"\n              },\n              \"is_new\": 1,\n              \"is_free\": 1\n           }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IndexController.php",
    "groupTitle": "Index"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/group_buy_scrollbar",
    "title": "拼团滚动信息",
    "version": "1.0.0",
    "name": "_api_v4_goods_group_buy_scrollbar",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy_scrollbar"
      }
    ],
    "description": "<p>拼团滚动信息</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "group_buy_id",
            "description": "<p>拼团id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "explain",
            "description": "<p>说明</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>订单时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"user_id\": 168934,\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\",\n\"nickname\": \"chandler\",\n\"created_at\": \"2020-06-23 16:16:24\",\n\"is_captain\": 1,\n\"is_success\": 0,\n\"explain\": \"发起拼团\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/group_buy_team_list",
    "title": "拼团队伍信息",
    "version": "1.0.0",
    "name": "_api_v4_goods_group_buy_team_list",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy_team_list"
      }
    ],
    "description": "<p>拼团队伍信息</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "group_buy_id",
            "description": "<p>拼团id</p>"
          },
          {
            "group": "Parameter",
            "type": "numer",
            "optional": true,
            "field": "group_key",
            "description": "<p>拼团队伍标识</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": true,
            "field": "flag",
            "description": "<p>1只返回两条 2全返</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "group_name",
            "description": "<p>group_buy_id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>队长id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "end_at",
            "description": "<p>队伍失效时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>队长昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "group_num",
            "description": "<p>组队需要人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "group_key",
            "description": "<p>队伍标似</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_count",
            "description": "<p>队伍已有人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": true,
            "field": "show_self",
            "description": "<p>是否显示本人订单(0默认不显示 1显示)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 5,\n\"group_name\": \"111\",\n\"order_id\": 9560,\n\"created_at\": \"2020-06-28 18:13:52\",\n\"user_id\": 168934,\n\"is_success\": 0,\n\"success_at\": null,\n\"begin_at\": \"2020-06-28 18:13:52\",\n\"end_at\": \"2020-07-28 18:14:59\",\n\"nickname\": \"chandler\",\n\"headimg\": null,\n\"is_self\":1,\n\"group_num\": 4,\n\"group_key\": \"2006280016893465633736\",\n\"order_count\": 1\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/mall/comment_issue_list",
    "title": "评价原因列表",
    "version": "1.0.0",
    "name": "_api_v4_mall_comment_issue_list",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/comment_issue_list"
      }
    ],
    "description": "<p>评价原因列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "comment_id",
            "description": "<p>id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 1,\n\"val\": \"商品问题\"\n},\n{\n\"id\": 2,\n\"val\": \"客服问题\"\n},\n{\n\"id\": 3,\n\"val\": \"物流问题\"\n},\n{\n\"id\": 4,\n\"val\": \"其他问题\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/mall/comment_list",
    "title": "商品评论列表",
    "version": "1.0.0",
    "name": "_api_v4_mall_comment_list",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/comment_list"
      }
    ],
    "description": "<p>未评论商品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>标记(1已评价,未评价,3全部)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "order_id",
            "description": "<p>如果按订单筛选,传订单id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"order_id\": 9527,\n\"ordernum\": \"2006190016893436005551\",\n\"order_detail_id\": 10327,\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"comment_id\": 0,\n\"sku_value\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/create_flash_sale_order",
    "title": "秒杀订单下单",
    "version": "1.0.0",
    "name": "_api_v4_mall_create_flash_sale_order",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/create_flash_sale_order"
      }
    ],
    "description": "<p>秒杀订单下单(参数同预下单)</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sku",
            "description": "<p>sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "buy_num",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "post_type",
            "description": "<p>物流方式(1邮寄2自提)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_freight_id",
            "description": "<p>免邮券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "address_id",
            "description": "<p>选择的地址id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "os_type",
            "description": "<p>1安卓2苹果3微信</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"order_id\": 9530,\n\"ordernum\": \"2006190016893457221111\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/create_group_buy_order",
    "title": "拼团订单下单",
    "version": "1.0.0",
    "name": "_api_v4_mall_create_group_buy_order",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/create_group_buy_order"
      }
    ],
    "description": "<p>拼团订单下单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sku",
            "description": "<p>sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "buy_num",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "post_type",
            "description": "<p>物流方式(1邮寄2自提)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_freight_id",
            "description": "<p>免邮券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "address_id",
            "description": "<p>选择的地址id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "os_type",
            "description": "<p>1安卓2苹果3微信</p>"
          },
          {
            "group": "Parameter",
            "type": "buy_type",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "buy_type",
            "description": "<p>1开团 2参团</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "group_key",
            "description": "<p>如果是参团,需要传</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"sku\":\"1612728266\",\n\"goods_id\":209,\n\"buy_num\":1,\n\"inviter\":211172,\n\"post_type\":1,\n\"coupon_goods_id\":0,\n\"coupon_freight_id\":0,\n\"address_id\":2814,\n\"os_type\":1,\n\"buy_type\":1,\n\"group_key\":1\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"order_id\": 9555,\n\"ordernum\": \"2006230016893460198201\",\n\"group_key\": \"2006230016893460198117\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/create_order",
    "title": "普通订单下单",
    "version": "1.0.0",
    "name": "_api_v4_mall_create_order",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/create_order"
      }
    ],
    "description": "<p>普通订单下单(参数同预下单)</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "from_cart",
            "description": "<p>下单方式(1:购物车下单  2:立即购买</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sku",
            "description": "<p>sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "buy_num",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "post_type",
            "description": "<p>物流方式(1邮寄2自提)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_goods_id",
            "description": "<p>优惠券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_freight_id",
            "description": "<p>免邮券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "address_id",
            "description": "<p>选择的地址id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "os_type",
            "description": "<p>1安卓2苹果3微信</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "messages",
            "description": "<p>留言</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "bill_type",
            "description": "<p>发票选项(0为不开发票 1个人 2公司)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "bill_title",
            "description": "<p>发票抬头</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "bill_number",
            "description": "<p>纳税人识别号</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "bill_format",
            "description": "<p>发票类型(1：纸质 2：电子)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"order_id\": 9530,\n\"ordernum\": \"2006190016893457221111\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/flash_sale_pay_fail",
    "title": "秒杀订单支付未成功处理",
    "version": "1.0.0",
    "name": "_api_v4_mall_flash_sale_pay_fail",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/flash_sale_pay_fail"
      }
    ],
    "description": "<p>秒杀订单支付未成功处理</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "order_id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"code\": true,\n\"msg\": \"成功\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/mall/get_comment",
    "title": "查看评价",
    "version": "1.0.0",
    "name": "_api_v4_mall_get_comment",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/get_comment"
      }
    ],
    "description": "<p>查看评价</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "comment_id",
            "description": "<p>id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"id\": 973,\n\"content\": \"\",\n\"picture\": [],\n\"star\": 5,\n\"status\": 1,\n\"issue_type\": []\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/mall/group_buy_order_info",
    "title": "拼团订单详情",
    "version": "1.0.0",
    "name": "_api_v4_mall_group_buy_order_info",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/group_buy_order_info"
      }
    ],
    "description": "<p>拼团订单详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"ordernum\":'2006190016893457221111'\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "dead_time",
            "description": "<p>未支付的失效时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>订单状态(同列表)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "messages",
            "description": "<p>留言</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "address_history",
            "description": "<p>收货地址</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.name",
            "description": "<p>收货人</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.phone",
            "description": "<p>电话</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.details",
            "description": "<p>详情</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.province_name",
            "description": "<p>省</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.city_name",
            "description": "<p>市</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.area_name",
            "description": "<p>区</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_child",
            "description": "<p>商品列表(按物流分组)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.status",
            "description": "<p>1:已发货 2:已签收</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.express_id",
            "description": "<p>物流公司id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.express_num",
            "description": "<p>物流单号</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_child.order_details",
            "description": "<p>商品详情</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.num",
            "description": "<p>购买数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.sku_value",
            "description": "<p>规格信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.price",
            "description": "<p>购买单价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.original_price",
            "description": "<p>购买原价</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "price_info",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.cost_price",
            "description": "<p>总价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.freight",
            "description": "<p>运费</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.vip_cut",
            "description": "<p>权益立减</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.coupon_money",
            "description": "<p>优惠券金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.special_price_cut",
            "description": "<p>活动立减</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.pay_time",
            "description": "<p>支付时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.pay_type",
            "description": "<p>支付渠道(1微信端 2app微信 3app支付宝 4ios)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.price",
            "description": "<p>订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "bill",
            "description": "<p>发票</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bill.bill_type",
            "description": "<p>0为不开发票 1个人 2公司</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bill.bill_title",
            "description": "<p>发票抬头</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bill.bill_number",
            "description": "<p>纳税人识别号</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "team_user_list",
            "description": "<p>队友列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "team_user_list.user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "team_user_list.nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "team_user_list.headimg",
            "description": "<p>头像</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"id\": 9530,\n\"ordernum\": \"2006190016893457221111\",\n\"dead_time\": null,\n\"status\": 10,\n\"address_history\": {\n\"id\": 2814,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 0,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n},\n\"messages\": \"\",\n\"post_type\": 1,\n\"goods_count\": 7,\n\"order_child\": [\n{\n\"status\": 1,\n\"order_id\": 9530,\n\"express_id\": 1,\n\"express_num\": \"1111111\",\n\"order_detail_id\": [\n\"10335\",\n\"10336\",\n\"10337\"\n],\n\"order_details\": [\n{\n\"goods_id\": 91,\n\"num\": 2,\n\"sku_value\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n],\n\"price\": \"9.70\",\n\"original_price\": \"379.00\",\n\"name\": \"AR立体浮雕星座地球仪\",\n\"picture\": \"/nlsg/goods/20191026172620981048.jpg\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"details_id\": 10335\n},\n{\n\"goods_id\": 98,\n\"num\": 1,\n\"sku_value\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"王琨专栏学习套装\"\n}\n],\n\"price\": \"254.15\",\n\"original_price\": \"399.00\",\n\"name\": \"王琨专栏学习套装\",\n\"picture\": \"/wechat/mall/goods/8885_1545795771.png\",\n\"subtitle\": \"王琨老师专栏年卡1张+《琨说》珍藏版\",\n\"details_id\": 10336\n}\n]\n},\n{\n\"status\": 1,\n\"order_id\": 9530,\n\"express_id\": 1,\n\"express_num\": \"2222222\",\n\"order_detail_id\": [\n\"10338\"\n],\n\"order_details\": [\n{\n\"goods_id\": 209,\n\"num\": 2,\n\"sku_value\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"儿童财商绘本(全10册)\"\n}\n],\n\"price\": \"134.64\",\n\"original_price\": \"180.00\",\n\"name\": \"儿童财商绘本(全10册)\",\n\"picture\": \"/wechat/mall/goods/625_1544239955.png\",\n\"subtitle\": \"帮助孩子建立正确的金钱观念 从容面对金钱问题\",\n\"details_id\": 10338\n}\n]\n}\n],\n\"price_info\": {\n\"cost_price\": \"1789.00\",\n\"freight\": \"13.00\",\n\"vip_cut\": \"304.13\",\n\"coupon_money\": \"0.00\",\n\"special_price_cut\": \"738.60\",\n\"pay_time\": null,\n\"pay_type\": 0,\n\"price\": \"759.27\"\n},\n\"bill_info\": {\n\"bill_type\": 0,\n\"bill_title\": \"\",\n\"bill_number\": \"\",\n\"bill_format\": 0\n},\n\"team_user_list\": [\n{\n\"id\": 1,\n\"user_id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": null,\n\"is_captain\": 1\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/mall/group_buy_order_list",
    "title": "拼团订单的列表",
    "version": "1.0.0",
    "name": "_api_v4_mall_group_buy_order_list",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/group_buy_order_list"
      }
    ],
    "description": "<p>拼团订单的列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>订单状态(全部0,待付款1,待发货10,待签收20,已完成30,已取消99,拼团中95)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"status\":0\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods_count",
            "description": "<p>商品数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_details",
            "description": "<p>订单商品列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.num",
            "description": "<p>购买数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_details.goods_info",
            "description": "<p>商品信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.subtitle",
            "description": "<p>商品说明</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.picture",
            "description": "<p>商品图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.id",
            "description": "<p>商品id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 9526,\n\"ordernum\": \"2006180016893463957101\",\n\"status\": 1,\n\"price\": \"741.27\",\n\"goods_count\": 7,\n\"order_details\": [\n{\n\"status\": 0,\n\"goods_id\": 91,\n\"num\": 2,\n\"order_id\": 9526,\n\"goods_info\": {\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"picture\": \"/nlsg/goods/20191026172620981048.jpg\",\n\"id\": 91\n}\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/mall/order_info",
    "title": "普通和秒杀订单的详情",
    "version": "1.0.0",
    "name": "_api_v4_mall_order_info",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/order_info"
      }
    ],
    "description": "<p>普通和秒杀订单的详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"ordernum\":'2006190016893457221111'\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "dead_time",
            "description": "<p>未支付的失效时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>订单状态(同列表)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "messages",
            "description": "<p>留言</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "address_history",
            "description": "<p>收货地址</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.name",
            "description": "<p>收货人</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.phone",
            "description": "<p>电话</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.details",
            "description": "<p>详情</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.province_name",
            "description": "<p>省</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.city_name",
            "description": "<p>市</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "address_history.area_name",
            "description": "<p>区</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_child",
            "description": "<p>商品列表(按物流分组)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.status",
            "description": "<p>1:已发货 2:已签收</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.express_id",
            "description": "<p>物流公司id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.express_num",
            "description": "<p>物流单号</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_child.order_details",
            "description": "<p>商品详情</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.num",
            "description": "<p>购买数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.sku_value",
            "description": "<p>规格信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.price",
            "description": "<p>购买单价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_child.order_details.original_price",
            "description": "<p>购买原价</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "price_info",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.cost_price",
            "description": "<p>总价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.freight",
            "description": "<p>运费</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.vip_cut",
            "description": "<p>权益立减</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.coupon_money",
            "description": "<p>优惠券金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.special_price_cut",
            "description": "<p>活动立减</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.pay_time",
            "description": "<p>支付时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.pay_type",
            "description": "<p>支付渠道(1微信端 2app微信 3app支付宝 4ios)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price_info.price",
            "description": "<p>订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "bill",
            "description": "<p>发票</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bill.bill_type",
            "description": "<p>0为不开发票 1个人 2公司</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bill.bill_title",
            "description": "<p>发票抬头</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bill.bill_number",
            "description": "<p>纳税人识别号</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"id\": 9530,\n\"ordernum\": \"2006190016893457221111\",\n\"dead_time\": null,\n\"status\": 10,\n\"address_history\": {\n\"id\": 2814,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 0,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n},\n\"messages\": \"\",\n\"post_type\": 1,\n\"goods_count\": 7,\n\"order_child\": [\n{\n\"status\": 1,\n\"order_id\": 9530,\n\"express_id\": 1,\n\"express_num\": \"1111111\",\n\"order_detail_id\": [\n\"10335\",\n\"10336\",\n\"10337\"\n],\n\"order_details\": [\n{\n\"goods_id\": 91,\n\"num\": 2,\n\"sku_value\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n],\n\"price\": \"9.70\",\n\"original_price\": \"379.00\",\n\"name\": \"AR立体浮雕星座地球仪\",\n\"picture\": \"/nlsg/goods/20191026172620981048.jpg\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"details_id\": 10335\n},\n{\n\"goods_id\": 98,\n\"num\": 1,\n\"sku_value\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"王琨专栏学习套装\"\n}\n],\n\"price\": \"254.15\",\n\"original_price\": \"399.00\",\n\"name\": \"王琨专栏学习套装\",\n\"picture\": \"/wechat/mall/goods/8885_1545795771.png\",\n\"subtitle\": \"王琨老师专栏年卡1张+《琨说》珍藏版\",\n\"details_id\": 10336\n}\n]\n},\n{\n\"status\": 1,\n\"order_id\": 9530,\n\"express_id\": 1,\n\"express_num\": \"2222222\",\n\"order_detail_id\": [\n\"10338\"\n],\n\"order_details\": [\n{\n\"goods_id\": 209,\n\"num\": 2,\n\"sku_value\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"儿童财商绘本(全10册)\"\n}\n],\n\"price\": \"134.64\",\n\"original_price\": \"180.00\",\n\"name\": \"儿童财商绘本(全10册)\",\n\"picture\": \"/wechat/mall/goods/625_1544239955.png\",\n\"subtitle\": \"帮助孩子建立正确的金钱观念 从容面对金钱问题\",\n\"details_id\": 10338\n}\n]\n}\n],\n\"price_info\": {\n\"cost_price\": \"1789.00\",\n\"freight\": \"13.00\",\n\"vip_cut\": \"304.13\",\n\"coupon_money\": \"0.00\",\n\"special_price_cut\": \"738.60\",\n\"pay_time\": null,\n\"pay_type\": 0,\n\"price\": \"759.27\"\n},\n\"bill_info\": {\n\"bill_type\": 0,\n\"bill_title\": \"\",\n\"bill_number\": \"\",\n\"bill_format\": 0\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/mall/order_list",
    "title": "普通和秒杀订单的列表",
    "version": "1.0.0",
    "name": "_api_v4_mall_order_list",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/order_list"
      }
    ],
    "description": "<p>普通和秒杀订单的列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>订单状态(全部0,待付款1,待发货10,待签收20,已完成30,已取消99)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"status\":0\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods_count",
            "description": "<p>商品数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_details",
            "description": "<p>订单商品列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.num",
            "description": "<p>购买数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_details.goods_info",
            "description": "<p>商品信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.subtitle",
            "description": "<p>商品说明</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.picture",
            "description": "<p>商品图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_details.goods_info.id",
            "description": "<p>商品id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 9526,\n\"ordernum\": \"2006180016893463957101\",\n\"status\": 1,\n\"price\": \"741.27\",\n\"goods_count\": 7,\n\"order_details\": [\n{\n\"status\": 0,\n\"goods_id\": 91,\n\"num\": 2,\n\"order_id\": 9526,\n\"goods_info\": {\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"picture\": \"/nlsg/goods/20191026172620981048.jpg\",\n\"id\": 91\n}\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/prepare_create_flash_sale_order",
    "title": "秒杀订单预下单",
    "version": "1.0.0",
    "name": "_api_v4_mall_prepare_create_flash_sale_order",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/prepare_create_flash_sale_order"
      }
    ],
    "description": "<p>秒杀订单预下单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sku",
            "description": "<p>sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "buy_num",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "post_type",
            "description": "<p>物流方式(1邮寄2自提)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_freight_id",
            "description": "<p>免邮券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "address_id",
            "description": "<p>选择的地址id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "os_type",
            "description": "<p>1安卓2苹果3微信</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"sku\":\"1612728266\",\n\"goods_id\":209,\n\"buy_num\":1,\n\"inviter\":211172,\n\"post_type\":1,\n\"coupon_freight_id\":0,\n\"address_id\":2814,\n\"os_type\":1\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sku_list",
            "description": "<p>商品信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sku_list.sku_value_list",
            "description": "<p>规格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sku_list.num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "price_list",
            "description": "<p>订单价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.all_original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.all_price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.freight_money",
            "description": "<p>邮费</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.sp_cut_money",
            "description": "<p>活动立减</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.coupon_money",
            "description": "<p>优惠券立减</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.order_price",
            "description": "<p>订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "address_list",
            "description": "<p>用户地址列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "coupon_freight_list",
            "description": "<p>免邮券列表</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"user\": {\n\"id\": 168934,\n\"level\": 4,\n\"is_staff\": 1\n},\n\"sku_list\": {\n\"goods_id\": 91,\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"picture\": \"/wechat/mall/mall/goods/2224_1520841037.png\",\n\"sku_value_list\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n],\n\"num\": 2,\n\"original_price\": \"379.00\",\n\"price\": \"5.00\"\n},\n\"price_list\": {\n\"all_original_price\": \"758.00\",\n\"all_price\": \"10.00\",\n\"freight_money\": \"13.00\",\n\"sp_cut_money\": \"748.00\",\n\"freight_free_flag\": false,\n\"order_price\": \"23.00\"\n},\n\"address_list\": [\n{\n\"id\": 2815,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 1,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n},\n{\n\"id\": 2814,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 0,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n}\n],\n\"coupon_freight_list\": [\n{\n\"id\": 10,\n\"name\": \"测试免邮券\",\n\"type\": 4,\n\"price\": \"0.00\",\n\"full_cut\": \"0.00\",\n\"explain\": \"商品免邮券\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-28 23:59:59\",\n\"cr_id\": 35,\n\"sub_list\": []\n}\n],\n\"used_address\": {\n\"id\": 2814,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 0,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/prepare_create_group_buy_order",
    "title": "拼团订单预下单",
    "version": "1.0.0",
    "name": "_api_v4_mall_prepare_create_group_buy_order",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/prepare_create_group_buy_order"
      }
    ],
    "description": "<p>拼团订单预下单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sku",
            "description": "<p>sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "buy_num",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "post_type",
            "description": "<p>物流方式(1邮寄2自提)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_freight_id",
            "description": "<p>免邮券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "address_id",
            "description": "<p>选择的地址id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "os_type",
            "description": "<p>1安卓2苹果3微信</p>"
          },
          {
            "group": "Parameter",
            "type": "buy_type",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "buy_type",
            "description": "<p>1开团 2参团</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "group_key",
            "description": "<p>如果是参团,需要传</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"sku\":\"1612728266\",\n\"goods_id\":209,\n\"buy_num\":1,\n\"inviter\":211172,\n\"post_type\":1,\n\"coupon_goods_id\":0,\n\"coupon_freight_id\":0,\n\"address_id\":2814,\n\"os_type\":1,\n\"buy_type\":1,\n\"group_key\":1\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sku_list",
            "description": "<p>商品信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sku_list.sku_value_list",
            "description": "<p>规格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sku_list.num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "price_list",
            "description": "<p>订单价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.all_original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.all_price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.freight_money",
            "description": "<p>邮费</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.sp_cut_money",
            "description": "<p>活动立减</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.coupon_money",
            "description": "<p>优惠券立减</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.order_price",
            "description": "<p>订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "address_list",
            "description": "<p>用户地址列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "coupon_freight_list",
            "description": "<p>免邮券列表</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"user\": {\n\"id\": 168934,\n\"level\": 4,\n\"is_staff\": 1\n},\n\"sku_list\": {\n\"goods_id\": 91,\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"picture\": \"/wechat/mall/mall/goods/2224_1520841037.png\",\n\"sku_value_list\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n],\n\"num\": 2,\n\"original_price\": \"379.00\",\n\"price\": \"20.00\"\n},\n\"price_list\": {\n\"all_original_price\": \"758.00\",\n\"all_price\": \"40.00\",\n\"freight_money\": \"13.00\",\n\"vip_cut_money\": 0,\n\"sp_cut_money\": \"718.00\",\n\"coupon_money\": 0,\n\"freight_free_flag\": false,\n\"order_price\": \"53.00\"\n},\n\"address_list\": [\n{\n\"id\": 2815,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 1,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n}\n],\n\"coupon_list\": {\n\"coupon_goods\": [\n{\n\"id\": 7,\n\"name\": \"5元优惠券(六一专享)\",\n\"type\": 3,\n\"price\": \"5.00\",\n\"full_cut\": \"0.00\",\n\"explain\": \"六一活动期间\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-28 23:59:59\",\n\"cr_id\": 31,\n\"sub_list\": []\n}\n],\n\"coupon_freight\": [\n{\n\"id\": 10,\n\"name\": \"测试免邮券\",\n\"type\": 4,\n\"price\": \"0.00\",\n\"full_cut\": \"0.00\",\n\"explain\": \"商品免邮券\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-28 23:59:59\",\n\"cr_id\": 35,\n\"sub_list\": []\n}\n]\n},\n\"used_address\": {\n\"id\": 2814,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 0,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/prepare_create_order",
    "title": "普通订单预下单",
    "version": "1.0.0",
    "name": "_api_v4_mall_prepare_create_order",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/prepare_create_order"
      }
    ],
    "description": "<p>普通订单预下单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "from_cart",
            "description": "<p>下单方式(1:购物车下单  2:立即购买</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sku",
            "description": "<p>sku_number字符串或数组(如果是购物车,可以多条sku,直接购买只能一个sku)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "buy_num",
            "description": "<p>如果是购物车则不用传,直接购买必须传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "post_type",
            "description": "<p>物流方式(1邮寄2自提)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_goods_id",
            "description": "<p>优惠券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "coupon_freight_id",
            "description": "<p>免邮券id,没有0</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "address_id",
            "description": "<p>选择的地址id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "os_type",
            "description": "<p>1安卓2苹果3微信</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"from_cart\":1,\n\"sku\":\"1612728266,1835913656,1654630825,1626220663\",\n\"goods_id\":209,\n\"buy_num\":1,\n\"inviter\":211172,\n\"post_type\":1,\n\"coupon_goods_id\":0,\n\"coupon_freight_id\":0,\n\"address_id\":2814,\n\"os_type\":1\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sku_list",
            "description": "<p>商品信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sku_list.sku_value_list",
            "description": "<p>规格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sku_list.num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "price_list",
            "description": "<p>订单价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.all_original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.all_price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.freight_money",
            "description": "<p>邮费</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.vip_cut_money",
            "description": "<p>权益立减</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.sp_cut_money",
            "description": "<p>活动立减</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.coupon_money",
            "description": "<p>优惠券立减</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price_list.order_price",
            "description": "<p>订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "address_list",
            "description": "<p>用户地址列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "shop_address_list",
            "description": "<p>自提点列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "coupon_list",
            "description": "<p>可用优惠券列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "coupon_list.coupon_goods",
            "description": "<p>商品优惠券列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "coupon_list.coupon_freight",
            "description": "<p>免邮券列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"sku_list\": [\n{\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"picture\": \"/wechat/mall/mall/goods/2224_1520841037.png\",\n\"sku_value_list\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n],\n\"num\": 2,\n\"original_price\": \"379.00\",\n\"price\": \"9.70\"\n},\n{\n\"name\": \"王琨专栏学习套装\",\n\"subtitle\": \"王琨老师专栏年卡1张+《琨说》珍藏版\",\n\"picture\": \"/wechat/mall/goods/8873_1545796221.png\",\n\"sku_value_list\": [\n{\n\"key_name\": \"规格\",\n\"value_name\": \"王琨专栏学习套装\"\n}\n],\n\"num\": 1,\n\"original_price\": \"399.00\",\n\"price\": \"254.15\"\n}\n],\n\"price_list\": {\n\"all_original_price\": \"1789.00\",\n\"all_price\": \"746.27\",\n\"freight_money\": \"13.00\",\n\"vip_cut_money\": \"304.13\",\n\"sp_cut_money\": \"738.60\",\n\"coupon_money\": 0,\n\"freight_free_flag\": false,\n\"order_price\": \"759.27\"\n},\n\"address_list\": [\n{\n\"id\": 2815,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 1,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n}\n],\n\"coupon_list\": {\n\"coupon_goods\": [\n{\n\"id\": 7,\n\"name\": \"5元优惠券(六一专享)\",\n\"type\": 3,\n\"price\": \"5.00\",\n\"full_cut\": \"0.00\",\n\"explain\": \"六一活动期间\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-19 23:59:59\"\n}\n],\n\"coupon_freight\": [\n{\n\"id\": 10,\n\"name\": \"测试免邮券\",\n\"type\": 4,\n\"price\": \"0.00\",\n\"full_cut\": \"0.00\",\n\"explain\": \"商品免邮券\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-22 23:59:59\"\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "put",
    "url": "/api/v4/mall/status_change",
    "title": "修改订单状态(取消,删除,确认收货)",
    "version": "1.0.0",
    "name": "_api_v4_mall_status_change",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/status_change"
      }
    ],
    "description": "<p>修改订单状态</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "stop",
              "del",
              "receipt"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>标记</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "post",
    "url": "/api/v4/mall/sub_comment",
    "title": "评价",
    "version": "1.0.0",
    "name": "_api_v4_mall_sub_comment",
    "group": "MallOrder",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall/sub_comment"
      }
    ],
    "description": "<p>评价</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "order_detail_id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3",
              "4",
              "5"
            ],
            "optional": false,
            "field": "star",
            "description": "<p>星级</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "picture",
            "description": "<p>图片,多张用逗号隔开</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "issue_type",
            "description": "<p>原因,多个用逗号隔开(1,2,3)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>评价内容</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1597312942,\n\"data\": {\n\"code\": true,\n\"msg\": \"ok\",\n\"coupon\": {\n\"id\": 61,\n\"number\": \"202008131802211330000843024\",\n\"name\": \"20元优惠券(六一专享)\",\n\"type\": 3,\n\"price\": \"20.00\",\n\"full_cut\": \"199.00\",\n\"explain\": \"六一活动期间使用\",\n\"begin_time\": \"2020-06-18 00:00:21\",\n\"end_time\": \"2020-06-20 23:59:59\"\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallOrderController.php",
    "groupTitle": "MallOrder"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/banner_list",
    "title": "商城banner",
    "version": "4.0.0",
    "name": "_api_v4_goods_banner_list",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/banner_list"
      }
    ],
    "description": "<p>轮播,分类下方的banner,推荐的商品集</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "banner",
            "description": "<p>banner轮播的</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "banner.id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "banner.title",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "banner.pic",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "banner.url",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "banner.jump_type",
            "description": "<p>跳转类型(1:h5(走url),2商品,3优惠券领取页面)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "banner.obj_id",
            "description": "<p>跳转目标id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "recommend",
            "description": "<p>下方推荐位(字段同banner)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "hot_sale",
            "description": "<p>爆款推荐(字段同banner)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list",
            "description": "<p>推荐的商品专区</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.icon",
            "description": "<p>图标</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.ids_str",
            "description": "<p>专区商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "postage_line",
            "description": "<p>包邮线</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1598518779,\n\"data\": {\n\"banner\": [\n{\n\"id\": 407,\n\"title\": \"儿童情商社交游戏绘本\",\n\"pic\": \"wechat/mall/goods/20190110155407_333.png\",\n\"url\": \"/mall/shop-details?goods_id=333\",\n\"jump_type\": 0,\n\"obj_id\": 0\n},\n{\n\"id\": 406,\n\"title\": \"乌合之众\",\n\"pic\": \"wechat/mall/goods/20190110155401_327.png\",\n\"url\": \"/mall/shop-details?goods_id=327\",\n\"jump_type\": 2,\n\"obj_id\": 91\n}\n],\n\"recommend\": [\n{\n\"id\": 412,\n\"title\": \"活动测试\",\n\"pic\": \"nlsg/banner/20200521142524320648.png\",\n\"url\": \"/pages/activity/sixOne\",\n\"jump_type\": 0,\n\"obj_id\": 0\n},\n{\n\"id\": 408,\n\"title\": \"欢乐中国年\",\n\"pic\": \"wechat/mall/goods/20190110155411_338.png\",\n\"url\": \"/mall/shop-details?goods_id=338\",\n\"jump_type\": 0,\n\"obj_id\": 0\n}\n],\n\"goods_list\": [\n{\n\"id\": 2,\n\"name\": \"教学工具\",\n\"icon\": \"nlsg/goods/20200827113651486038.png\",\n\"ids_str\": \"156,159,160,161,163,164,165,166,168,184,188,189,191,194,196,197,202,205,209,218\"\n},\n{\n\"id\": 3,\n\"name\": \"家庭图书\",\n\"icon\": \"nlsg/goods/20200827114214174967.png\",\n\"ids_str\": \"230,231,255,261,262,263,265,324,325,327\"\n}\n],\n\"postage_line\": \"88\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/buyer_reading",
    "title": "商城购买须知",
    "version": "4.0.0",
    "name": "_api_v4_goods_buyer_reading",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/buyer_reading"
      }
    ],
    "description": "<p>商城服务说明</p>",
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"k\": \"关于发货\",\n\"v\": \"发货以订单拍下的商品及颜色为准，付款后2个工作日内发货。\"\n},\n{\n\"k\": \"售后服务电话：010-85164891\",\n\"v\": \"\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/buyer_reading_gb",
    "title": "拼团购买须知",
    "version": "4.0.0",
    "name": "_api_v4_goods_buyer_reading_gb",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/buyer_reading_gb"
      }
    ],
    "description": "<p>拼团购买须知</p>",
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/category_list",
    "title": "商品分类列表",
    "version": "4.0.0",
    "name": "_api_v4_goods_category_list",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/category_list"
      }
    ],
    "description": "<p>获取商品分类列表</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "name",
            "description": "<p>名称</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 40,\n\"name\": \"家庭育儿\"\n},\n{\n\"id\": 41,\n\"name\": \"夫妻关系\"\n},\n{\n\"id\": 42,\n\"name\": \"心理励志\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "post",
    "url": "/api/v4/goods/collect",
    "title": "收藏",
    "version": "4.0.0",
    "name": "_api_v4_goods_collect",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/collect"
      }
    ],
    "description": "<p>收藏,取消收藏</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/comment_list",
    "title": "商品评论列表",
    "version": "4.0.0",
    "name": "_api_v4_goods_comment_list",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/comment_list"
      }
    ],
    "description": "<p>获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>指定商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>页数,默认1</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>条数,默认10</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "count",
            "description": "<p>评论数量(只统计顶级评论数量)</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.id",
            "description": "<p>评论id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.nick_name",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.level",
            "description": "<p>用户等级</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.expire_time",
            "description": "<p>等级到期时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.content",
            "description": "<p>评论内容</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.ctime",
            "description": "<p>评论时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.star",
            "description": "<p>星级</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.reply_comment",
            "description": "<p>官方回复内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.reply_time",
            "description": "<p>回复时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.reply_nick_name",
            "description": "<p>官方回复人昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list.sku_value",
            "description": "<p>规格值</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"count\": 3,\n\"list\": [\n{\n\"id\": 951,\n\"user_id\": 168934,\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\",\n\"nick_name\": null,\n\"level\": 0,\n\"expire_time\": 0,\n\"content\": \"12345\",\n\"ctime\": 1578991712,\n\"pid\": 0,\n\"goods_id\": 91,\n\"sku_number\": \"1612728266\",\n\"star\": 5,\n\"reply_comment\": \"感谢您的认可与支持，我们会不断提升产品质量和服务，为您营造更好的用户体验，欢迎您下次光临~\",\n\"reply_time\": 1581652688,\n\"reply_user_id\": 2,\n\"sku_id\": 1884,\n\"sku_value\": [\n{\n\"id\": 364,\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n],\n\"list\": [\n{\n\"id\": 923,\n\"user_id\": 168934,\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\",\n\"nick_name\": null,\n\"level\": 0,\n\"expire_time\": 0,\n\"content\": \"测试测试\",\n\"ctime\": 0,\n\"pid\": 951,\n\"goods_id\": 91,\n\"sku_number\": \"7459726\",\n\"star\": 3,\n\"reply_comment\": \"\",\n\"reply_time\": 0,\n\"reply_user_id\": 0,\n\"sku_id\": null,\n\"sku_value\": [],\n\"list\": [\n{\n\"id\": 925,\n\"user_id\": 168934,\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\",\n\"nick_name\": null,\n\"level\": 0,\n\"expire_time\": 0,\n\"content\": \"质量好，实用，有趣，两个孩子非常喜欢！\",\n\"ctime\": 1535506746,\n\"pid\": 923,\n\"goods_id\": 91,\n\"sku_number\": \"1806683894\",\n\"star\": 5,\n\"reply_comment\": \"\",\n\"reply_time\": 0,\n\"reply_user_id\": 0,\n\"sku_id\": 184,\n\"sku_value\": [],\n\"list\": []\n}\n]\n}\n]\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/coupon_list",
    "title": "优惠券列表",
    "version": "4.0.0",
    "name": "_api_v4_goods_coupon_list",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/coupon_list"
      }
    ],
    "description": "<p>获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "goods_id",
            "description": "<p>指定商品id则返回无限制优惠券以及指定商品优惠券</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "goods_only",
            "description": "<p>1:如果指定goods_id,可通过该参数控制只返回指定商品优惠券</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "ob",
            "description": "<p>排序(id上架时间,price价格,以上后缀分为_asc正序,_desc逆序.不传为默认)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "show_zero_stock",
            "description": "<p>1:没有库存的也返回  默认不返回</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": true,
            "field": "get_all",
            "description": "<p>1:不设置分页,都传回</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "name",
            "description": "<p>优惠券名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "infinite",
            "description": "<p>库存无限  1无限  0有限</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "stock",
            "description": "<p>库存</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>面值</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "full_cut",
            "description": "<p>满减线,0表示无限制</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "get_begin_time",
            "description": "<p>开始领取时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "get_end_time",
            "description": "<p>领取结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "past",
            "description": "<p>领取后几天有效</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "remarks",
            "description": "<p>说明</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "use_time_begin",
            "description": "<p>有效期</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "use_time_end",
            "description": "<p>有效期</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "can_use",
            "description": "<p>是否能领取</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 34,\n\"name\": \"车速\",\n\"infinite\": 0,\n\"stock\": 10,\n\"price\": \"8.00\",\n\"restrict\": 1,\n\"full_cut\": \"0.00\",\n\"get_begin_time\": 0,\n\"get_end_time\": 0,\n\"past\": \"2\",\n\"use_type\": 3,\n\"remarks\": \"10\",\n\"use_time_begin\": 0,\n\"use_time_end\": 0,\n\"have_sub\": 2,\n\"can_use\": 1\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/flash_sale",
    "title": "秒杀首页",
    "version": "4.0.0",
    "name": "_api_v4_goods_flash_sale",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/flash_sale"
      }
    ],
    "description": "<p>秒杀首页</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "show_time",
            "description": "<p>时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "statis",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "data",
            "description": "<p>列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.group_num",
            "description": "<p>拼团需要人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.group_price",
            "description": "<p>拼团价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.stock",
            "description": "<p>库存</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "data.use_stock",
            "description": "<p>已用库存</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"time\": \"2020-07-30 18:12:00\",\n\"show_time\": \"18:12\",\n\"timestamp\": 1596103920,\n\"status\": \"即将开抢\",\n\"data\": [\n{\n\"goods_id\": 91,\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"goods_original_price\": \"0.00\",\n\"original_price\": \"379.00\",\n\"stock\": 0,\n\"use_stock\": 1,\n\"goods_price\": \"5.00\",\n\"begin_time\": \"2020-07-30 18:12:00\",\n\"end_time\": \"2020-08-28 18:26:59\",\n\"begin_timestamp\": 1596103920,\n\"end_timestamp\": 1598610419\n}\n]\n},\n{\n\"time\": \"2020-07-31 18:12:00\",\n\"show_time\": \"18:12\",\n\"timestamp\": 1596190320,\n\"status\": \"即将开抢\",\n\"data\": [\n{\n\"goods_id\": 98,\n\"name\": \"王琨专栏学习套装\",\n\"subtitle\": \"王琨老师专栏年卡1张+《琨说》珍藏版\",\n\"goods_original_price\": \"0.00\",\n\"original_price\": \"399.00\",\n\"stock\": 0,\n\"use_stock\": 0,\n\"goods_price\": \"9.90\",\n\"begin_time\": \"2020-07-31 18:12:00\",\n\"end_time\": \"2020-08-28 18:26:59\",\n\"begin_timestamp\": 1596190320,\n\"end_timestamp\": 1598610419\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/for_your_reference",
    "title": "猜你喜欢",
    "version": "4.0.0",
    "name": "_api_v4_goods_for_your_reference",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/for_your_reference"
      }
    ],
    "description": "<p>猜你喜欢(参数同商品列表接口)</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "num",
            "description": "<p>显示数量</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/group_buy",
    "title": "拼团首页",
    "version": "4.0.0",
    "name": "_api_v4_goods_group_buy",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy"
      }
    ],
    "description": "<p>拼团首页</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group_num",
            "description": "<p>拼团需要人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group_price",
            "description": "<p>拼团价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "user_count",
            "description": "<p>参加人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "is_begin",
            "description": "<p>是否开始(1开始 0未开始)</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "order_user",
            "description": "<p>用户头像列表</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"goods_id\": 91,\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"group_num\": 10,\n\"group_price\": \"20.00\",\n\"begin_time\": \"2020-06-05 09:40:00\",\n\"end_time\": \"2022-01-26 09:40:00\",\n\"user_count\": 4,\n\"order_user\": [\n\"1.jpg\",\n\"1.jpg\",\n\"1.jpg\",\n\"1.jpg\"\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/group_buy_info",
    "title": "拼团商品详情",
    "version": "4.0.0",
    "name": "_api_v4_goods_group_buy_info",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/group_buy_info"
      }
    ],
    "description": "<p>拼团商品详情(返回值参考商品详情. group_num拼团需要人数 order_numn已拼人数 normal_price单独购买价格,goods和sku都有这个字段)</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group_buy_id",
            "description": "<p>拼团列表id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/home_sp_list",
    "title": "秒杀和拼团预告",
    "version": "4.0.0",
    "name": "_api_v4_goods_home_sp_list",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/home_sp_list"
      }
    ],
    "description": "<p>秒杀和拼团预告</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec",
            "description": "<p>秒杀的</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list",
            "description": "<p>商品列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list.name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list.group_num",
            "description": "<p>拼团需要人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list.group_price",
            "description": "<p>拼团价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list.begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sec.list.end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group",
            "description": "<p>拼团的</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group.name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group.group_num",
            "description": "<p>拼团需要人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group.group_price",
            "description": "<p>拼团价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group.begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "group.end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "group.is_begin",
            "description": "<p>是否开始(1开始 0未开始)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"sec\": {\n\"time\": \"2020-06-11 17:34:00\",\n\"list\": [\n{\n\"goods_id\": 86,\n\"name\": \"AR智能学生专用北斗地球仪\",\n\"subtitle\": \"王树声地理教学研究室倾力打造地理教学地球仪\",\n\"goods_original_price\": \"0.00\",\n\"original_price\": \"379.00\",\n\"goods_price\": \"0.00\",\n\"begin_time\": \"2020-06-11 17:34:00\",\n\"end_time\": \"2020-06-11 17:52:59\"\n}\n]\n},\n\"group\": [\n{\n\"goods_id\": 91,\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"group_num\": 10,\n\"group_price\": \"20.00\",\n\"begin_time\": \"2020-06-05 09:40:00\",\n\"end_time\": \"2022-01-26 09:40:00\"\n},\n{\n\"goods_id\": 86,\n\"name\": \"AR智能学生专用北斗地球仪\",\n\"subtitle\": \"王树声地理教学研究室倾力打造地理教学地球仪\",\n\"group_num\": 5,\n\"group_price\": \"18.00\",\n\"begin_time\": \"2020-06-05 09:36:17\",\n\"end_time\": \"2022-01-26 09:40:00\"\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/info",
    "title": "获取商品信息(列表,详情)",
    "version": "4.0.0",
    "name": "_api_v4_goods_info",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/info"
      }
    ],
    "description": "<p>获取商品信息,如不指定id,get_sku=0 则返回商品列表.指定商品id,get_sku=1则返回商品详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ids_str",
            "description": "<p>商品id,如果需要指定商品,则传该值(例:91,98)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": true,
            "field": "get_sku",
            "description": "<p>1:获取商品sku_list规格信息</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": true,
            "field": "get_details",
            "description": "<p>1:获取商品详情,图片列表,服务说明</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "cid",
            "description": "<p>商品分类,如需指定分类搜索则传该值(1,2,3)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "zone_id",
            "description": "<p>商品专区id(banner接口返回的goods_list的id)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "ob",
            "description": "<p>排序(new上架时间,sales售出,price价格,以上后缀分为_asc正序,_desc逆序.如果有ids_str可指定排序为ids_str,不传为默认.chandler:热度:sales_desc;上新:new_asc)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>页数,默认1</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>条数,默认10</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "get_all",
            "description": "<p>1:不设置分页,都传回</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "stock",
            "description": "<p>库存</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "collect",
            "description": "<p>1:已收藏 0:未收藏</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>商品详情</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "cagetory_list",
            "description": "<p>分类</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "cagetory_list.name",
            "description": "<p>分类名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sku_list",
            "description": "<p>规格列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sku_list.id",
            "description": "<p>规格id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.sku_number",
            "description": "<p>sku码</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.original_price",
            "description": "<p>规格原价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.price",
            "description": "<p>规格售价</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.stock",
            "description": "<p>sku码</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.sku_value",
            "description": "<p>sku值列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sku_list.sku_value.sku_id",
            "description": "<p>skuid</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.sku_value.key_name",
            "description": "<p>规格名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list.sku_value.value_name",
            "description": "<p>规格值</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "picture_list",
            "description": "<p>商品轮播图片(排序规则:视频,主图,其他)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "picture_list.id",
            "description": "<p>图片id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "picture_list.url",
            "description": "<p>图片地址</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "picture_list.is_main",
            "description": "<p>1:主图</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "picture_list.is_video",
            "description": "<p>1:表示是视频</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "picture_list.duration",
            "description": "<p>视频时长(单位秒)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "picture_list.cover_img",
            "description": "<p>视频封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "tos_bind_list",
            "description": "<p>服务说明</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "tos_list.tos.title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "tos_list.tos.content",
            "description": "<p>内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "active_group_list",
            "description": "<p>促销活动(可能多条,以第一条为准)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "active_group_list.id",
            "description": "<p>活动id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.title",
            "description": "<p>活动标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.begin_time",
            "description": "<p>活动开始时间(2020-06-01 00:00:00)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.end_time",
            "description": "<p>活动结束时间(2020-07-01 23:59:59)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.ad_begin_time",
            "description": "<p>活动图标开始时间(2020-05-12 00:00:00)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.pre_begin_time",
            "description": "<p>活动预热开始时间(2020-05-12 00:00:00)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.lace_img",
            "description": "<p>活动图标</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.wx_share_title",
            "description": "<p>分享标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.wx_share_img",
            "description": "<p>分享图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "active_group_list.wx_share_desc",
            "description": "<p>分享内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sp_info",
            "description": "<p>商品特价详情</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sp_info.group_buy",
            "description": "<p>空表示没有拼团或多 不是空且price有值表有拼团和拼团的价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sp_info.sp_type",
            "description": "<p>当前商品特价表示(1:折扣  2:秒杀)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sp_info.begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sp_info.end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "sp_info.list",
            "description": "<p>所有活动类型列表([2,1])</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 91,\n\"name\": \"AR立体浮雕星座地球仪\",\n\"subtitle\": \"高清生动准确的星座秘密等你来发现\",\n\"picture\": \"/nlsg/goods/20191026172620981048.jpg\",\n\"original_price\": \"379.00\",\n\"price\": \"5.00\",\n\"category_id\": 56,\n\"content\": \"<p><img src=\\\"http://share.nlsgapp.com/wechat/mall/goods/15205072688377.jpg\\\"></p>\",\n\"active_group_list\": {\n\"1\": {\n\"id\": 1,\n\"title\": \"三八活动\",\n\"begin_time\": \"2020-06-01 00:00:00\",\n\"end_time\": \"2020-07-01 23:59:59\",\n\"ad_begin_time\": \"2020-05-12 00:00:00\",\n\"pre_begin_time\": \"2020-05-12 00:00:00\",\n\"lace_img\": \"\",\n\"wx_share_title\": \"微信三八标题\",\n\"wx_share_img\": \"wx38.jpg\",\n\"wx_share_desc\": \"微信三八简介\"\n}\n},\n\"twitter_money_list\": [\n{\n\"sku_number\": \"1612728266\",\n\"twitter_money\": {\n\"t_money_black\": \"2.00\",\n\"t_money_yellow\": \"3.00\",\n\"t_money_dealer\": \"4.00\",\n\"t_money\": \"1.00\",\n\"t_staff_money\": 0\n}\n}\n],\n\"sku_list\": [\n{\n\"id\": 1884,\n\"goods_id\": 91,\n\"sku_number\": \"1612728266\",\n\"picture\": \"/wechat/mall/mall/goods/2224_1520841037.png\",\n\"original_price\": \"379.00\",\n\"price\": \"9.70\",\n\"stock\": 294,\n\"sku_value_list\": [\n{\n\"id\": 364,\n\"sku_id\": 1884,\n\"key_name\": \"规格\",\n\"value_name\": \"AR立体浮雕星座地球仪\"\n}\n]\n}\n],\n\"sp_info\": {\n{\n\"group_buy\": {\n\"price\": \"0.00\",\n\"num\": 10,\n\"begin_time\": \"2020-06-05 09:40:00\",\n\"end_time\": \"2022-01-26 09:40:00\"\n},\n\"sp_type\": 1,\n\"begin_time\": \"2020-06-04 20:16:45\",\n\"end_time\": \"2020-07-11 00:00:00\",\n\"list\": [\n1,\n4\n]\n}\n},\n\"tos_bind_list\": [\n{\n\"goods_id\": 91,\n\"tos_id\": 1,\n\"tos\": [\n{\n\"title\": \"7天可退还\",\n\"content\": \"不影响销售的话\",\n\"icon\": \"1.jpg\",\n\"id\": 1\n}\n]\n},\n{\n\"goods_id\": 91,\n\"tos_id\": 2,\n\"tos\": [\n{\n\"title\": \"14天保修\",\n\"content\": \"不是人为损坏\",\n\"icon\": \"\",\n\"id\": 2\n}\n]\n}\n],\n\"picture_list\": [\n{\n\"url\": \"/wechat/mall/goods/vg_20181208142653.jpg\",\n\"is_main\": 0,\n\"is_video\": 0,\n\"duration\": \"\",\n\"goods_id\": 91,\n\"cover_img\":\"\"\n}\n],\n\"category_list\": {\n\"id\": 56,\n\"name\": \"益智玩具\"\n}\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "/api/v4/goods/service_description",
    "title": "商城服务说明",
    "version": "4.0.0",
    "name": "_api_v4_goods_service_description",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/service_description"
      }
    ],
    "description": "<p>商城服务说明</p>",
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"k\": \"七天无理由退换货\",\n\"v\": \"买家提出退款申请所指向的商品\"\n},\n{\n\"k\": \"正品保障\",\n\"v\": \"正品保障服务是指\"\n},\n{\n\"k\": \"会员85折\",\n\"v\": \"成为能量时光皇钻会员\"\n},\n{\n\"k\": \"满88包邮\",\n\"v\": \"能量时光自营商品\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "post",
    "url": "/api/v4/goods/sub",
    "title": "到货提醒",
    "version": "4.0.0",
    "name": "_api_v4_goods_sub",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/sub"
      }
    ],
    "description": "<p>到货提醒,假接口</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "sku_number",
            "description": "<p>sku编码</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "post",
    "url": "/api/v4/mall_coupon/rule",
    "title": "优惠券领取页面",
    "version": "4.0.0",
    "name": "_api_v4_mall_coupon_rule",
    "group": "Mall",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/mall_coupon/rule"
      }
    ],
    "description": "<p>优惠券领取页面</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MallController.php",
    "groupTitle": "Mall"
  },
  {
    "type": "get",
    "url": "api/v4/order/reward/user",
    "title": "鼓励列表",
    "version": "4.0.0",
    "name": "getRewardUser",
    "group": "Order",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/order/reward/user"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>相关id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型 3想法 4百科</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reward_num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>送花的用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.nickname",
            "description": "<p>送花的用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user.headimg",
            "description": "<p>送花的用户头像</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n\"data\": {\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "Order"
  },
  {
    "type": "get",
    "url": "api/v4/rank/wiki",
    "title": "百科排行榜",
    "version": "4.0.0",
    "name": "wiki",
    "group": "Rank",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/rank/wiki"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>主标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_wroks",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_wroks.wiki.name",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_wroks.wiki.content",
            "description": "<p>内容简介</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_wroks.wiki.view_num",
            "description": "<p>浏览数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_wroks.wiki.like_num",
            "description": "<p>收藏数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list_wroks.wiki.comment_num",
            "description": "<p>评论数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": [\n{\n\"id\": 9,\n\"title\": \"热门百科榜单\",\n\"content\": null,\n\"list_works\": [\n{\n\"id\": 16,\n\"lists_id\": 9,\n\"works_id\": 1,\n\"created_at\": \"2020-07-08T02:00:00.000000Z\",\n\"updated_at\": \"2020-07-08T02:00:00.000000Z\",\n\"wiki\": {\n\"id\": 1,\n\"name\": \"室内空气污染对孩子的危害\",\n\"content\": \"社会的进步，工业的发展，导致污染越来越严重，触目惊心\",\n\"view_num\": 10,\n\"like_num\": 2,\n\"comment_num\": 5\n}\n},\n{\n\"id\": 17,\n\"lists_id\": 9,\n\"works_id\": 2,\n\"created_at\": \"2020-07-08T02:00:00.000000Z\",\n\"updated_at\": \"2020-07-08T02:00:00.000000Z\",\n\"wiki\": {\n\"id\": 2,\n\"name\": \"世界名著必读岁月经典\",\n\"content\": \"每个时代都有极其红极广受好评\",\n\"view_num\": 5,\n\"like_num\": 6,\n\"comment_num\": 5\n}\n}\n]\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/RankController.php",
    "groupTitle": "Rank"
  },
  {
    "type": "get",
    "url": "api/v4/rank/works",
    "title": "排行榜-热门课程",
    "version": "4.0.0",
    "name": "works",
    "group": "Rank",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/rank/works"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>课程</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works.chapter_num",
            "description": "<p>章节数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.subscibe_num",
            "description": "<p>学习人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works.is_free",
            "description": "<p>是否免费</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "works.price",
            "description": "<p>课程价格</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n     \"data\": [\n{\n\"id\": 8,\n\"title\": \"热门课程榜单\",\n\"works\": [\n{\n\"works_id\": 30,\n\"user_id\": 168934,\n\"title\": \"不要羞辱你的孩子 他的心很脆弱\",\n\"subtitle\": \"家庭教育\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416324725316.jpg\",\n\"chapter_num\": 8,\n\"subscribe_num\": 0,\n\"is_free\": 1,\n\"price\": \"0.00\",\n\"pivot\": {\n\"lists_id\": 8,\n\"works_id\": 30\n}\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n},\n{\n\"works_id\": 31,\n\"user_id\": 168934,\n\"title\": \"小孩子做噩梦怎么办？九成父母都没当回事\",\n\"subtitle\": \"家庭教育\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416393315731.jpg\",\n\"chapter_num\": 5,\n\"subscribe_num\": 0,\n\"is_free\": 1,\n\"price\": \"0.00\",\n\"pivot\": {\n\"lists_id\": 8,\n\"works_id\": 31\n}\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n},\n{\n\"works_id\": 32,\n\"user_id\": 1,\n\"title\": \"时间就像你手中的冰淇淋\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416424169642.jpg\",\n\"chapter_num\": 0,\n\"subscribe_num\": 0,\n\"is_free\": 0,\n\"price\": \"0.00\",\n\"pivot\": {\n\"lists_id\": 8,\n\"works_id\": 32\n}\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n}\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/RankController.php",
    "groupTitle": "Rank"
  },
  {
    "type": "get",
    "url": "api/v4/reply/destroy",
    "title": "回复删除",
    "version": "4.0.0",
    "name": "destroy",
    "group": "Reply",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/reply/destroy"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>回复id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ReplyController.php",
    "groupTitle": "Reply"
  },
  {
    "type": "get",
    "url": "api/v4/reply/store",
    "title": "回复",
    "version": "4.0.0",
    "name": "store",
    "group": "Reply",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/reply/store"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "comment_id",
            "description": "<p>评论id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>回复内容</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ReplyController.php",
    "groupTitle": "Reply"
  },
  {
    "type": "get",
    "url": "api/v4/reply/update",
    "title": "回复更新内容",
    "version": "4.0.0",
    "name": "update",
    "group": "Reply",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/reply/update"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>回复id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>回复内容</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ReplyController.php",
    "groupTitle": "Reply"
  },
  {
    "type": "get",
    "url": "api/v4/user/account",
    "title": "账户与安全",
    "version": "4.0.0",
    "name": "account",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/account"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_wx",
            "description": "<p>是否绑定微信 0 否 1是</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n     \"is_wx\": 1,\n     \"phone\": \"186****5324\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "get",
    "url": "api/v4/user/base",
    "title": "基本资料",
    "version": "4.0.0",
    "name": "base",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/base"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "birthday",
            "description": "<p>生日</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "intro",
            "description": "<p>简介</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "like_nun",
            "description": "<p>喜欢精选</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "income_num",
            "description": "<p>收益动态</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "reply_num",
            "description": "<p>评论@</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "fans_num",
            "description": "<p>新增粉丝</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "systerm_num",
            "description": "<p>系统通知</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "update_num",
            "description": "<p>更新消息</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":{\n     'nickname': '张三',\n     'headimg' : 'test.png',\n     'sex': 1,\n      'birthday': '1990-1-1',\n      'intro': '简介'\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "post",
    "url": "api/v4/user/check_phone",
    "title": "验证手机号是否已经存在",
    "version": "4.0.0",
    "name": "check_phone",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/coupon"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>当前用户token</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "get",
    "url": "api/v4/user/coupon",
    "title": "邀请有礼",
    "version": "4.0.0",
    "name": "coupon",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/coupon"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>当前用户token</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "coupon",
            "description": "<p>优惠券</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "coupon.name",
            "description": "<p>优惠券名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "coupon.price",
            "description": "<p>优惠券价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "coupon.begin_time",
            "description": "<p>优惠券开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "coupon.end_time",
            "description": "<p>优惠券结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "invite_num",
            "description": "<p>邀请数量</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "get",
    "url": "api/v4/user/edit_user",
    "title": "用户信息采集",
    "version": "4.0.0",
    "name": "coupon",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/coupon"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sex",
            "description": "<p>性别  0 未知 1 男 2 女</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "children_age",
            "description": "<p>孩子年龄范围  0:无  1: 0~6岁  2:7~18岁   3: 18岁以上</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\" : '成功',\n   \"data\":[]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "get",
    "url": "api/v4/user/feed",
    "title": "用户动态",
    "version": "4.0.0",
    "name": "feed",
    "group": "User",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/feed"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments",
            "description": "<p>想法</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.content",
            "description": "<p>内容</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "comments.forward_num",
            "description": "<p>转发数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "comments.share_num",
            "description": "<p>分享数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "comments.like_num",
            "description": "<p>喜欢数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "comments.flower_num",
            "description": "<p>送花数量</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "comments.reply_num",
            "description": "<p>评论数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "comments.created_at",
            "description": "<p>发布时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.user",
            "description": "<p>评论的用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.user.nickname",
            "description": "<p>评论的用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.user.headimg",
            "description": "<p>评论的用户头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.attach",
            "description": "<p>评论的图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.attach.img",
            "description": "<p>评论的图片地址</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.column",
            "description": "<p>专栏 【讲座】</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.column.title",
            "description": "<p>专栏的标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.column.cover_pic",
            "description": "<p>专栏的封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.column.price",
            "description": "<p>专栏的价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.column.subscribe_num",
            "description": "<p>专栏的订阅数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.wiki",
            "description": "<p>百科</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.wiki.name",
            "description": "<p>百科标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.wiki.cover",
            "description": "<p>百科封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comments.wiki.view_num",
            "description": "<p>百科浏览数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": {\n\"id\": 1,\n\"comments\": [\n{\n\"id\": 14,\n\"pid\": 0,\n\"user_id\": 1,\n\"relation_id\": 1,\n\"content\": \"生命 \",\n\"forward_num\": 0,\n\"share_num\": 0,\n\"like_num\": 0,\n\"reply_num\": 0,\n\"created_at\": \"2020-07-14 17:05:45\",\n\"user\": {\n\"id\": 1,\n\"nickname\": \"刘先森\",\n\"headimg\": \"https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png\"\n},\n\"attach\": [\n{\n\"id\": 16,\n\"relation_id\": 14,\n\"img\": \"/wechat/mall/goods/3476_1533614056.png\"\n},\n{\n\"id\": 17,\n\"relation_id\": 14,\n\"img\": \"/wechat/mall/goods/3476_1533614056.png\"\n},\n{\n\"id\": 18,\n\"relation_id\": 14,\n\"img\": \"/wechat/mall/goods/3476_1533614056.png\"\n}\n]\n}\n]\n}\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "post",
    "url": "api/v4/user/followed",
    "title": "关注",
    "version": "4.0.0",
    "name": "followed",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/followed"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "to_uid",
            "description": "<p>被关注者的uid</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "get",
    "url": "api/v4/user/homepage",
    "title": "用户主页",
    "version": "4.0.0",
    "name": "homepage",
    "group": "User",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "string",
            "optional": false,
            "field": "Bearer",
            "description": "<p>eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC92NC5jb21cL2FwaVwvdjRcL2F1dGhcL2xvZ2luIiwiaWF0IjoxNTk0OTU0MDQxLCJleHAiOjE1OTYyNTAwNDEsIm5iZiI6MTU5NDk1NDA0MSwianRpIjoiMFVhdmsxT0piNXJSSHFENSIsInN1YiI6MSwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.9qShuy0F5zwn-USMqKeVrDUKUW3JYQYCn46Yy04wbg0</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          }
        ]
      }
    },
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/homepage"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sex",
            "description": "<p>性别   1 男 2 女</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>用户头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headcover",
            "description": "<p>背景图</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_author",
            "description": "<p>是否是作者 1是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "intro",
            "description": "<p>简介</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "follow_num",
            "description": "<p>关注数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "fan_num",
            "description": "<p>粉丝数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "is_teacher",
            "description": "<p>是否为老师</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "is_self",
            "description": "<p>是否为当前用户  1 是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "is_follow",
            "description": "<p>是否关注 1 是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>作品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>作品标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.subtitle",
            "description": "<p>作品副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>作品封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.subscribe_num",
            "description": "<p>作品订阅数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.original_price",
            "description": "<p>作品价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "history",
            "description": "<p>学习记录</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "history.relation_type",
            "description": "<p>学习记录类型 1专栏   2课程   3讲座</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column",
            "description": "<p>专栏</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column.name",
            "description": "<p>专栏名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column.title",
            "description": "<p>专栏标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column.subtitle",
            "description": "<p>专栏副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column.original_price",
            "description": "<p>专栏价格</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": {\n\"id\": 1,\n\"nickname\": \"刘先森\",\n\"headimg\": \"https://nlsg-saas.oss-cn-beijing.aliyuncs.com/static/class/157291903507887.png\",\n\"headcover\": null,\n\"intro\": \"需要思考下了\",\n\"follow_num\": 10,\n\"fan_num\": 0,\n\"is_teacher\": 1,\n\"works\": {\n\"id\": 1,\n\"nickname\": \"刘先森\",\n\"works\": [\n{\n\"user_id\": 1,\n\"title\": \"理解孩子行为背后的原因\",\n\"cover_img\": \"/wechat/works/video/161627/2017061411282192073.jpg\",\n\"subscribe_num\": 0,\n\"original_price\": \"0.00\"\n},\n{\n\"user_id\": 1,\n\"title\": \"帮助孩子树立健康自尊的六个方法\",\n\"cover_img\": \"/wechat/works/video/161627/2017061411462579459.jpg\",\n\"subscribe_num\": 0,\n\"original_price\": \"0.00\"\n},\n{\n\"user_id\": 1,\n\"title\": \"培养责任心是孩子成长的必修课\",\n\"cover_img\": \"/wechat/works/video/161627/2017061411572097640.jpg\",\n\"subscribe_num\": 0,\n\"original_price\": \"0.00\"\n}\n]\n},\n\"column\": {\n\"id\": 1,\n\"nickname\": \"刘先森\",\n\"columns\": [\n{\n\"user_id\": 1,\n\"name\": \"张宝萍专栏\",\n\"title\": \"国家十百千万工程心灵导师\",\n\"subtitle\": \"心灵导师 直击人心\",\n\"original_price\": \"0.00\"\n}\n]\n}\n}\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "post",
    "url": "api/v4/user/store",
    "title": "个人更新",
    "version": "4.0.0",
    "name": "store",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/store"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "sex",
            "description": "<p>性别</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "birthday",
            "description": "<p>生日</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "intro",
            "description": "<p>简介</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "post",
    "url": "api/v4/user/unfollow",
    "title": "取消关注",
    "version": "4.0.0",
    "name": "unfollow",
    "group": "User",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/unfollow"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "to_uid",
            "description": "<p>被关注者uid</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "User"
  },
  {
    "type": "get",
    "url": "api/v4/wiki/category",
    "title": "百科-分类",
    "version": "4.0.0",
    "name": "category",
    "group": "Wiki",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/wiki/category"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>分类名</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": [\n{\n\"id\": 1,\n\"name\": \"两性关系\"\n},\n{\n\"id\": 2,\n\"name\": \"婚姻哲学\"\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WikiController.php",
    "groupTitle": "Wiki"
  },
  {
    "type": "get",
    "url": "api/v4/wiki/index",
    "title": "百科-首页",
    "version": "4.0.0",
    "name": "index",
    "group": "Wiki",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/wiki/index"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>百科标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>百科描述</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>百科封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "view_num",
            "description": "<p>浏览数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "like_num",
            "description": "<p>收藏数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comment_num",
            "description": "<p>评论数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n\"data\": [\n{\n\"id\": 1,\n\"category_id\": 1,\n\"name\": \"室内空气污染对孩子的危害\",\n\"content\": \"社会的进步，工业的发展，导致污染越来越严重，触目惊心\",\n\"cover\": \"/wechat/mall/goods/3264_1512448129.jpg\",\n\"view_num\": 10,\n\"like_num\": 2,\n\"comment_num\": 5,\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WikiController.php",
    "groupTitle": "Wiki"
  },
  {
    "type": "get",
    "url": "api/v4/wiki/related",
    "title": "百科-相关推荐",
    "version": "4.0.0",
    "name": "related",
    "group": "Wiki",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/wiki/related"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>百科id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>百科名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>百科内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>百科封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "view_num",
            "description": "<p>浏览量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "like_num",
            "description": "<p>收藏数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "comment_num",
            "description": "<p>评论数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n    \"data\": [\n{\n\"name\": \"世界名著必读岁月经典\",\n\"content\": \"每个时代都有极其红极广受好评\",\n\"cover\": \"/wechat/mall/mall/goods/389_1519697199.png\",\n\"view_num\": 5,\n\"like_num\": 6,\n\"comment_num\": 5\n}\n]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WikiController.php",
    "groupTitle": "Wiki"
  },
  {
    "type": "post",
    "url": "api/v4/works/subscribe",
    "title": "订阅",
    "version": "4.0.0",
    "name": "____",
    "group": "Works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>作品id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "Works"
  },
  {
    "type": "post",
    "url": "/api/v4/address/create",
    "title": "添加,编辑",
    "version": "1.0.0",
    "name": "_api_v4_address_create",
    "group": "address",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/address/create"
      }
    ],
    "description": "<p>添加,编辑</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "province",
            "description": "<p>省</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "city",
            "description": "<p>市</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "area",
            "description": "<p>地区(如北京没有三级,可不传)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>收货人名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>收货人电话</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "details",
            "description": "<p>详细地址</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": false,
            "field": "is_default",
            "description": "<p>1:默认地址 0:普通</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"province\":210000,\n\"city\":210100,\n\"area\":210102,\n\"name\":\"张三\",\n\"phone\":\"1111111111\",\n\"details\":\"数量的发掘Sofia1号\",\n\"is_default\":1\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n      \"code\": 200,\n      \"msg\": \"成功\",\n      \"data\": {\n      \"code\": true,\n      \"msg\": \"成功\"\n      }\n      }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AddressController.php",
    "groupTitle": "address"
  },
  {
    "type": "get",
    "url": "/api/v4/address/get_data",
    "title": "行政区划表数据",
    "version": "4.0.0",
    "name": "_api_v4_address_get_data",
    "group": "address",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/address/get_data"
      }
    ],
    "description": "<p>行政区划表数据</p>",
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n  \"code\": 200,\n  \"msg\": \"成功\",\n  \"data\": {\n      {\n          \"id\": 110000,\n          \"name\": \"北京\",\n          \"pid\": 0,\n          \"area_list\": [\n              {\n                  \"id\": 110101,\n                  \"name\": \"东城\",\n                  \"pid\": 110000,\n                  \"area_list\": []\n              },\n              {\n                  \"id\": 110102,\n                  \"name\": \"西城\",\n                  \"pid\": 110000,\n                  \"area_list\": []\n              }\n          ]\n      }\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AddressController.php",
    "groupTitle": "address"
  },
  {
    "type": "get",
    "url": "/api/v4/address/get_list",
    "title": "收货地址列表",
    "version": "4.0.0",
    "name": "_api_v4_address_get_list",
    "group": "address",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/address/get_list"
      }
    ],
    "description": "<p>收货地址列表,字段说明见创建接口</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "id",
            "description": "<p>如果传id,就是单条</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 2812,\n\"name\": \"李四\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 1,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n},\n{\n\"id\": 2816,\n\"name\": \"sfas\",\n\"phone\": \"18624078563\",\n\"details\": \"sdkfjsljfl1ao\",\n\"is_default\": 0,\n\"province\": 210000,\n\"city\": 210100,\n\"area\": 210102,\n\"province_name\": \"辽宁\",\n\"city_name\": \"沈阳\",\n\"area_name\": \"和平区\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AddressController.php",
    "groupTitle": "address"
  },
  {
    "type": "get",
    "url": "/api/v4/address/list_of_shop",
    "title": "自提点和退货点列表",
    "version": "4.0.0",
    "name": "_api_v4_address_list_of_shop",
    "group": "address",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/address/list_of_shop"
      }
    ],
    "description": "<p>自提点和退货点列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "2",
              "3"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>2自提 3退货</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 10,\n\"name\": \"退货点1\",\n\"admin_name\": \"啊哈哈\",\n\"admin_phone\": \"20349024\",\n\"province\": 110000,\n\"city\": 110105,\n\"area\": 0,\n\"details\": \"朝阳路85号\",\n\"province_name\": \"北京\",\n\"city_name\": \"朝阳\",\n\"area_name\": \"\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AddressController.php",
    "groupTitle": "address"
  },
  {
    "type": "put",
    "url": "/api/v4/address/status_change",
    "title": "修改状态",
    "version": "1.0.0",
    "name": "_api_v4_address_status_change",
    "group": "address",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/address/status_change"
      }
    ],
    "description": "<p>修改状态</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "default",
              "nomal",
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>状态(默认,普通,删除)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"flag\":\"default\",\n\"id\":2815\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n      \"code\": 200,\n      \"msg\": \"成功\",\n      \"data\": {\n      \"code\": true,\n      \"msg\": \"成功\"\n      }\n      }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AddressController.php",
    "groupTitle": "address"
  },
  {
    "type": "post",
    "url": "/api/v4/after_sales/create_order",
    "title": "申请售后",
    "version": "4.0.0",
    "name": "_api_v4_after_sales_create_order",
    "group": "afterSales",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/after_sales/create_order"
      }
    ],
    "description": "<p>申请售后</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>1退款2退货</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "order_id",
            "description": "<p>order_id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "order_detail_id",
            "description": "<p>订单详情id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "num",
            "description": "<p>退货的申请数量</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "reason_id",
            "description": "<p>理由id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "picture",
            "description": "<p>图片(字符串,数组)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "description",
            "description": "<p>用户退货描述</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/AfterSalesController.php",
    "groupTitle": "afterSales"
  },
  {
    "type": "get",
    "url": "/api/v4/after_sales/goods_list",
    "title": "可申请售后订单和商品列表",
    "version": "4.0.0",
    "name": "_api_v4_after_sales_goods_list",
    "group": "afterSales",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/after_sales/goods_list"
      }
    ],
    "description": "<p>可申请售后订单和商品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>页数,默认1</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>条数,默认10</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "order_detail_id",
            "description": "<p>订单详情id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "order_id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "order_detail_id",
            "description": "<p>订单详情id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sku_number",
            "description": "<p>sku</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_name",
            "description": "<p>品名</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "num",
            "description": "<p>可申请数量</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "sku_value",
            "description": "<p>规格信息</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": false,
            "field": "is_pass",
            "description": "<p>1:失效</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"ordernum\": \"2006280016893465633601\",\n\"order_id\": 9560,\n\"order_detail_id\": 10367,\n\"goods_id\": 160,\n\"sku_number\": \"1904221194\",\n\"goods_name\": \"少有人走的路\",\n\"subtitle\": \"武志红 张德芬 胡茵梦等名人大咖推荐\",\n\"receipt_at\": \"2020-07-02 14:47:22\",\n\"num\": 2,\n\"sku_value\": [\n{\n\"key_name\": \"少有人走的路\",\n\"value_name\": \"勇敢地面对谎言\"\n}\n],\n\"is_pass\": 0\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AfterSalesController.php",
    "groupTitle": "afterSales"
  },
  {
    "type": "get",
    "url": "/api/v4/after_sales/list",
    "title": "售后列表",
    "version": "4.0.0",
    "name": "_api_v4_after_sales_list",
    "group": "afterSales",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/after_sales/list"
      }
    ],
    "description": "<p>售后列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>页数,默认1</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>条数,默认10</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "status",
            "description": "<p>状态(全部0,待审核10,待寄回20,待鉴定30,待退款40,已完成:60,已驳回:70,已取消99)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "service_num",
            "description": "<p>服务单号</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "order_id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型(1退款,2退货)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "refe_price",
            "description": "<p>预计退款金额</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>实际退款金额</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "goods_list",
            "description": "<p>商品列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.name",
            "description": "<p>品名</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_list.price",
            "description": "<p>单价</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 2,\n\"service_num\": \"2007030016893462686832\",\n\"order_id\": 9560,\n\"order_detail_id\": 10367,\n\"type\": 2,\n\"num\": 1,\n\"cost_price\": \"10.00\",\n\"refe_price\": \"0.00\",\n\"price\": \"0.00\",\n\"status\": 99,\n\"user_cancel\": 0,\n\"user_cancel_time\": null,\n\"goods_list\": [\n{\n\"goods_id\": 160,\n\"name\": \"少有人走的路\",\n\"subtitle\": \"武志红 张德芬 胡茵梦等名人大咖推荐\",\n\"picture\": \"/wechat/mall/goods/7700_1532401324.png\",\n\"num\": 1,\n\"price\": \"10.00\"\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AfterSalesController.php",
    "groupTitle": "afterSales"
  },
  {
    "type": "get",
    "url": "/api/v4/after_sales/order_info",
    "title": "售后详情",
    "version": "4.0.0",
    "name": "_api_v4_after_sales_order_info",
    "group": "afterSales",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/after_sales/order_info"
      }
    ],
    "description": "<p>售后详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": false,
            "field": "only_bar",
            "description": "<p>是否只返回进度条(1是0否)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "service_num",
            "description": "<p>售后单号</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "order_id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "order_detail_id",
            "description": "<p>订单详情id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型(1退款2退货)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "user_cancel",
            "description": "<p>1用户取消</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "user_cancel_time",
            "description": "<p>用户取消时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>提交时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "picture",
            "description": "<p>图片</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "pass_at",
            "description": "<p>审核通过时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "check_at",
            "description": "<p>验货时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "receive_at",
            "description": "<p>收货时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "succeed_at",
            "description": "<p>succeed_at</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "reason_id",
            "description": "<p>售后原因id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "description",
            "description": "<p>买家退货描述</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_check_reject",
            "description": "<p>1:审核拒绝 2:审核通过</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "check_reject_at",
            "description": "<p>审核拒绝时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "check_remark",
            "description": "<p>审核备注</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_authenticate_reject",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "authenticate_reject_at",
            "description": "<p>鉴定时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "authenticate_remark",
            "description": "<p>鉴定备注</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "express_id",
            "description": "<p>寄回快递公司id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "express_num",
            "description": "<p>寄回快递单号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "express_name",
            "description": "<p>寄回快递公司名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "goods_list",
            "description": "<p>商品列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "refund_address",
            "description": "<p>售后点信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "express_info",
            "description": "<p>寄回物流信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "progress_bar",
            "description": "<p>进度条</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"id\": 2,\n\"service_num\": \"2007030016893462686832\",\n\"order_id\": 9560,\n\"order_detail_id\": 10367,\n\"type\": 2,\n\"num\": 1,\n\"cost_price\": \"10.00\",\n\"refe_price\": \"0.00\",\n\"price\": \"0.00\",\n\"status\": 99,\n\"user_cancel\": 1,\n\"user_cancel_time\": \"2020-07-08 13:51:57\",\n\"created_at\": \"2020-07-03 17:24:46\",\n\"return_address_id\": 10,\n\"picture\": \"\",\n\"pass_at\": \"2020-07-24 15:46:52\",\n\"check_at\": \"2020-07-23 15:47:01\",\n\"receive_at\": \"2020-07-12 15:47:06\",\n\"succeed_at\": \"2020-07-25 15:47:10\",\n\"reason_id\": 0,\n\"description\": null,\n\"is_check_reject\": 0,\n\"check_reject_at\": \"2020-07-23 15:47:21\",\n\"check_remark\": \"\",\n\"is_authenticate_reject\": 0,\n\"authenticate_reject_at\": null,\n\"authenticate_remark\": \"\",\n\"express_info_id\": 1,\n\"goods_list\": [\n{\n\"goods_id\": 160,\n\"name\": \"少有人走的路\",\n\"subtitle\": \"武志红 张德芬 胡茵梦等名人大咖推荐\",\n\"picture\": \"/wechat/mall/goods/7700_1532401324.png\",\n\"num\": 1,\n\"price\": \"10.00\"\n}\n],\n\"express_name\": \"\",\n\"refund_address\": {\n\"id\": 10,\n\"name\": \"退货点1\",\n\"admin_name\": \"啊哈哈\",\n\"admin_phone\": \"20349024\",\n\"province\": 110000,\n\"city\": 110105,\n\"area\": 0,\n\"details\": \"朝阳路85号\",\n\"province_name\": \"北京\",\n\"city_name\": \"朝阳\",\n\"area_name\": \"\"\n},\n\"progress_bar\": [\n{\n\"time\": \"2020-07-25 15:47\",\n\"status\": \"退款完毕文本\"\n},\n{\n\"time\": \"1970-01-01 08:00\",\n\"status\": \"鉴定待退款文本\"\n},\n{\n\"time\": \"2020-07-24 15:46\",\n\"status\": \"通过,寄回文本\"\n},\n{\n\"time\": \"2020-07-03 17:24\",\n\"status\": \"提交申请\"\n}\n],\n\"express_info\": {\n\"id\": 1,\n\"history\": {\n\"number\": \"YT4538526006366\",\n\"type\": \"yto\",\n\"typename\": \"圆通速递\",\n\"logo\": \"https://api.jisuapi.com/express/static/images/logo/80/yto.png\",\n\"list\": [\n{\n\"time\": \"2020-05-24 13:23:02\",\n\"status\": \"客户签收人: 周一派送急件电联18513793888 已签收  感谢使用圆通速递，期待再次为您服务 如有疑问请联系：18513793888，投诉电话：010-53579888\"\n},\n{\n\"time\": \"2020-05-22 15:38:58\",\n\"status\": \"【浙江省金华市永康市公司】 已收件 取件人: 00773969 (15268689991)\"\n}\n],\n\"deliverystatus\": 3,\n\"issign\": 1\n}\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AfterSalesController.php",
    "groupTitle": "afterSales"
  },
  {
    "type": "get",
    "url": "/api/v4/after_sales/reason_list",
    "title": "售后原因列表",
    "version": "1.0.0",
    "name": "_api_v4_after_sales_reason_list",
    "group": "afterSales",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/after_sales/reason_list"
      }
    ],
    "description": "<p>售后原因列表</p>",
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 1,\n\"value\": \"不喜欢/不想要\",\n\"status\": 1\n},\n{\n\"id\": 2,\n\"value\": \"颜色/图案/款式等不符\",\n\"status\": 1\n},\n{\n\"id\": 3,\n\"value\": \"包装/商品破损/污渍\",\n\"status\": 1\n},\n{\n\"id\": 4,\n\"value\": \"少件/漏发\",\n\"status\": 1\n},\n{\n\"id\": 5,\n\"value\": \"发票问题\",\n\"status\": 1\n},\n{\n\"id\": 6,\n\"value\": \"卖家发错货\",\n\"status\": 1\n},\n{\n\"id\": 7,\n\"value\": \"退运费\",\n\"status\": 1\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AfterSalesController.php",
    "groupTitle": "afterSales"
  },
  {
    "type": "put",
    "url": "/api/v4/after_sales/refund_post",
    "title": "寄回商品",
    "version": "1.0.0",
    "name": "_api_v4_after_sales_refund_post",
    "group": "afterSales",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/after_sales/refund_post"
      }
    ],
    "description": "<p>寄回商品</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "express_id",
            "description": "<p>快递公司id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "express_num",
            "description": "<p>快递单号</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"express_id\":1,\n\"express_num\":\"42134234242\",\n\"id\":2\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"code\": true,\n\"msg\": \"成功\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AfterSalesController.php",
    "groupTitle": "afterSales"
  },
  {
    "type": "put",
    "url": "/api/v4/after_sales/status_change",
    "title": "修改状态",
    "version": "1.0.0",
    "name": "_api_v4_after_sales_status_change",
    "group": "afterSales",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/after_sales/status_change"
      }
    ],
    "description": "<p>修改状态</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "stop",
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>状态(取消,删除)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"flag\":\"stop\",\n\"id\":2\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"code\": true,\n\"msg\": \"成功\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AfterSalesController.php",
    "groupTitle": "afterSales"
  },
  {
    "type": "get",
    "url": "/api/v4/book/get_book_index",
    "title": "听书-听书首页",
    "name": "get_book_index",
    "version": "1.0.0",
    "group": "book",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ListenBookController.php",
    "groupTitle": "book"
  },
  {
    "type": "get",
    "url": "/api/v4/book/get_book_list",
    "title": "听书-精选书单",
    "name": "get_book_list",
    "version": "1.0.0",
    "group": "book",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "page",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ListenBookController.php",
    "groupTitle": "book"
  },
  {
    "type": "get",
    "url": "/api/v4/book/get_book_list_detail",
    "title": "听书-精选书单详情",
    "name": "get_book_list_detail",
    "version": "1.0.0",
    "group": "book",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "lists_id",
            "description": "<p>书单id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "page",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"lists_info\": {\n\"id\": 1,\n\"title\": \"世界名著必读，历经岁月经典依旧陪伴成长\",\n\"subtitle\": \"强烈推荐\",\n\"cover\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"details_pic\": \"\",\n\"num\": 5,\n\"type\": 3,\n\"created_at\": \"2020-06-08T02:00:00.000000Z\",\n\"updated_at\": \"2020-06-08T02:00:00.000000Z\",\n\"status\": 1\n},\n\"works\": [\n{\n\"id\": 30,\n\"user_id\": 211172,\n\"type\": 3,\n\"title\": \"不要羞辱你的孩子 他的心很脆弱\",\n\"subtitle\": \"家庭教育\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416324725316.jpg\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"message\": \"\",\n\"is_free\": 1,\n\"user\": {\n\"id\": 211172,\n\"nickname\": \"能量时光\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"is_sub\": 0\n},\n{\n\"id\": 31,\n\"user_id\": 168934,\n\"type\": 3,\n\"title\": \"小孩子做噩梦怎么办？九成父母都没当回事\",\n\"subtitle\": \"家庭教育\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416393315731.jpg\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"message\": \"\",\n\"is_free\": 1,\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\",\n\"headimg\": \"/wechat/works/headimg/3833/2017110823004219451.png\"\n},\n\"is_sub\": 0\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ListenBookController.php",
    "groupTitle": "book"
  },
  {
    "type": "get",
    "url": "/api/v4/book/get_listen_detail",
    "title": "听书-听书详情",
    "name": "get_listen_detail",
    "version": "1.0.0",
    "group": "book",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order",
            "description": "<p>asc | desc  默认desc</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ListenBookController.php",
    "groupTitle": "book"
  },
  {
    "type": "get",
    "url": "/api/v4/book/get_new_book_list",
    "title": "听书-新书速递",
    "name": "get_new_book_list",
    "version": "1.0.0",
    "group": "book",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "page",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"current_page\": 1,\n\"data\": [\n{\n\"id\": 30,\n\"type\": 3,\n\"title\": \"不要羞辱你的孩子 他的心很脆弱\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416324725316.jpg\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"message\": \"\",\n\"is_free\": 0,\n\"is_sub\": 0\n},\n{\n\"id\": 31,\n\"type\": 3,\n\"title\": \"小孩子做噩梦怎么办？九成父母都没当回事\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416393315731.jpg\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"message\": \"\",\n\"is_free\": 0,\n\"is_sub\": 0\n},\n{\n\"id\": 32,\n\"type\": 3,\n\"title\": \"时间就像你手中的冰淇淋\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416424169642.jpg\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"message\": null,\n\"is_free\": 0,\n\"is_sub\": 0\n},\n{\n\"id\": 33,\n\"type\": 3,\n\"title\": \"在垃圾桶的手表也是手表\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161627/2017061416503678286.jpg\",\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"message\": \"\",\n\"is_free\": 0,\n\"is_sub\": 0\n}\n],\n\"first_page_url\": \"http://nlsgv4.com/api/v4/book/get_new_book_list?page=1\",\n\"from\": 1,\n\"last_page\": 1,\n\"last_page_url\": \"http://nlsgv4.com/api/v4/book/get_new_book_list?page=1\",\n\"next_page_url\": null,\n\"path\": \"http://nlsgv4.com/api/v4/book/get_new_book_list\",\n\"per_page\": 50,\n\"prev_page_url\": null,\n\"to\": 4,\n\"total\": 4\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ListenBookController.php",
    "groupTitle": "book"
  },
  {
    "type": "get",
    "url": "/api/v4/coupon/list",
    "title": "我的优惠券列表",
    "version": "4.0.0",
    "name": "_api_v4_coupon_list",
    "group": "coupon",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/coupon/list"
      }
    ],
    "description": "<p>我的优惠券列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>标识(1未用,2已用,3过期)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>page</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>size</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "count",
            "description": "<p>数量统计</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "count.status_1",
            "description": "<p>未用数量</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "count.status_2",
            "description": "<p>已用数量</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "count.status_3",
            "description": "<p>过期数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.number",
            "description": "<p>编号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.type",
            "description": "<p>类型(1专栏 3商品 4免邮券 5课程)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.price",
            "description": "<p>面值</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.full_cut",
            "description": "<p>满减线</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.explain",
            "description": "<p>说明</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.begin_time",
            "description": "<p>生效时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.end_time",
            "description": "<p>失效时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"count\": {\n\"status_1\": 0,\n\"status_2\": 0,\n\"status_3\": 4\n},\n\"list\": [\n{\n\"id\": 7,\n\"number\": \"202006121535411310000787769\",\n\"name\": \"5元优惠券(六一专享)\",\n\"type\": 3,\n\"price\": \"5.00\",\n\"full_cut\": \"0.00\",\n\"explain\": \"六一活动期间\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-28 23:59:59\"\n},\n{\n\"id\": 8,\n\"number\": \"202006121535411320000596680\",\n\"name\": \"10元优惠券(六一专享)\",\n\"type\": 3,\n\"price\": \"10.00\",\n\"full_cut\": \"99.00\",\n\"explain\": \"六一活动期间使用\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-28 23:59:59\"\n},\n{\n\"id\": 9,\n\"number\": \"202006121535411330000634480\",\n\"name\": \"20元优惠券(六一专享)\",\n\"type\": 3,\n\"price\": \"20.00\",\n\"full_cut\": \"199.00\",\n\"explain\": \"六一活动期间使用\",\n\"begin_time\": \"2020-06-18 00:00:21\",\n\"end_time\": \"2020-06-28 23:59:59\"\n},\n{\n\"id\": 10,\n\"number\": \"202006121544321350000639494\",\n\"name\": \"测试免邮券\",\n\"type\": 4,\n\"price\": \"0.00\",\n\"full_cut\": \"0.00\",\n\"explain\": \"商品免邮券\",\n\"begin_time\": \"2020-06-12 00:00:00\",\n\"end_time\": \"2020-06-28 23:59:59\"\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CouponController.php",
    "groupTitle": "coupon"
  },
  {
    "type": "post",
    "url": "/api/v4/goods/get_coupon",
    "title": "领取优惠券",
    "version": "4.0.0",
    "name": "_api_v4_goods_get_coupon",
    "group": "coupon",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/goods/get_coupon"
      }
    ],
    "description": "<p>领取优惠券</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "flag",
            "description": "<p>优惠券规则id(31,32,33)</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n      \"code\": 200,\n      \"msg\": \"成功\",\n      \"data\": {\n      \"msg\": \"领取成功\"\n      }\n      }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CouponController.php",
    "groupTitle": "coupon"
  },
  {
    "type": "get",
    "url": "/api/v4/create/create_poster",
    "title": "制作专属海报",
    "name": "create_poster",
    "version": "1.0.0",
    "group": "create",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "post_type",
            "description": "<p>类型 post_type   5精品课/听书     7优品海报   8 专栏/讲座/训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_id",
            "description": "<p>对应 课程或专栏id或商品</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "is_qrcode",
            "description": "<p>1 生成纯二维码</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "info_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_info_id",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CreatePosterController.php",
    "groupTitle": "create"
  },
  {
    "type": "post",
    "url": "/api/v4/create/upload_push",
    "title": "上传",
    "name": "create_poster",
    "version": "1.0.0",
    "group": "create",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type_flag",
            "description": "<p>类型 1 头像  2 作品  3 专栏  4 商品  5身份证审核  6 banner  7 书单 8 企业 9问题反馈  10晒单评价  100其他</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "file_base64",
            "description": "<p>图片base64</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/CreatePosterController.php",
    "groupTitle": "create"
  },
  {
    "type": "get",
    "url": "/api/v4/post/company_list",
    "title": "快递公司列表",
    "version": "4.0.0",
    "name": "_api_v4_post_company_list",
    "group": "express",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/post/company_list"
      }
    ],
    "description": "<p>快递公司列表</p>",
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 3,\n\"name\": \"圆通\"\n},\n{\n\"id\": 1,\n\"name\": \"顺丰\"\n},\n{\n\"id\": 2,\n\"name\": \"韵达\"\n},\n{\n\"id\": 4,\n\"name\": \"京东\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ExpressController.php",
    "groupTitle": "express"
  },
  {
    "type": "get",
    "url": "/api/v4/post/get_info",
    "title": "快递进度查询",
    "version": "4.0.0",
    "name": "_api_v4_post_get_info",
    "group": "express",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/post/get_info"
      }
    ],
    "description": "<p>快递进度查询</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "express_id",
            "description": "<p>快递公司id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "express_num",
            "description": "<p>快递单号</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>进度</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.time",
            "description": "<p>时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.status",
            "description": "<p>进展</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"number\": \"YT4538526006366\",\n\"type\": \"yto\",\n\"typename\": \"圆通速递\",\n\"logo\": \"https://api.jisuapi.com/express/static/images/logo/80/yto.png\",\n\"list\": [\n{\n\"time\": \"2020-05-24 13:23:02\",\n\"status\": \"客户签收人: 周一派送急件电联18513793888 已签收  感谢使用圆通速递，期待再次为您服务 如有疑问请联系：18513793888，投诉电话：010-53579888\"\n}\n],\n\"deliverystatus\": 3,\n\"issign\": 1\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ExpressController.php",
    "groupTitle": "express"
  },
  {
    "type": "get",
    "url": "/api/v4/im_friend/add_friend",
    "title": "Im添加好友",
    "name": "add_friend",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "From_Account",
            "description": "<p>为该 用户 添加好友</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "To_Account",
            "description": "<p>需要添加好友的id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "AddWording",
            "description": "<p>添加的备注</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_friend/add_friend",
    "title": "管理后台-Im添加好友",
    "name": "admin_add_friend",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "From_Account",
            "description": "<p>为该 用户 添加好友</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "To_Account",
            "description": "<p>需要添加好友的id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "AddWording",
            "description": "<p>添加的备注</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_friend/del_friend",
    "title": "管理后台-Im删除好友",
    "name": "admin_del_friend",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "From_Account",
            "description": "<p>需要删除该 用户 的好友</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "To_Account",
            "description": "<p>需要删除好友的id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_friend/friend_check",
    "title": "管理后台-校验用户关系",
    "name": "admin_friend_check",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "From_Account",
            "description": "<p>需要校验该 UserID 的好友</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "To_Account",
            "description": "<p>请求校验的好友的 UserID 列表</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "Relation",
            "description": "<p>CheckResult_Type_BothWay\tFrom_Account 的好友表中有 To_Account，To_Account 的好友表中也有 From_Account CheckResult_Type_AWithB\tFrom_Account 的好友表中有 To_Account，但 To_Account 的好友表中没有 From_Account CheckResult_Type_BWithA\tFrom_Account 的好友表中没有 To_Account，但 To_Account 的好友表中有 From_Account CheckResult_Type_NoRelation\tFrom_Account 的好友表中没有 To_Account，To_Account 的好友表中也没有 From_Account</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_friend/portrait_get",
    "title": "管理后台-拉取im 用户资料",
    "name": "admin_portrait_get",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "get",
    "url": "/api/v4/im_friend/get_im_user_id",
    "title": "Im根据手机号查好友",
    "name": "del_friend",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "str",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "get",
    "url": "/api/v4/im_friend/del_friend",
    "title": "Im删除好友",
    "name": "del_friend",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "From_Account",
            "description": "<p>需要删除该 用户 的好友</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "To_Account",
            "description": "<p>需要删除好友的id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "post",
    "url": "/api/v4/im_friend/friend_check",
    "title": "校验用户关系",
    "name": "friend_check",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "From_Account",
            "description": "<p>需要校验该 UserID 的好友</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "To_Account",
            "description": "<p>请求校验的好友的 UserID 列表</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "Relation",
            "description": "<p>CheckResult_Type_BothWay\tFrom_Account 的好友表中有 To_Account，To_Account 的好友表中也有 From_Account CheckResult_Type_AWithB\tFrom_Account 的好友表中有 To_Account，但 To_Account 的好友表中没有 From_Account CheckResult_Type_BWithA\tFrom_Account 的好友表中没有 To_Account，但 To_Account 的好友表中有 From_Account CheckResult_Type_NoRelation\tFrom_Account 的好友表中没有 To_Account，To_Account 的好友表中也没有 From_Account</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "get",
    "url": "/api/v4/im_friend/get_im_user",
    "title": "拉取im权限",
    "name": "get_im_user",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "get",
    "url": "/api/v4/im_friend/portrait_get",
    "title": "拉取im 用户资料",
    "name": "portrait_get",
    "version": "1.0.0",
    "group": "im_friend",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImFriendController.php",
    "groupTitle": "im_friend"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/change_group_owner",
    "title": "管理后台-转让群",
    "name": "admin_change_group_owner",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "GroupId",
            "description": "<p>GroupId</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "NewOwner_Account",
            "description": "<p>新群主id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/create_group",
    "title": "管理后台-创建群",
    "name": "admin_create_group",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id  数组类型 群初始人员</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "Name",
            "description": "<p>群名称</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/destroy_group",
    "title": "管理后台-解散群",
    "name": "admin_destroy_group",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "GroupId",
            "description": "<p>GroupId</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/edit_join_group",
    "title": "管理后台-添加/删除成员入群",
    "name": "admin_edit_join_group",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "group_id",
            "description": "<p>腾讯云的groupId</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id  数组类型</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "type",
            "description": "<p>type==del删除  add添加</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "silence",
            "description": "<p>type==del删除时Silence是否静默删人。0表示非静默删人，1表示静默删人</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "reason",
            "description": "<p>type==del删除时踢出用户原因</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/get_group_member_info",
    "title": "管理后台-获取群成员",
    "name": "admin_get_group_member_info",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "GroupId",
            "description": "<p>GroupId</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "Limit",
            "description": "<p>最多获取多少个成员的资料  //默认100</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "Offset",
            "description": "<p>从第多少个成员开始获取资料</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "/api/v4/im_group/edit_join_group",
    "title": "添加/删除成员入群",
    "name": "edit_join_group",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "group_id",
            "description": "<p>腾讯云的groupId</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id  数组类型</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "type",
            "description": "<p>type==del删除  add添加</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "silence",
            "description": "<p>type==del删除时Silence是否静默删人。0表示非静默删人，1表示静默删人</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "reason",
            "description": "<p>type==del删除时踢出用户原因</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "/api/v4/im_group/forbid_msg_list",
    "title": "群成员禁言list",
    "name": "forbid_msg_list",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "group_id",
            "description": "<p>腾讯云的groupId</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "/api/v4/im_group/forbid_send_msg",
    "title": "群成员禁言/解禁",
    "name": "forbid_send_msg",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "group_id",
            "description": "<p>腾讯云的groupId</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "shut_up_time",
            "description": "<p>禁言时长  0解禁 其他表示禁言</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "is_all",
            "description": "<p>是否全员 1是</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "/api/v4/im_group/set_group_user",
    "title": "设置群管理员",
    "name": "set_group_user",
    "version": "1.0.0",
    "group": "im_group",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "group_id",
            "description": "<p>腾讯云的groupId</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id 数组</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>2取消管理员 1设置管理员</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n   \"code\": 200,\n   \"msg\": \"成功\",\n   \"data\": [\n   ]\n   }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImGroupController.php",
    "groupTitle": "im_group"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im/msg_collection",
    "title": "管理后台-消息收藏操作",
    "name": "admin_msg_collection",
    "version": "1.0.0",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "os_msg_id",
            "description": "<p>消息序列号 array</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>收藏类型   1消息收藏</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "collection_id",
            "description": "<p>收藏列表id (取消收藏只传该字段)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImMsgController.php",
    "groupTitle": "im"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im/msg_collection_list",
    "title": "管理后台-消息收藏列表",
    "name": "admin_msg_collection_list",
    "version": "1.0.0",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "keywords",
            "description": "<p>收藏消息关键字</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "page",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImMsgController.php",
    "groupTitle": "im"
  },
  {
    "type": "post",
    "url": "/api/v4/im/del_send_all_list",
    "title": "清空群发列表",
    "name": "del_send_all_list",
    "version": "1.0.0",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "id",
            "description": "<p>需要清空的列表id  不传则全部清空</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImMsgController.php",
    "groupTitle": "im"
  },
  {
    "type": "get",
    "url": "api/v4/im/get_user_sig",
    "title": "用户签名",
    "version": "4.0.0",
    "name": "get_user_sig",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id  数组类型</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImController.php",
    "groupTitle": "im"
  },
  {
    "type": "post",
    "url": "/api/v4/im/msg_collection",
    "title": "消息收藏操作",
    "name": "msg_collection",
    "version": "1.0.0",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "os_msg_id",
            "description": "<p>消息序列号 array</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>收藏类型   1消息收藏</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "collection_id",
            "description": "<p>收藏列表id (取消收藏只传该字段)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImMsgController.php",
    "groupTitle": "im"
  },
  {
    "type": "get",
    "url": "/api/v4/im/msg_collection_list",
    "title": "消息收藏列表",
    "name": "msg_collection_list",
    "version": "1.0.0",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "keywords",
            "description": "<p>收藏消息关键字</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "page",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImMsgController.php",
    "groupTitle": "im"
  },
  {
    "type": "post",
    "url": "/api/v4/im/msg_send_all",
    "title": "消息群发",
    "name": "msg_send_all",
    "version": "1.0.0",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "From_Account",
            "description": "<p>发送方帐号</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "To_Account",
            "description": "<p>接收方用户 数组类型</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "To_Group",
            "description": "<p>接收方群组 数组类型</p>"
          },
          {
            "group": "Parameter",
            "type": "json",
            "optional": false,
            "field": "Msg_Content",
            "description": "<p>消息体:[{&quot;MsgType&quot;:&quot;TIMTextElem&quot;,&quot;Text&quot;:&quot;文本消息&quot;},{&quot;MsgType&quot;:&quot;TIMSoundElem&quot;,&quot;Url&quot;:&quot;语音url&quot;},{&quot;MsgType&quot;:&quot;TIMImageElem&quot;,&quot;Url&quot;:&quot;http://xxx/3200490432214177468_144115198371610486_D61040894AC3DE44CDFFFB3EC7EB720F/0&quot;},{&quot;MsgType&quot;:&quot;TIMFileElem&quot;,&quot;Url&quot;:&quot;http://xxx/3200490432214177468_144115198371610486_D61040894AC3DE44CDFFFB3EC7EB720F/0&quot;,&quot;FileName&quot;:&quot;file&quot;},{&quot;MsgType&quot;:&quot;TIMVideoFileElem&quot;,&quot;Url&quot;:&quot;http://xxx/3200490432214177468_144115198371610486_D61040894AC3DE44CDFFFB3EC7EB720F/0&quot;},{&quot;MsgType&quot;:&quot;TIMCustomElem&quot;,&quot;Data&quot;:&quot;eqweqeqe&quot;}]  消息类型  根据MsgType  对应im的字段类型 参考：https://cloud.tencent.com/document/product/269/2720</p>"
          },
          {
            "group": "Parameter",
            "type": "array",
            "optional": false,
            "field": "collection_id",
            "description": "<p>收藏id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImMsgController.php",
    "groupTitle": "im"
  },
  {
    "type": "post",
    "url": "/api/v4/im/send_all_list",
    "title": "群发列表",
    "name": "send_all_list",
    "version": "1.0.0",
    "group": "im",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "page",
            "description": "<p>page</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "list_id",
            "description": "<p>list_id  如果有该参数  获取全部名字  没有只获取10个名称</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImMsgController.php",
    "groupTitle": "im"
  },
  {
    "type": "post",
    "url": "/api/v4/income/cash_data",
    "title": "钱包认证信息 || 提交修改认证",
    "name": "cash_data",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>1 钱包认证信息[只需要user_id]   2 提交修改认证[所有参数都需要]</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "status",
            "description": "<p>1个人认证，2企业认证</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "org_name",
            "description": "<p>机构名称</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "org_area",
            "description": "<p>机构地区</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "org_address",
            "description": "<p>机构详细地址</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "org_license_picture",
            "description": "<p>营业执照照片</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "bank_opening",
            "description": "<p>开户行</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "bank_number",
            "description": "<p>银行卡号</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "bank_permit_picture",
            "description": "<p>开户许可证照片</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "idcard",
            "description": "<p>身份证号</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "truename",
            "description": "<p>真实姓名</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "idcard_cover",
            "description": "<p>身份证图片</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "idcard_type",
            "description": "<p>身份证类型  1:身份证 2:台胞证 3:香港身份证 4:澳门身份证 5:护照</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"truename\": \"房思楠\",   姓名\n\"idcard_cover\": \"nlsg/idcard/20200301113714131141.png\",     身份证照片\n\"idcard\": \"123456789123456789\",         身份证号\n\"idcard_type\": 1,               1:身份证 2:台胞证 3:香港身份证 4:澳门身份证 5:护照\n\"org_name\": \"\",                 机构名称\n\"org_address\": \"\",              机构地区\n\"org_license_picture\": \"\",      营业执照照片\n\"bank_opening\": \"\",             开户行\n\"bank_number\": \"\",              银行卡号\n\"bank_permit_picture\": \"\"       开户许可证照片\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/detail",
    "title": "收益详情",
    "name": "detail",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "earn_type",
            "description": "<p>1支出 2收入</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"type\": 2,\n\"created_at\": \"2020-06-03T06:12:34.000000Z\",\n\"price\": \"110.00\",\n\"content\": \"分享收益\",\n\"name\": \"王琨专栏\",\n\"nick_name\": null\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/get_deposit",
    "title": "充值记录",
    "name": "detail",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "earn_type",
            "description": "<p>1支出 2收入</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n    {\n    \"price\": \"10.00\",\n    \"created_at\": \"2020-07-09 10:49:16\"\n    }\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/get_list",
    "title": "收支明细[默认显示支出的  不可同时显示支出和收入]",
    "name": "get_list",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "earn_type",
            "description": "<p>1支出 2收入</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>收支类型 earn_type=1时type[type类型  1电商支付    2内容支付   3 会员       4 所有提现] earn_type=2时 type [type类型  1 电商收益   2内容收益   3会员收益  4直播收益]</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "date",
            "description": "<p>格式化的时间精确到月</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"expenditure_price\": \"10.00\",\n\"income_price\": \"110.00\",\n\"list\": {\n\"current_page\": 1,\n\"data\": [\n{\n\"id\": 1,\n\"ordernum\": \"202005231631148119\",  所属订单号\n\"created_at\": \"2020-06-03 14:12:34\",\n\"type\": 2,          同请求参数\n\"user_id\": 211172,\n\"price\": \"110.00\",    金额\n\"order_detail_id\": 0,\n\"subsidy_type\": 0,\n\"earn_type\": 2,             //1支出 2收入\n\"pay_content\": \"到账成功\",   状态描述\n\"content\": \"分享收益\",      类型描述\n\"name\": \"王琨专栏\"          支出|收益 主体\n}\n],\n\"first_page_url\": \"http://nlsgv4.com/api/v4/income/get_list?page=1\",\n\"from\": 1,\n\"last_page\": 1,\n\"last_page_url\": \"http://nlsgv4.com/api/v4/income/get_list?page=1\",\n\"next_page_url\": null,\n\"path\": \"http://nlsgv4.com/api/v4/income/get_list\",\n\"per_page\": 50,\n\"prev_page_url\": null,\n\"to\": 1,\n\"total\": 1\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/get_withdraw",
    "title": "提现 个税计算",
    "name": "get_withdraw",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "money",
            "description": "<p>提现金额</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1597030864,\n\"data\": {\n\"balance\": 0,    可提现余额\n\"income_tax\": 0,    个税\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/index",
    "title": "用户钱包首页信息",
    "name": "index",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"is_pass\": 1, //-1信息未认证  1已认证 2 拒绝\n\"nick_name\":\"房思楠\",  //昵称\n\"not_pass_reason\": \"\",  //拒绝理由\n\"bind_tx\": 1,           //1 已绑定\n\"bind_tx_type\": 1,      //1微信  2支付宝 已绑定\n\"amount\": 0,\n\"type\": 0,\n\"idcard_type\": 1,          // 身份类型  1身份证\n\"org_type\": 1,              // '1：个人   2：机构'\n\"org_name\": \"\",             //机构名称\n\"truename\": \"房思楠\"           //真实姓名\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/present",
    "title": "绑定提现微信|支付宝账户",
    "name": "present",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>1 1微信  2支付宝</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "openid",
            "description": "<p>openid</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "zfb_account",
            "description": "<p>支付宝账号</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/profit",
    "title": "用户钱包首页 (统计数)",
    "name": "profit",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"monthly_earnings\": 0,       //获取当月\n\"last_month_earnings\": 0,    //获取上月结算\n\"all_proceeds\": 0,           //获取全部收益\n\"cashable_income\": 0,            //获取提现余额\n\"stay_money\": 0,                 //待收益\n\"ios_balance\": null,             //能量币\n\"toDay\": 0,                    //今日\n\"yesterDay\": 0,                  //昨天\n\"user_status\": 0,               // 0 未绑定信息  1认证通过|个人  2认证通过|个人  绑定支付宝或者微信   3认证通过|机构\n\"goods_data\": 1,                //5电商推客收益\n\"column_data\": 0,                   //6专栏推客收益\n\"work_data\": 0,                 //7精品课收益\n\"vip_data\": 0                    //8会员收益\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/send_invoice",
    "title": "邮寄发票",
    "name": "send_invoice",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "express",
            "description": "<p>快递公司快递公司 编码 如：YUNDA</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "express_num",
            "description": "<p>快递单号</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "img",
            "description": "<p>图片</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"id\": 1,\n\"user_id\": 211172,\n\"express\": \"YUNDA\",\n\"express_num\": \"12312313\",\n\"img\": \"image\",\n\"created_at\": \"2020-07-09 14:30:55\",\n\"updated_at\": \"2020-07-09 14:30:55\",\n\"status\": 0         //状态 1 审核通过 2 未通过\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/income/withdrawals",
    "title": "提现操作",
    "name": "withdrawals",
    "version": "1.0.0",
    "group": "income",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "money",
            "description": "<p>金额</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "channel",
            "description": "<p>ali|WeChat  支付宝或微信</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/IncomeController.php",
    "groupTitle": "income"
  },
  {
    "type": "get",
    "url": "/api/v4/order/close_order",
    "title": "取消订单",
    "name": "close_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "post",
    "url": "/api//v4/order/create_coin_order",
    "title": "//能量币充值（ios支付使用）",
    "name": "create_coin_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "coin_id",
            "description": "<p>能量币代码 如：merchant.NLSGApplePay.6nlb</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "post",
    "url": "api/v4/order/create_column_order",
    "title": "专栏下单",
    "name": "create_column_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "column_id",
            "description": "<p>专栏id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "coupon_id",
            "description": "<p>优惠券id 默认0</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id 默认0</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "os_type",
            "description": "<p>os_type 1 安卓 2ios</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id  直播间购买时传</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "pay_type",
            "description": "<p>1 微信端 2app微信 3app支付宝 4ios</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "post",
    "url": "/api/v4/order/create_new_vip_order",
    "title": "幸福360下单",
    "name": "create_new_vip_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "level",
            "description": "<p>1 360会员  2钻石合伙人</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "os_type",
            "description": "<p>os_type 1 安卓 2ios</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "post",
    "url": "/api//v4/order/create_products_order",
    "title": "线下产品下单",
    "name": "create_products_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "product_id",
            "description": "<p>产品id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "os_type",
            "description": "<p>os_type 1 安卓 2ios</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "post",
    "url": "/api//v4/order/create_reward_order",
    "title": "打赏下单",
    "name": "create_reward_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_id",
            "description": "<p>打赏类型目标id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "reward",
            "description": "<p>//1 鲜花 2爱心 3书籍 4咖啡  默认1</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "reward_num",
            "description": "<p>数量 默认1</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "reward_type",
            "description": "<p>打赏类型1专栏|讲座 2课程|听书  3想法   4百科  5直播 (每个类型只需要传对应id)</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "os_type",
            "description": "<p>os_type 1 安卓 2ios</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "post",
    "url": "/api//v4/order/create_send_order",
    "title": "赠送课程下单",
    "name": "create_send_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_id",
            "description": "<p>目标id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "send_type",
            "description": "<p>目标类型   1 专栏  2课程|听书    6讲座</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "os_type",
            "description": "<p>os_type 1 安卓 2ios</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "remark",
            "description": "<p>增言</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "coupon_id",
            "description": "<p>优惠券id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "post",
    "url": "/api/v4/order/create_works_order",
    "title": "精品课下单",
    "name": "create_works_order",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "work_id",
            "description": "<p>课程id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "coupon_id",
            "description": "<p>优惠券id 默认0</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "inviter",
            "description": "<p>推客id 默认0</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "os_type",
            "description": "<p>os_type 1 安卓 2ios</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id  直播间购买时传</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "get",
    "url": "/api/v4/order/get_coupon",
    "title": "获取我的优惠券",
    "name": "get_coupon",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1专栏  2会员  5课程  6赠送专用  7讲座  8训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "price",
            "description": "<p>订单金额</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 1,\n\"name\": \"心智优惠券\",\n\"number\": \"12353\",\n\"type\": 1,                  1专栏  2会员 3商品 4免邮券 5课程\n\"user_id\": 211172,\n\"status\": 1,            //0 未领取 1 未使用 2已使用 3已过期  4已删除\n\"price\": \"10.00\",           //优惠券金额\n\"full_cut\": \"99.00\",        //满减金额\n\"explain\": \"\",          //描述\n\"order_id\": 0,\n\"flag\": \"\",\n\"get_way\": 1,\n\"cr_id\": 0,\n\"created_at\": null,\n\"updated_at\": null,\n\"begin_time\": null,             生效时间\n\"end_time\": \"2020-07-28 23:59:59\",  失效时间\n\"used_time\": null           使用时间\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "get",
    "url": "/api/v4/order/get_subscribe",
    "title": "我的-已购",
    "name": "get_subscribe",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>1 专栏  2作品 3直播  4会员 5线下产品  6讲座</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "is_audio_book",
            "description": "<p>当type == 2(作品)时  0课程  1 听书  2全部</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "get",
    "url": "/api/v4/order/order_detail",
    "title": "订单详情",
    "name": "order_detail",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"id\": 3,\n\"type\": 1,              类型  1、专栏  9、课程  15讲座\n\"relation_id\": 1,   对应的id\n\"user_id\": 211172,\n\"status\": 1,  0待支付  1 已支付  2取消【不展示】\n\"price\": \"99.00\",    金额\n\"pay_price\": \"0.01\",    实际支付金额\n\"coupon_id\": 0,     优惠券id\n\"pay_time\": null,  支付时间\n\"ordernum\": \"202005231631148119\", 订单号OA\n\"created_at\": \"2020-07-01 10:44:35\",  下单时间\n\"coupon_price\": 0,  优惠券金额\n\"relation_data\": [    内容信息\n{\n\"id\": 1,\n\"name\": \"王琨专栏\",\n\"title\": \"顶尖导师 经营能量\",\n\"subtitle\": \"顶尖导师 经营能量\",\n\"message\": \"\",\n\"price\": \"99.00\",\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"is_new\": 1\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "get",
    "url": "/api/v4/order/order_list",
    "title": "订单列表",
    "name": "order_list",
    "version": "1.0.0",
    "group": "order",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型  专栏 2 会员  3充值  4财务打款 5 打赏 6分享赚钱 7支付宝提现 8微信提现  9精品课  10直播    13能量币充值  14 线下产品(门票类)   15讲座  16新360会员 17赠送下单</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "status",
            "description": "<p>0待支付  1 已支付 2全部</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"current_page\": 1,\n\"data\": [\n{\n\"id\": 58,\n\"type\": 15,   类型  1、专栏  9、课程  15讲座\n\"relation_id\": 1,   对应id\n\"user_id\": 211172,\n\"status\": 1,        0待支付 1 支付\n\"price\": \"10.00\",       金额\n\"pay_price\": \"0.01\",        实际支付金额\n\"coupon_id\": 0,     优惠券id\n\"pay_time\": null,       支付时间\n\"ordernum\": \"20200709104916\",   订单号\n\"relation_data\": [\n{\n\"id\": 1,\n\"name\": \"王琨专栏\",\n\"title\": \"顶尖导师 经营能量\",\n\"subtitle\": \"顶尖导师 经营能量\",\n\"message\": \"\",\n\"price\": \"99.00\",\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"is_new\": 1\n}\n]\n},\n{\n\"id\": 45,\n\"type\": 9,\n\"relation_id\": 16,\n\"user_id\": 211172,\n\"status\": 1,\n\"price\": \"10.00\",\n\"pay_price\": \"0.00\",\n\"coupon_id\": 0,\n\"pay_time\": null,\n\"ordernum\": \"20200708114026\",\n\"relation_data\": [\n{\n\"id\": 16,\n\"user_id\": 168934,\n\"title\": \"如何经营幸福婚姻\",\n\"cover_img\": \"/nlsg/works/20190822150244797760.png\",\n\"subtitle\": \"\",\n\"price\": \"29.90\",\n\"user\": {\n\"id\": 168934,\n\"nickname\": \"chandler\"\n},\n\"is_new\": 1,\n\"is_free\": 1\n}\n]\n},\n{\n\"id\": 3,\n\"type\": 1,\n\"relation_id\": 1,\n\"user_id\": 211172,\n\"status\": 1,\n\"price\": \"99.00\",\n\"pay_price\": \"0.01\",\n\"coupon_id\": 0,\n\"pay_time\": null,\n\"ordernum\": \"202005231631148119\",\n\"relation_data\": [\n{\n\"id\": 1,\n\"name\": \"王琨专栏\",\n\"title\": \"顶尖导师 经营能量\",\n\"subtitle\": \"顶尖导师 经营能量\",\n\"message\": \"\",\n\"price\": \"99.00\",\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"is_new\": 1\n}\n]\n}\n],\n\"first_page_url\": \"http://nlsgv4.com/api/v4/order/order_list?page=1\",\n\"from\": 1,\n\"last_page\": 1,\n\"last_page_url\": \"http://nlsgv4.com/api/v4/order/order_list?page=1\",\n\"next_page_url\": null,\n\"path\": \"http://nlsgv4.com/api/v4/order/order_list\",\n\"per_page\": 50,\n\"prev_page_url\": null,\n\"to\": 3,\n\"total\": 3\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/OrderController.php",
    "groupTitle": "order"
  },
  {
    "type": "get",
    "url": "api/v4/pay/ali_pay",
    "title": "支付宝支付-预下单",
    "name": "ali_pay",
    "version": "1.0.0",
    "group": "pay",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1专栏 2会员 5打赏 8  电商   9精品课 11直播 14线下课购买 15讲座  16幸福360购买   17赠送  18 训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/PayController.php",
    "groupTitle": "pay"
  },
  {
    "type": "get",
    "url": "api/v4/pay/apple_pay",
    "title": "苹果支付验证接口 [ 苹果端 能量币充值 ]",
    "name": "apple_pay",
    "version": "1.0.0",
    "group": "pay",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "receipt-data",
            "description": "<p>苹果支付返回信息</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/PayController.php",
    "groupTitle": "pay"
  },
  {
    "type": "get",
    "url": "api/v4/pay/order_find",
    "title": "下单查询接口",
    "name": "order_find",
    "version": "1.0.0",
    "group": "pay",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1专栏 2会员 5打赏 9精品课 听课  11直播 12预约回放 8商品订单</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/PayController.php",
    "groupTitle": "pay"
  },
  {
    "type": "get",
    "url": "api/v4/pay/pay_coin",
    "title": "能量币支付回调",
    "name": "pay_coin",
    "version": "1.0.0",
    "group": "pay",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>user_id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order_id",
            "description": "<p>order_id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "pay_type",
            "description": "<p>当类型为[ 专栏  会员  打赏  精品课]时 传1   类型为[ 月卡  季卡 押金  违约金 退押金] 传2</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/PayController.php",
    "groupTitle": "pay"
  },
  {
    "type": "get",
    "url": "api/v4/pay/wechat_pay",
    "title": "微信支付-统一下单",
    "name": "wechat_pay",
    "version": "1.0.0",
    "group": "pay",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>1专栏 2会员 5打赏 8  电商   9精品课 11直播 14线下课购买 15讲座  16幸福360购买   17赠送  18 训练营</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"return_code\": \"SUCCESS\",\n\"return_msg\": \"OK\",\n\"appid\": \"wx3296e2b7430df182\",\n\"mch_id\": \"1460495202\",\n\"nonce_str\": \"mUXLVUSyafnOzjA4\",\n\"sign\": \"729C4C7B8D489945637D0BF61B333316\",\n\"result_code\": \"SUCCESS\",\n\"prepay_id\": \"wx1819455494088084c893b29c1290375800\",\n\"trade_type\": \"APP\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/PayController.php",
    "groupTitle": "pay"
  },
  {
    "type": "get",
    "url": "/api/v4/search/index",
    "title": "全局搜索 热词",
    "name": "index",
    "version": "1.0.0",
    "group": "search",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "flag",
            "description": "<p>类型(商品:only_goods)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 9,\n\"keywords\": \"爸爸\",\n\"user_id\": 1,\n\"num\": 6,\n\"created_at\": \"2020-06-28 11:14:44\",\n\"updated_at\": \"2020-06-28 11:14:53\"\n},\n{\n\"id\": 6,\n\"keywords\": \"如何培\",\n\"user_id\": 1,\n\"num\": 2,\n\"created_at\": \"2020-06-28 11:01:53\",\n\"updated_at\": \"2020-06-28 11:02:06\"\n},\n{\n\"id\": 8,\n\"keywords\": \"孩子\",\n\"user_id\": 0,\n\"num\": 2,\n\"created_at\": \"2020-06-28 11:02:16\",\n\"updated_at\": \"2020-06-28 11:16:14\"\n},\n{\n\"id\": 7,\n\"keywords\": \"haizi\",\n\"user_id\": 0,\n\"num\": 1,\n\"created_at\": \"2020-06-28 11:02:13\",\n\"updated_at\": \"2020-06-28 11:02:13\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/SearchController.php",
    "groupTitle": "search"
  },
  {
    "type": "get",
    "url": "/api/v4/search/search",
    "title": "全局搜索",
    "name": "search",
    "version": "1.0.0",
    "group": "search",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "keywords",
            "description": "<p>关键字</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "flag",
            "description": "<p>类型(商品:only_goods)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"column\": {\n\"res\": [],\n\"count\": 0\n},\n\"works\": {\n\"res\": [\n{\n\"id\": 18,\n\"type\": 2,\n\"title\": \"如何培养高情商孩子\",\n\"user_id\": 211172\n}\n],\n\"count\": 1\n},\n\"lecture\": {\n\"res\": [],\n\"count\": 0\n},\n\"listen_book\": {\n\"res\": [],\n\"count\": 0\n},\n\"goods\": {\n\"res\": [\n{\n\"id\": 66,\n\"name\": \"培养大器的孩子\",\n\"subtitle\": \"走出教育误区让孩子成为自己想成为的人\",\n\"original_price\": \"58.00\",\n\"price\": \"51.04\",\n\"picture\": \"/wechat/mall/goods/9210_1533089066.jpg\"\n},\n{\n\"id\": 67,\n\"name\": \"经营孩子的智慧\",\n\"subtitle\": \"激发孩子心中无限潜能让孩子成为一个充满智慧的人\",\n\"original_price\": \"50.00\",\n\"price\": \"44.00\",\n\"picture\": \"/wechat/mall/goods/4697_1533088898.jpg\"\n},\n{\n\"id\": 122,\n\"name\": \"孩子，你会更优秀（全套4册）\",\n\"subtitle\": \"德国教育家写给6~9岁孩子的心理疗愈童话\",\n\"original_price\": \"88.00\",\n\"price\": \"77.44\",\n\"picture\": \"/wechat/mall/goods/10000_1536646764.jpeg\"\n},\n{\n\"id\": 184,\n\"name\": \"钢铁是怎样练成的\",\n\"subtitle\": \"培养孩子在风暴中练就钢铁意志和崇高品德\",\n\"original_price\": \"35.80\",\n\"price\": \"31.50\",\n\"picture\": \"/wechat/mall/goods/8323_1533612770.png\"\n},\n{\n\"id\": 202,\n\"name\": \"小王子\",\n\"subtitle\": \"法国经典名著 滋养孩子心灵的精神财富\",\n\"original_price\": \"22.00\",\n\"price\": \"19.36\",\n\"picture\": \"/wechat/mall/goods/4517_1533625832.png\"\n},\n{\n\"id\": 205,\n\"name\": \"伊索寓言\",\n\"subtitle\": \"古希腊经典名著 培养孩子博爱、善良和真诚的品质\",\n\"original_price\": \"25.00\",\n\"price\": \"22.00\",\n\"picture\": \"/wechat/mall/goods/8155_1533626464.png\"\n},\n{\n\"id\": 209,\n\"name\": \"儿童财商绘本(全10册)\",\n\"subtitle\": \"帮助孩子建立正确的金钱观念 从容面对金钱问题\",\n\"original_price\": \"180.00\",\n\"price\": \"158.40\",\n\"picture\": \"/wechat/mall/goods/625_1544239955.png\"\n},\n{\n\"id\": 262,\n\"name\": \"蒙台梭利教育精华\",\n\"subtitle\": \"让孩子自信又独立\",\n\"original_price\": \"39.90\",\n\"price\": \"35.11\",\n\"picture\": \"/wechat/mall/goods/3835_1542853822.jpeg\"\n},\n{\n\"id\": 328,\n\"name\": \"我不要上幼儿园\",\n\"subtitle\": \"了解孩子的内心世界，增进亲子情感的更好途径 [3-6岁]\",\n\"original_price\": \"35.00\",\n\"price\": \"30.80\",\n\"picture\": \"/wechat/mall/goods/454_1545200190.png\"\n},\n{\n\"id\": 333,\n\"name\": \"儿童情商社交游戏绘本\",\n\"subtitle\": \"经典游戏力大奖版权引进绘本童书大师给孩子的25堂情商课\",\n\"original_price\": \"375.00\",\n\"price\": \"375.00\",\n\"picture\": \"/nlsg/goods/20191106174941142518.jpg\"\n},\n{\n\"id\": 348,\n\"name\": \"忍住！别插手\",\n\"subtitle\": \"让孩子从3岁开始学习独立的自我管理课\",\n\"original_price\": \"108.00\",\n\"price\": \"95.04\",\n\"picture\": \"/wechat/mall/goods/190313/4328_1547707310.png\"\n},\n{\n\"id\": 370,\n\"name\": \"如何读懂孩子的行为\",\n\"subtitle\": \"理解并解决孩子各种行为问题的方法\",\n\"original_price\": \"32.00\",\n\"price\": \"28.16\",\n\"picture\": \"/nlsg/goods/20191101173308844950.png\"\n},\n{\n\"id\": 371,\n\"name\": \"教室里的正面管教\",\n\"subtitle\": \"培养孩子人生技能造就理想班级氛围的“黄金准则”\",\n\"original_price\": \"30.00\",\n\"price\": \"30.00\",\n\"picture\": \"/wechat/mall/goods/190313/5896_1552013600.png\"\n},\n{\n\"id\": 372,\n\"name\": \"十几岁孩子的正面管教\",\n\"subtitle\": \"养育青春期十几岁孩子的“黄金准则”\",\n\"original_price\": \"35.00\",\n\"price\": \"30.80\",\n\"picture\": \"/wechat/mall/goods/190313/6206_1552013731.png\"\n},\n{\n\"id\": 373,\n\"name\": \"3～6岁孩子的正面管教\",\n\"subtitle\": \"家庭教育畅销书养育3～6岁孩子的“黄金准则”\",\n\"original_price\": \"42.00\",\n\"price\": \"36.96\",\n\"picture\": \"/wechat/mall/goods/190313/9795_1552013918.png\"\n},\n{\n\"id\": 374,\n\"name\": \"正面管教A-Z\",\n\"subtitle\": \"以实例讲解不惩罚不娇纵管教孩子的“黄金准则”\",\n\"original_price\": \"45.00\",\n\"price\": \"39.60\",\n\"picture\": \"/wechat/mall/goods/190313/6563_1552014044.png\"\n},\n{\n\"id\": 377,\n\"name\": \"正面管教\",\n\"subtitle\": \"如何不惩罚、不娇纵地有效管教孩子\",\n\"original_price\": \"38.00\",\n\"price\": \"33.44\",\n\"picture\": \"/wechat/mall/goods/190313/3405_1552386629.png\"\n},\n{\n\"id\": 378,\n\"name\": \"孩子，把你的手给我\",\n\"subtitle\": \"\",\n\"original_price\": \"32.00\",\n\"price\": \"28.16\",\n\"picture\": \"/nlsg/goods/20191101173401613891.png\"\n},\n{\n\"id\": 379,\n\"name\": \"如何培养孩子的社会能力\",\n\"subtitle\": \"教孩子学会解决冲突和与人相处的技巧\",\n\"original_price\": \"30.00\",\n\"price\": \"26.40\",\n\"picture\": \"/wechat/mall/goods/190313/2941_1552386834.png\"\n},\n{\n\"id\": 391,\n\"name\": \"艺术启蒙绘画套装\",\n\"subtitle\": \"123件不同绘画材料定制套盒满足孩子的绘画天赋\",\n\"original_price\": \"199.00\",\n\"price\": \"189.05\",\n\"picture\": \"/wechat/mall/goods/5460_1554891417.png\"\n},\n{\n\"id\": 394,\n\"name\": \"科学盒子基础版\",\n\"subtitle\": \"一个盒子12个科学实验包 成就每个孩子的科学梦\",\n\"original_price\": \"199.00\",\n\"price\": \"189.05\",\n\"picture\": \"/wechat/mall/goods/3254_1555654752.png\"\n},\n{\n\"id\": 398,\n\"name\": \"家庭版蒙氏教具-感官系列\",\n\"subtitle\": \"感官是心灵的门户为孩子打开认识世界的大门\",\n\"original_price\": \"729.00\",\n\"price\": \"692.55\",\n\"picture\": \"/wechat/mall/goods/2080_1559282072.png\"\n},\n{\n\"id\": 415,\n\"name\": \"儿童绘画与心理发展（6~9岁）\",\n\"subtitle\": \"帮助孩子释放不良情绪提升亲子关系质量\",\n\"original_price\": \"49.80\",\n\"price\": \"43.82\",\n\"picture\": \"/nlsg/goods/20190906095752891447.png\"\n},\n{\n\"id\": 416,\n\"name\": \"儿童绘画与心理发展（9~12岁）\",\n\"subtitle\": \"读懂儿童绘画走进孩子心里让亲子关系融洽和谐\",\n\"original_price\": \"49.80\",\n\"price\": \"43.82\",\n\"picture\": \"/nlsg/goods/20190906152020393177.png\"\n},\n{\n\"id\": 421,\n\"name\": \"与原生家庭和解\",\n\"subtitle\": \"童年早期的生活和教养经历是决定孩子一生快乐与否的关键\",\n\"original_price\": \"42.00\",\n\"price\": \"36.96\",\n\"picture\": \"/nlsg/goods/20190906161418147159.png\"\n},\n{\n\"id\": 424,\n\"name\": \"正面管教儿童心理学\",\n\"subtitle\": \"一部家长对孩子感到迷茫困惑不知所措时的应急手册\",\n\"original_price\": \"35.00\",\n\"price\": \"30.80\",\n\"picture\": \"/nlsg/goods/20190907095339193806.png\"\n},\n{\n\"id\": 428,\n\"name\": \"轻松做父母只需100天\",\n\"subtitle\": \"100天掌握孩子管教那些事儿让你轻松做父母\",\n\"original_price\": \"49.80\",\n\"price\": \"49.80\",\n\"picture\": \"/nlsg/goods/20190924100547509956.png\"\n},\n{\n\"id\": 431,\n\"name\": \"戒掉孩子的拖延症\",\n\"subtitle\": \"从孩子拖延的诱因出发28种拖延类型逐一解决\",\n\"original_price\": \"39.80\",\n\"price\": \"35.02\",\n\"picture\": \"/nlsg/goods/20191015140711849806.png\"\n},\n{\n\"id\": 432,\n\"name\": \"妈妈心态决定孩子状态\",\n\"subtitle\": \"一本书解决孩子成长中的心理问题轻松摆脱育儿焦虑\",\n\"original_price\": \"45.00\",\n\"price\": \"39.60\",\n\"picture\": \"/nlsg/goods/20191015141138501974.png\"\n},\n{\n\"id\": 433,\n\"name\": \"如何培养孩子的沟通力\",\n\"subtitle\": \"8大关键步骤培养让孩子受益一生的高情商竞争力\",\n\"original_price\": \"45.00\",\n\"price\": \"45.00\",\n\"picture\": \"/nlsg/goods/20191015141416747915.png\"\n},\n{\n\"id\": 434,\n\"name\": \"如何培养孩子的学习力\",\n\"subtitle\": \"5大关键步骤培养让孩子受益一生的学习思维\",\n\"original_price\": \"45.00\",\n\"price\": \"45.00\",\n\"picture\": \"/nlsg/goods/20191015142050532443.png\"\n},\n{\n\"id\": 436,\n\"name\": \"如何培养孩子的专注力\",\n\"subtitle\": \"6大关键步骤培养让孩子受益一生的专注力\",\n\"original_price\": \"45.00\",\n\"price\": \"45.00\",\n\"picture\": \"/nlsg/goods/20191015142748189211.png\"\n},\n{\n\"id\": 438,\n\"name\": \"自私的父母\",\n\"subtitle\": \"改善与父母的亲子关系与自己的孩子更好地相处\",\n\"original_price\": \"38.00\",\n\"price\": \"33.44\",\n\"picture\": \"/nlsg/goods/20191015143444811573.png\"\n},\n{\n\"id\": 461,\n\"name\": \"0~3岁孩子的正面管教\",\n\"subtitle\": \"影响孩子一生的头三年养育0～3岁孩子的“黄金准则”\",\n\"original_price\": \"42.00\",\n\"price\": \"36.96\",\n\"picture\": \"/nlsg/goods/20191123133332508621.png\"\n},\n{\n\"id\": 472,\n\"name\": \"儿童教育心理学\",\n\"subtitle\": \"如何正确参与孩子的成长理解孩子的心理情绪\",\n\"original_price\": \"38.00\",\n\"price\": \"33.44\",\n\"picture\": \"/nlsg/goods/20191226180419234794.jpg\"\n},\n{\n\"id\": 486,\n\"name\": \"写给孩子的数学三书\",\n\"subtitle\": \"奇妙的数学趣味百科365知识文化读物\",\n\"original_price\": \"99.00\",\n\"price\": \"99.00\",\n\"picture\": \"/nlsg/goods/20200304193842998885.jpg\"\n}\n],\n\"count\": 36\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/SearchController.php",
    "groupTitle": "search"
  },
  {
    "type": "post",
    "url": "/api/v4/shopping_cart/create",
    "title": "添加,编辑",
    "version": "1.0.0",
    "name": "_api_v4_shopping_cart_create",
    "group": "shopping_cart",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/shopping_cart/create"
      }
    ],
    "description": "<p>添加,编辑</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sku_number",
            "description": "<p>sku</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "replace",
              "add"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>添加方式(replace:覆盖数量  add:累计数量)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "inviter",
            "description": "<p>邀请人</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"goods_id\":98,\n\"sku_number\":\"1835913656\",\n\"num\":666,\n\"id\":1,\n\"flag\":\"replace\",\n\"inviter\":168934\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"code\": true,\n\"msg\": \"成功\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ShoppingCartController.php",
    "groupTitle": "shopping_cart"
  },
  {
    "type": "get",
    "url": "/api/v4/shopping_cart/get_list",
    "title": "购物车列表",
    "version": "4.0.0",
    "name": "_api_v4_shopping_cart_get_list",
    "group": "shopping_cart",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/shopping_cart/get_list"
      }
    ],
    "description": "<p>购物车列表</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "id",
            "description": "<p>list列表  invalid_list失效商品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_number",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "num",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods_name",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods_subtitle",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sku_list",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"list\": [\n{\n\"id\": 4,\n\"goods_id\": 98,\n\"sku_number\": 1835913656,\n\"num\": 110,\n\"invalid\": 0,\n\"goods_name\": \"王琨专栏学习套装\",\n\"goods_subtitle\": \"王琨老师专栏年卡1张+《琨说》珍藏版\",\n\"original_price\": \"399.00\",\n\"price\": \"254.15\",\n\"sku_list\": {\n\"id\": 1369,\n\"goods_id\": 98,\n\"sku_number\": \"1835913656\",\n\"picture\": \"/wechat/mall/goods/8873_1545796221.png\",\n\"original_price\": \"399.00\",\n\"price\": \"299.00\",\n\"stock\": 457,\n\"status\": 1,\n\"sku_value_list\": [\n{\n\"id\": 241,\n\"sku_id\": 1369,\n\"key_name\": \"规格\",\n\"value_name\": \"王琨专栏学习套装\"\n}\n]\n}\n}\n],\n\"invalid_list\": [\n{\n\"id\": 5,\n\"goods_id\": 476,\n\"sku_number\": 1654630825,\n\"num\": 2,\n\"invalid\": 1,\n\"goods_name\": \"藏在地图里的成语（全四册）\",\n\"goods_subtitle\": \"一套与地图相结合的成语把知识断点连成一片海\",\n\"original_price\": \"136.00\",\n\"price\": \"101.72\",\n\"sku_list\": {\n\"id\": 1936,\n\"goods_id\": 476,\n\"sku_number\": \"1654630825\",\n\"picture\": \"/nlsg/goods/20191227135102333850.jpg\",\n\"original_price\": \"136.00\",\n\"price\": \"119.68\",\n\"stock\": 99,\n\"status\": 1,\n\"sku_value_list\": [\n{\n\"id\": 383,\n\"sku_id\": 1936,\n\"key_name\": \"规格\",\n\"value_name\": \"藏在地图里的成语（全四册）\"\n}\n]\n}\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ShoppingCartController.php",
    "groupTitle": "shopping_cart"
  },
  {
    "type": "put",
    "url": "/api/v4/shopping_cart/status_change",
    "title": "修改状态",
    "version": "1.0.0",
    "name": "_api_v4_shopping_cart_status_change",
    "group": "shopping_cart",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/shopping_cart/status_change"
      }
    ],
    "description": "<p>修改状态</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>状态(删除)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id(如  1或者1,2,3 或者[1,2,3])</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"flag\":\"del\",\n\"id\":1\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"code\": true,\n\"msg\": \"成功\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ShoppingCartController.php",
    "groupTitle": "shopping_cart"
  },
  {
    "type": "post",
    "url": "/api/v4/upload/del_ali_ydb",
    "title": "删除音视频点播",
    "name": "DelAliYdb",
    "version": "1.0.0",
    "group": "upload",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1 视频 2音频 3图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "videoid",
            "description": "<p>点播id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n  \"code\": 200,\n  \"msg\": \"成功\",\n  \"now\": 1627033302,\n  \"data\": { //音视频返回\n     \"RequestId\": \"BC452DE2-5BAB-45A7-989B-C3F62CC41855\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AliUploadController.php",
    "groupTitle": "upload"
  },
  {
    "type": "post",
    "url": "/api/v4/upload/get_play",
    "title": "获取播放权限",
    "name": "GetPlay",
    "version": "1.0.0",
    "group": "upload",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "flag",
            "description": "<p>标记  1 播放地址 2播放凭证</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "videoid",
            "description": "<p>点播id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "timeout",
            "description": "<p>有效时长(默认10分)    flag为2时传入</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n  \"code\": 200,\n  \"msg\": \"成功\",\n  \"now\": 1627033302,\n  \"data\": {\n      \"url\": \"https://audiovideo.ali.nlsgapp.com/13a3ba6d4f1b4c7ba1b585cad344562e/9e59679532694464973a0d0abef64977-6e86e0f1fab7f47b962a9711b2a9eb8d-ld.mp4\" //flag为1返回\n      \"PlayAuth\": \"eyJTZWN1cml0eVRva2VuIjoiQ0FJU2h3TjFxNkZ0NUIyeWZTaklyNWZrSVl6a3JLbHoyYlM2UlI3OXRqZHRmTHBjaVAyVHBqejJJSDFOZEhGb0FlNGF0UDR4blcxVjdmc2Nsck1xRWNJWUdoeWJOWkFydE1zUHFGcnhKcExGc3QySjZyOEpqc1VRNklaZ3psbXBzdlhKYXNEVkVmbDJFNVhFTWlJUi8wMGU2TC8rY2lyWXBUWEhWYlNDbFo5Z2FQa09Rd0M4ZGtBb0xkeEtKd3hrMnQxNFVtWFdPYVNDUHdMU2htUEJMVXhtdldnR2wyUnp1NHV5M3ZPZDVoZlpwMXI4eE80YXhlTDBQb1AyVjgxbExacGxlc3FwM0k0U2M3YmFnaFpVNGdscjhxbHg3c3BCNVN5Vmt0eVdHVWhKL3phTElvaXQ3TnBqZmlCMGVvUUFQb3BGcC9YNmp2QWF3UExVbTliWXhncGhCOFIrWGo3RFpZYXV4N0d6ZW9XVE84MCthS3p3TmxuVXo5bUxMZU9WaVE0L1ptOEJQdzQ0RUxoSWFGMElVRVp6RjJ5RWNQSDVvUXFXT1YvN0ZaTG9pdjltamNCSHFIeno1c2VQS2xTMVJMR1U3RDBWSUpkVWJUbHpha2RHaFRTNUxQTmNLVklWTGc0OFd1aVBNYXgzYlFGRHI1M3ZzVGJiWHpaYjBtcHR1UG56ZDJWdWJWS1dnaytWR29BQk1HODRLVGNNMGY0dmZaSUZ1cjV2eGo0UkN3QUFpa0l0dVNaT2Y5ZFhCditxMVh1eDladVA0WjlnTm5ITzBXSUZEZGNCQ0phTTNyNFVaQUFXL25mR0FpblpjR2gxKzNqMExWaUFmQkN5a3VCQ0JGVUhqU0J4Ni9uYlRkTkhOVU13MjVLVndPN2RyMEdPd1pKd1B2MHpVaUFERmt4amNTYXU3MlFvREVqTVBOND0iLCJBdXRoSW5mbyI6IntcIkNJXCI6XCI3TDJ0V3hzejVYdDhtZFFIVlBiRVYxbUhnMFhLemFFQktKSkhBVkd1a21ZREJ1OC9RY2RNZ0JmQ0NsSkpaUTgvXCIsXCJDYWxsZXJcIjpcImZvcko3VG5wNXdkWXVQVnVvL0hOUWM5K0JXdjk5QlJ0REJYZktvcWR4amc9XCIsXCJFeHBpcmVUaW1lXCI6XCIyMDIxLTA3LTIzVDExOjUwOjIzWlwiLFwiTWVkaWFJZFwiOlwiMTNhM2JhNmQ0ZjFiNGM3YmExYjU4NWNhZDM0NDU2MmVcIixcIlBsYXlEb21haW5cIjpcImF1ZGlvdmlkZW8uYWxpLm5sc2dhcHAuY29tXCIsXCJTaWduYXR1cmVcIjpcIkV5bXptQ1MrTWVleitVd1FVR2s3Y1hQVFYrVT1cIn0iLCJWaWRlb01ldGEiOnsiU3RhdHVzIjoiTm9ybWFsIiwiVmlkZW9JZCI6IjEzYTNiYTZkNGYxYjRjN2JhMWI1ODVjYWQzNDQ1NjJlIiwiVGl0bGUiOiJ2aWRlby5tcDQiLCJDb3ZlclVSTCI6Imh0dHBzOi8vYXVkaW92aWRlby5hbGkubmxzZ2FwcC5jb20vMTNhM2JhNmQ0ZjFiNGM3YmExYjU4NWNhZDM0NDU2MmUvc25hcHNob3RzLzhmYTAyYjAxZTdjNTRlMzVhZTY2NmE3ZDM1M2VkNGZiLTAwMDA1LmpwZyIsIkR1cmF0aW9uIjo0NzUuMjY2N30sIkFjY2Vzc0tleUlkIjoiU1RTLk5UUWo2UEF2Um52UUc4TFI3OHA1cGc4OEQiLCJQbGF5RG9tYWluIjoiYXVkaW92aWRlby5hbGkubmxzZ2FwcC5jb20iLCJBY2Nlc3NLZXlTZWNyZXQiOiI3YmVUTHM2YVNBTnpKOGNOUXBYMjlRVXNWUWNFVFVpWmVmZW9CZlVRVUxHaiIsIlJlZ2lvbiI6ImNuLWJlaWppbmciLCJDdXN0b21lcklkIjoxMjU1Nzg3MDMzMjcwMTE4fQ==\",\n      \"VideoMeta\": {\n         \"Status\": \"Normal\",\n         \"VideoId\": \"13a3ba6d4f1b4c7ba1b585cad344562e\",\n         \"Title\": \"video.mp4\",\n         \"CoverURL\": \"https://audiovideo.ali.nlsgapp.com/13a3ba6d4f1b4c7ba1b585cad344562e/snapshots/8fa02b01e7c54e35ae666a7d353ed4fb-00005.jpg\",\n         \"Duration\": 475.2666931152344\n       },\n     \"RequestId\": \"5283C6AF-8C98-4434-9BE2-31280772DEC1\"\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AliUploadController.php",
    "groupTitle": "upload"
  },
  {
    "type": "post",
    "url": "/api/v4/upload/push_ali_auth",
    "title": "上传音视频点播和图片",
    "name": "PushAliAuth",
    "version": "1.0.0",
    "group": "upload",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1 视频 2音频 3图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "filename",
            "description": "<p>文件名(带扩展名)   音视频传参</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "imageext",
            "description": "<p>文件扩展名   图片传参</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n  \"code\": 200,\n  \"msg\": \"成功\",\n  \"now\": 1627033302,\n  \"data\": { //音视频返回\n     \"VideoId\": \"42bbabf7312346428ca2b2773d5e6fe9\",\n     \"UploadAddress\": \"eyJFbmRwb2ludCI6Imh0dHBzOi8vb3NzLWNuLWJlaWppbmcuYWxpeXVuY3MuY29tIiwiQnVja2V0Ijoib3V0aW4tNjc2YThhNDNlODM4MTFlYjhiZTYwMDE2M2UxMDhhOGYiLCJGaWxlTmFtZSI6Im9yaWdpbmFsL3dvcmtmbG93L2E3OGE3YWQtMTdhZDJiZGY1OTgtMDAwNC03NjIxLWI0MC04MTc2Ni5tcDQifQ==\",\n     \"RequestId\": \"562A2132-4999-4905-AD8F-6160F4C9BF71\",\n     \"UploadAuth\": \"eyJTZWN1cml0eVRva2VuIjoiQ0FJUzFnUjFxNkZ0NUIyeWZTaklyNWZ6QWVtTW9KVnRnYmlqY3hMVGxWZ0ZTdlp0dUlQTnB6ejJJSDFOZEhGb0FlNGF0UDR4blcxVjdmc2NsclVxRWNJWUdoeWJOWkFydE1zUHFGcnhKcGZadjh1ODRZQURpNUNqUWJvenNPRSttNTI4V2Y3d2FmK0FVQS9HQ1RtZDVNMFlvOWJUY1RHbFFDWnVXLy90b0pWN2I5TVJjeENsWkQ1ZGZybC9MUmRqcjhsbzF4R3pVUEcyS1V6U24zYjNCa2hsc1JZZTcyUms4dmFIeGRhQXpSRGNnVmJtcUpjU3ZKK2pDNEM4WXM5Z0c1MTlYdHlwdm9weGJiR1Q4Q05aNXo5QTlxcDlrTTQ5L2l6YzdQNlFIMzViNFJpTkw4L1o3dFFOWHdoaWZmb2JIYTlZcmZIZ21OaGx2dkRTajQzdDF5dFZPZVpjWDBha1E1dTdrdTdaSFArb0x0OGphWXZqUDNQRTNyTHBNWUx1NFQ0OFpYVVNPRHREWWNaRFVIaHJFazRSVWpYZEk2T2Y4VXJXU1FDN1dzcjIxN290ZzdGeXlrM3M4TWFIQWtXTFg3U0IyRHdFQjRjNGFFb2tWVzRSeG5lelc2VUJhUkJwYmxkN0JxNmNWNWxPZEJSWm9LK0t6UXJKVFg5RXoycExtdUQ2ZS9MT3M3b0RWSjM3V1p0S3l1aDRZNDlkNFU4clZFalBRcWl5a1QwbkZncGZUSzFSemJQbU5MS205YmFCMjUvelcrUGREZTBkc1Znb0psS0VwaUdXRzNSTE5uK3p0Sjl4YmtlRStzS1Vrdk9TK3NOcFRGQWp0b3NQVkZpSWU0Wm5vZ0krdS9Mc3RCbktxTC9xQW43dCtYQTU5ZGplOW8wSXEya2NKNjM3M3JMTTVHQ0E1U2JNT3ZGaHh2MjZBak0vSFU2RkhGVmkyKzJYaTM0OW9CUU1ybnE1SVI1MzZTN0tpRC9nSkpWRGpLRFJuQzBkWC9vSXhMckNOeDZrLzNSOUQreU83NDBDVVBoWllQdDBWZkt4elpmUkVROXVSTmZhR29BQlowczMrT1VBTmVkeU1PMmNrUlZENWdkcGNHSU1QTmhxVDdQOENjWmFubno4bENsM0tsTGtLdVBZUSttWW5QS053OHN6TTN5ODVKMlNRL3BsdFBWMEpuZXo4WTIvUlBNTFlHNCs4SkFyUHNtQ051YTh6TzFSVFNud1lCdVIwa09OOEN6OFB0ZXhXL3VuNXFJYWJQY1pXVGxaWTc3QXgvL0FCbGlXQ01qNHRUWT0iLCJBY2Nlc3NLZXlJZCI6IlNUUy5OVEZKUzhNSkw2ekhxNGJxWFBGeUFXRmZFIiwiRXhwaXJlVVRDVGltZSI6IjIwMjEtMDctMjNUMTA6NDE6NDJaIiwiQWNjZXNzS2V5U2VjcmV0IjoiRUp0dXc3UEFXYk5VdXk3WTEzUmhucWRhempTTGVKV1hxc0NSb1lySFNRUE4iLCJFeHBpcmF0aW9uIjoiMzYwMCIsIlJlZ2lvbiI6ImNuLWJlaWppbmcifQ==\"\n  }\n  \"data\":{ //图片返回\n     \"FileURL\": \"https://outin-676a8a43e83811eb8be600163e108a8f.oss-cn-beijing.aliyuncs.com/image/default/7D1F2DC6CF9946FEAC4F8EC76F731F16-6-2.jpg\",\n     \"UploadAddress\": \"eyJFbmRwb2ludCI6Imh0dHBzOi8vb3NzLWNuLWJlaWppbmcuYWxpeXVuY3MuY29tIiwiQnVja2V0Ijoib3V0aW4tNjc2YThhNDNlODM4MTFlYjhiZTYwMDE2M2UxMDhhOGYiLCJGaWxlTmFtZSI6ImltYWdlL2RlZmF1bHQvN0QxRjJEQzZDRjk5NDZGRUFDNEY4RUM3NkY3MzFGMTYtNi0yLmpwZyJ9\",\n     \"RequestId\": \"EE425893-8C74-4C33-9CF4-6BF771033EEA\",\n     \"UploadAuth\": \"eyJTZWN1cml0eVRva2VuIjoiQ0FJUzB3UjFxNkZ0NUIyeWZTaklyNVdHUCt6a21aVnp6dk8vU1JQa3NuVURXUGhEbDRQK3BEejJJSDFOZEhGb0FlNGF0UDR4blcxVjdmc2NsclVxRWNJWUdoeWJOWkFydE1zUHFGcnhKcGZadjh1ODRZQURpNUNqUWNBMjRlNCttNTI4V2Y3d2FmK0FVQkxHQ1RtZDVNQVlvOWJUY1RHbFFDWnVXLy90b0pWN2I5TVJjeENsWkQ1ZGZybC9MUmRqcjhsbzF4R3pVUEcyS1V6U24zYjNCa2hsc1JZZTcyUms4dmFIeGRhQXpSRGNnVmJtcUpjU3ZKK2pDNEM4WXM5Z0c1MTlYdHlwdm9weGJiR1Q4Q05aNXo5QTlxcDlrTTQ5L2l6YzdQNlFIMzViNFJpTkw4L1o3dFFOWHdoaWZmb2JIYTlZcmZIZ21OaGx2dkRTajQzdDF5dFZPZVpjWDBha1E1dTdrdTdaSFArb0x0OGphWXZqUDNQRTNyTHBNWUx1NFQ0OFpYVVNPRHREWWNaRFVIaHJFazRSVWpYZEk2T2Y4VXJXU1FDN1dzcjIxN290ZzdGeXlrM3M4TWFIQWtXTFg3U0IyRHdFQjRjNGFFb2tWVzRSeG5lelc2VUJhUkJwYmxkN0JxNmNWNWxPZEJSWm9LK0t6UXJKVFg5RXoycExtdUQ2ZS9MT3M3b0RWSjM3V1p0S3l1aDRZNDlkNFU4clZFalBRcWl5a1Qwa0ZncGZUSzFSemJQbU5MS205YmFCMjUvelcrUGREZTBkc1Znb0pWS0RwaUdXRzNSTE5uK3p0Sjl4YmtlRStzS1Vrdk9TK3NOcFRGQWp0b3NQVkZpSWU0Wm5vZ0krdS9Mc3RCbktxTC9xQW43dC8yOHg5ZFNmdmFzM3NCRTdJNnI2MmJITTUyU0M1eVRJUDVOVXdwbUhCRGRkSmoyc1lHRjh6ZnlvZ1hZS21nc01pV25jT1d4RXRnM0JqVDdvSXBGQmlLTFNteTRmWC9sSjVjM2NTaWE5K0Z0bkJlbUE2cTB3UmZoWWUrUkRRbUFFQ3ZQZ0xUZU5Hb0FCbnJ4ZzVsaUxjNjdwQm5xTlFVWjZPUzBlMmNsQUNNdWw5N1IrcHp1aHpIeTdlTjBYWlNxMTlrNkJxVWxrL1pGYVNrV2RNUDlKUVp5dFJMc2hPR1BGemZ3OWFJT3BIeGlyWXZyZkFLaVp1ckdxeHRaRUY4dkZzazBUdFpRV1M1dy90bWJIYjVMLytwMFVxWHNhTmU2VklOSWVwUGlBWVk0WTluditwUGxBcjhNPSIsIkFjY2Vzc0tleUlkIjoiU1RTLk5WM3RWUHRKUnkxVEs1VVZ1VlR3b3hGVUYiLCJFeHBpcmVVVENUaW1lIjoiMjAyMS0wNy0yM1QxMDo1NDozM1oiLCJBY2Nlc3NLZXlTZWNyZXQiOiJINEZpeUFTYTdEQjllNng4cVBnNlIzZXVZRFpqQXBqRjJFRHoyY0dOWFJQVCIsIkV4cGlyYXRpb24iOiIzNjAwIiwiUmVnaW9uIjoiY24tc2hhbmdoYWkifQ==\",\n     \"ImageId\": \"d850661b8add4c3f8d6b5deb04f1f8a8\",\n     \"ImageURL\": \"https://audiovideo.ali.nlsgapp.com/image/default/7D1F2DC6CF9946FEAC4F8EC76F731F16-6-2.jpg\" //图片地址使用这个\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AliUploadController.php",
    "groupTitle": "upload"
  },
  {
    "type": "post",
    "url": "/api/v4/upload/addmedia",
    "title": "上传成功入库",
    "name": "addmedia",
    "version": "1.0.0",
    "group": "upload",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1 视频 2音频 3图片 4文件</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "videoid",
            "description": "<p>点播id    ||type为4不传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>媒体地址  ||全链接</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>type为4时上传</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n  \"code\": 200,\n  \"msg\": \"成功\",\n  \"now\": 1627033302,\n  \"data\": {\n\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AliUploadController.php",
    "groupTitle": "upload"
  },
  {
    "type": "post",
    "url": "/api/v4/upload/del_ali_oss",
    "title": "删除阿里OSS文件",
    "name": "del_ali_oss",
    "version": "1.0.0",
    "group": "upload",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>文件名</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n    \"code\": 200,\n    \"msg\": \"成功\",\n    \"now\": 1627028907,\n    \"data\": [\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AliUploadController.php",
    "groupTitle": "upload"
  },
  {
    "type": "post",
    "url": "/api/v4/upload/file_ali_oss",
    "title": "oss上传文件",
    "name": "file_ali_oss",
    "version": "1.0.0",
    "group": "upload",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "file",
            "optional": false,
            "field": "file",
            "description": "<p>文件</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n     \"code\": 200,\n     \"msg\": \"成功\",\n     \"now\": 1627028886,\n     \"data\": {\n         \"url\": \"https://image.nlsgapp.com/\",\n         \"name\": \"1111/20210723d6d1d2835569399dcfcb36a2e140ac8e.doc\"   //删除时传此字段值\n     }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/AliUploadController.php",
    "groupTitle": "upload"
  },
  {
    "type": "get",
    "url": "api/v4/user/clear_history",
    "title": "我的--清空学习记录",
    "version": "4.0.0",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "his_id",
            "description": "<p>清空全部值为all  否则传id 多个用英文逗号拼接 如1,2,3</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "user",
    "name": "GetApiV4UserClear_history"
  },
  {
    "type": "get",
    "url": "api/v4/user/collection",
    "title": "我的--收藏列表",
    "version": "4.0.0",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>默认1  1专栏  2课程  3商品  4书单 5百科 6听书  7讲座  8训练营</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 91,\n\"name\": \"AR立体浮雕星座地球仪\",   //商品名称  类型不同返回字段不同\n\"picture\": \"/nlsg/goods/20191026172620981048.jpg\",\n\"original_price\": \"379.00\",\n\"price\": \"333.52\"\n}\n]\n}\n\n\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": [\n{\n\"id\": 1,\n\"name\": \"王琨专栏\",     //专栏名\n\"title\": \"顶尖导师 经营能量\",       //头衔\n\"subtitle\": \"顶尖导师 经营能量\",    //副标题\n\"message\": \"\",\n\"price\": \"99.00\",\n\"cover_pic\": \"/wechat/works/video/161627/2017121117503851065.jpg\",\n\"is_new\": 1\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "user",
    "name": "GetApiV4UserCollection"
  },
  {
    "type": "get",
    "url": "api/v4/user/history",
    "title": "我的--历史记录",
    "version": "4.0.0",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "order",
            "description": "<p>desc|asc</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column_name",
            "description": "<p>名称   优先级 专栏 &gt; 课程  &gt;章节</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works_name",
            "description": "<p>名称   优先级 专栏 &gt; 课程  &gt;章节</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works_info_name",
            "description": "<p>名称   优先级 专栏 &gt; 课程  &gt;章节</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "column_cover_img",
            "description": "<p>封面 优先级 专栏 &gt; 课程</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works_cover_img",
            "description": "<p>封面 优先级 专栏 &gt; 课程</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"07-05 \": [\n{\n\"id\": 8,\n\"column_id\": 0,\n\"works_id\": 16,\n\"worksinfo_id\": 1,\n\"user_id\": 211172,\n\"time_leng\": \"10\",\n\"time_number\": \"5\",\n\"is_del\": 0,\n\"created_at\": \"2020-07-04T19:47:22.000000Z\",\n\"updated_at\": \"2020-06-04T20:07:36.000000Z\",\n\"column_name\": \"\",\n\"column_cover_img\": \"\",\n\"works_name\": \"如何经营幸福婚姻\",\n\"works_cover_img\": \"/nlsg/works/20190822150244797760.png\",\n\"worksInfo_name\": \"01何为坚毅\"\n},\n{\n\"id\": 9,\n\"column_id\": 1,\n\"works_id\": 16,\n\"worksinfo_id\": 2,\n\"user_id\": 211172,\n\"time_leng\": \"0\",\n\"time_number\": \"\",\n\"is_del\": 0,\n\"created_at\": \"2020-07-04T19:47:22.000000Z\",\n\"updated_at\": null,\n\"column_name\": \"王琨专栏\",\n\"column_cover_img\": null,\n\"works_name\": \"如何经营幸福婚姻\",\n\"works_cover_img\": \"/nlsg/works/20190822150244797760.png\",\n\"worksInfo_name\": \"02坚毅品格的重要性\"\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "user",
    "name": "GetApiV4UserHistory"
  },
  {
    "type": "get",
    "url": "api/v4/user/statistics",
    "title": "我的数据统计",
    "version": "4.0.0",
    "group": "user",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "level",
            "description": "<p>等级  2推客  3黑钻  4皇钻  5服务商</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_vip",
            "description": "<p>1幸福大使  2钻石</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_author",
            "description": "<p>是否是作者 1是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "notify_num",
            "description": "<p>消息数量 &gt;0 显示</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "follow_num",
            "description": "<p>关注数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "fan_num",
            "description": "<p>粉丝数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "history_num",
            "description": "<p>学习记录数</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\":\n {\n      \"notify_num\": 1,\n     \"follow_num\": 2,\n      \"fan_num\": 2,\n     \"history_num\": 4\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "user",
    "name": "GetApiV4UserStatistics"
  },
  {
    "type": "POST",
    "url": "api/v4/change/phone",
    "title": "更换手机号",
    "version": "4.0.0",
    "group": "user",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>手机号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "code",
            "description": "<p>验证码</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>用户授权</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "成功响应:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\":\n {\n\n  }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/UserController.php",
    "groupTitle": "user",
    "name": "PostApiV4ChangePhone"
  },
  {
    "type": "post",
    "url": "/api/v4/works/neighbor",
    "title": "相邻章节",
    "version": "1.0.0",
    "name": "_api_v4_works_neighbor",
    "group": "works",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/works/neighbor"
      }
    ],
    "description": "<p>相邻章节</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "works_info_id",
            "description": "<p>章节id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>1 专栏  2作品  6讲座  7训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "column_id",
            "description": "<p>专栏/讲座 id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>相邻章节列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.previous",
            "description": "<p>上一个</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.current",
            "description": "<p>当前</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list.next",
            "description": "<p>下一个</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.next.works_info_id",
            "description": "<p>章节id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.next.works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "list.next.info_history",
            "description": "<p>历史记录</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "works",
            "description": "<p>作品信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.is_pay",
            "description": "<p>1为精品课</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works._is_free",
            "description": "<p>1限免</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.is_sub",
            "description": "<p>1为当前用户订阅了</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "/api/v4/works/get_works_category",
    "title": "课程首页分类 名师",
    "name": "get_works_category",
    "version": "1.0.0",
    "group": "works_get_works_index",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"category\": [\n{\n\"id\": 1,\n\"name\": \"父母关系\",\n\"count\": 2\n},\n{\n\"id\": 2,\n\"name\": \"亲子关系\",\n\"count\": 0\n}\n],\n\"teacher\": [\n{\n\"id\": 168934,\n\"nickname\": \"chandler_v4\"\n},\n{\n\"id\": 211172,\n\"nickname\": \"房某某\"\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works_get_works_index"
  },
  {
    "type": "get",
    "url": "api/v4/works/edit_history_time",
    "title": "更新学习进度 时长及百分比",
    "name": "edit_history_time",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_id",
            "description": "<p>对应id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_type",
            "description": "<p>1专栏   2讲座   3听书    4精品课程    5训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "works_info_id",
            "description": "<p>章节id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "time_leng",
            "description": "<p>百分比</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "time_number",
            "description": "<p>章节分钟数</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "api/v4/send/get_send_order",
    "title": "赠送订单详情",
    "name": "get_send_order",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order_id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"code\": 200,\n    \"msg\": \"成功\",\n    \"data\": [\n    ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/SendController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "api/v4/works/get_works_content",
    "title": "获取文稿",
    "name": "get_works_content",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "info_id",
            "description": "<p>章节id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "\n{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"id\": 1,\n\"works_info_id\": 16,\n\"content\": \"文稿内容\",\n\"created_at\": null,\n\"updated_at\": null\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "api/v4/works/get_works_detail",
    "title": "课程详情",
    "name": "get_works_detail",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "works_id",
            "description": "<p>课程id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order",
            "description": "<p>排序  asc默认正序 desc</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"code\": 200,\n    \"msg\": \"成功\",\n    \"data\": [\n    ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "/api/v4/works/get_works_index",
    "title": "课程首页",
    "name": "get_works_index",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order",
            "description": "<p>1 最多学习  2 最新上架  3最多收藏  4 最多分享</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "hide",
            "description": "<p>1 隐藏已购</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "teacher_id",
            "description": "<p>老师id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "is_free",
            "description": "<p>1免费</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "is_audio_book",
            "description": "<p>0全部  1 听书 2课程</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"works\": [\n{\n\"id\": 1,\n\"work_id\": 16,\n\"category_id\": 1,\n\"created_at\": null,\n\"updated_at\": null,\n\"works\": {\n\"id\": 16,\n\"user_id\": 168934,\n\"column_id\": 1,\n\"type\": 1,\n\"title\": \"如何经营幸福婚姻\",\n\"subtitle\": \"\",\n\"cover_img\": \"/nlsg/works/20190822150244797760.png\",\n\"detail_img\": \"/nlsg/works/20191023183946478177.png\",\n\"content\": \"<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>\",\n\"down_time\": null,\n\"online_time\": null,\n\"view_num\": 1295460,\n\"message\": null,\n\"is_pay\": 1,\n\"original_price\": \"29.90\",\n\"price\": \"29.90\",\n\"promotion_cost\": \"0.00\",\n\"twitter_price\": \"0.00\",\n\"subscribe_num\": 287,\n\"collection_num\": 0,\n\"timing_online\": 0,\n\"timing_time\": null,\n\"can_twitter\": 0,\n\"chapter_num\": 0,\n\"book_sku\": 0,\n\"is_audio_book\": 1,\n\"is_end\": 1,\n\"roof_placement\": 1,\n\"is_teaching_aids\": 0,\n\"is_free\": 0,\n\"status\": 4,\n\"works_update_time\": null,\n\"created_at\": null,\n\"updated_at\": null,\n\"is_sub\": 1,\n\"is_new\": 0\n}\n},\n{\n\"id\": 2,\n\"work_id\": 18,\n\"category_id\": 1,\n\"created_at\": null,\n\"updated_at\": null,\n\"works\": {\n\"id\": 18,\n\"user_id\": 211172,\n\"column_id\": 1,\n\"type\": 2,\n\"title\": \"如何培养高情商孩子\",\n\"subtitle\": \"\",\n\"cover_img\": \"/wechat/works/video/161910/1639_1525340866.png\",\n\"detail_img\": \"/wechat/works/video/1/2017101715260412803.jpg\",\n\"content\": \"<p>一个人能否取得成功，智商只起到20%的作用，剩下的80%取决于情商。——许多孩子的学习问题不是智商低，而是缺乏情商培养！</p>\",\n\"down_time\": null,\n\"online_time\": null,\n\"view_num\": 3770,\n\"message\": null,\n\"is_pay\": 0,\n\"original_price\": \"0.00\",\n\"price\": \"0.00\",\n\"promotion_cost\": \"0.00\",\n\"twitter_price\": \"0.00\",\n\"subscribe_num\": 0,\n\"collection_num\": 0,\n\"timing_online\": 0,\n\"timing_time\": null,\n\"can_twitter\": 0,\n\"chapter_num\": 0,\n\"book_sku\": 0,\n\"is_audio_book\": 0,\n\"is_end\": 1,\n\"roof_placement\": 1,\n\"is_teaching_aids\": 0,\n\"is_free\": 0,\n\"status\": 4,\n\"works_update_time\": null,\n\"created_at\": null,\n\"updated_at\": null,\n\"is_sub\": 0,\n\"is_new\": 0\n}\n},\n{\n\"id\": 3,\n\"work_id\": 16,\n\"category_id\": 3,\n\"created_at\": null,\n\"updated_at\": null,\n\"works\": {\n\"id\": 16,\n\"user_id\": 168934,\n\"column_id\": 1,\n\"type\": 1,\n\"title\": \"如何经营幸福婚姻\",\n\"subtitle\": \"\",\n\"cover_img\": \"/nlsg/works/20190822150244797760.png\",\n\"detail_img\": \"/nlsg/works/20191023183946478177.png\",\n\"content\": \"<p>幸福的婚姻是“同床同梦”，悲情的婚姻是“同床异梦”。两个相爱的人因为一时的爱慕之情走到一起，但在经过柴米油盐酱醋茶的考验后他们未必会幸福、未必会长久；两个不相爱的人走到一起，但在长时间的磨合之后他们未必不幸福、未必不长久。</p>\",\n\"down_time\": null,\n\"online_time\": null,\n\"view_num\": 1295460,\n\"message\": null,\n\"is_pay\": 1,\n\"original_price\": \"29.90\",\n\"price\": \"29.90\",\n\"promotion_cost\": \"0.00\",\n\"twitter_price\": \"0.00\",\n\"subscribe_num\": 287,\n\"collection_num\": 0,\n\"timing_online\": 0,\n\"timing_time\": null,\n\"can_twitter\": 0,\n\"chapter_num\": 0,\n\"book_sku\": 0,\n\"is_audio_book\": 1,\n\"is_end\": 1,\n\"roof_placement\": 1,\n\"is_teaching_aids\": 0,\n\"is_free\": 0,\n\"status\": 4,\n\"works_update_time\": null,\n\"created_at\": null,\n\"updated_at\": null,\n\"is_sub\": 1,\n\"is_new\": 0\n}\n}\n],\n\"total\": 3\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "api/v4/works/materials",
    "title": "作品素材",
    "version": "4.0.0",
    "name": "materials",
    "group": "works",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/works/materials"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1 文字 2图片</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "api/v4/send/send_edit",
    "title": "领取操作",
    "name": "send_edit",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "order_id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n    \"code\": 200,\n    \"msg\": \"成功\",\n    \"data\": [\n    ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/SendController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "api/v4/works/show",
    "title": "点播时 记录首次历史记录 阅读数自增",
    "name": "show",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_type",
            "description": "<p>1专栏   2讲座   3听书  4精品课程 5训练营</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_id",
            "description": "<p>对应id(1专栏对应id但课程  2课程id   3讲座使用对应的课程id )</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "works_info_id",
            "description": "<p>章节id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": { }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "/api/v4/works/works_category_data",
    "title": "获取分类[app首页和分类列表用]",
    "name": "works_category_data",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "is_index",
            "description": "<p>是否首页</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "type",
            "description": "<p>1课程  2 听书</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\ncode: 200,\nmsg: \"成功\",\ndata: [\n{\nid: 1,\nname: \"父母关系\",\npid: 0,\nlevel: 1,\nson: [\n{\nid: 3,\nname: \"母子亲密关系\",\npid: 1,\nlevel: 2,\nson: [ ]\n}\n]\n},\n{\nid: 2,\nname: \"亲子关系\",\npid: 0,\nlevel: 1,\nson: [ ]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "get",
    "url": "/api/v4/works/works_sub_works",
    "title": "免费课程静默订阅操作",
    "name": "works_sub_works",
    "version": "1.0.0",
    "group": "works",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "relation_id",
            "description": "<p>订阅id、</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "sub_type",
            "description": "<p>订阅对象类型  1 专栏  2作品 3直播  4会员 5线下产品  6讲座</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\ncode: 200,\nmsg: \"成功\",\ndata: []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/WorksController.php",
    "groupTitle": "works"
  },
  {
    "type": "post",
    "url": "/api/v4/meeting_sales/bind_dealer",
    "title": "添加绑定经销商",
    "version": "4.0.0",
    "name": "_api_v4_meeting_sales_bind_dealer",
    "group": "会场销售",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/bind_dealer"
      }
    ],
    "description": "<p>添加绑定经销商</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "dealer_phone",
            "description": "<p>经销商账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "dealer_name",
            "description": "<p>经销商名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "remark",
            "description": "<p>场次备注</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MeetingController.php",
    "groupTitle": "会场销售"
  },
  {
    "type": "get",
    "url": "/api/v4/meeting_sales/bind_record",
    "title": "经销商绑定记录",
    "version": "4.0.0",
    "name": "_api_v4_meeting_sales_bind_record",
    "group": "会场销售",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/bind_record"
      }
    ],
    "description": "<p>经销商绑定记录</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态(1当前生效 2已过期)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "bind_count",
            "description": "<p>绑定数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "dealer_count",
            "description": "<p>经销商数</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MeetingController.php",
    "groupTitle": "会场销售"
  },
  {
    "type": "get",
    "url": "/api/v4/meeting_sales/check_dealer",
    "title": "校验经销商电话",
    "version": "4.0.0",
    "name": "_api_v4_meeting_sales_check_dealer",
    "group": "会场销售",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/check_dealer"
      }
    ],
    "description": "<p>校验经销商电话</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>电话</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态(1当前生效 2已过期)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MeetingController.php",
    "groupTitle": "会场销售"
  },
  {
    "type": "get",
    "url": "/api/v4/meeting_sales/index",
    "title": "老师当前绑定和二维码信息",
    "version": "4.0.0",
    "name": "_api_v4_meeting_sales_index",
    "group": "会场销售",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/meeting_sales/index"
      }
    ],
    "description": "<p>老师当前绑定和二维码信息</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "bind",
            "description": "<p>当前生效的绑定经销商,如果空表示没有</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "bind.end_at",
            "description": "<p>失效时间</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/MeetingController.php",
    "groupTitle": "会场销售"
  },
  {
    "type": "get",
    "url": "/api/v4/channel/banner",
    "title": "创业天下banner",
    "version": "4.0.0",
    "name": "_api_v4_channel_banner",
    "group": "创业天下",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/channel/banner"
      }
    ],
    "description": "<p>创业天下banner</p>",
    "filename": "../app/Http/Controllers/Api/V4/ChannelController.php",
    "groupTitle": "创业天下"
  },
  {
    "type": "get",
    "url": "/api/v4/channel/click",
    "title": "点击统计",
    "version": "4.0.0",
    "name": "_api_v4_channel_click",
    "group": "创业天下",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/channel/click"
      }
    ],
    "description": "<p>点击统计</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3",
              "4"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>(1：专栏  2：商品  3：精品课 4: banner)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "wid",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "flag",
            "description": "<p>(cytx)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/ChannelController.php",
    "groupTitle": "创业天下"
  },
  {
    "type": "get",
    "url": "/api/v4/channel/cytx",
    "title": "创业天下课程列表(旧)",
    "version": "4.0.0",
    "name": "_api_v4_channel_cytx",
    "group": "创业天下",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/channel/cytx"
      }
    ],
    "description": "<p>创业天下课程列表(旧)</p>",
    "filename": "../app/Http/Controllers/Api/V4/ChannelController.php",
    "groupTitle": "创业天下"
  },
  {
    "type": "get",
    "url": "/api/v4/channel/cytx_new",
    "title": "创业天下课程列表",
    "version": "4.0.0",
    "name": "_api_v4_channel_cytx_new",
    "group": "创业天下",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/channel/cytx_new"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "0",
              "1",
              "2"
            ],
            "optional": true,
            "field": "is_buy",
            "description": "<p>是否已购(0全部 1已购 2未购)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "0",
              "1",
              "2"
            ],
            "optional": true,
            "field": "works_type",
            "description": "<p>类型(0全部 1视频 2音频)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "category_id",
            "description": "<p>分类id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "ob",
            "description": "<p>排序(view_num,created,price)</p>"
          }
        ]
      }
    },
    "description": "<p>创业天下课程列表</p>",
    "filename": "../app/Http/Controllers/Api/V4/ChannelController.php",
    "groupTitle": "创业天下"
  },
  {
    "type": "get",
    "url": "/api/v4/channel/cytx_order",
    "title": "创业天下消费记录",
    "version": "4.0.0",
    "name": "_api_v4_channel_cytx_order",
    "group": "创业天下",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/channel/cytx_order"
      }
    ],
    "description": "<p>创业天下消费记录</p>",
    "filename": "../app/Http/Controllers/Api/V4/ChannelController.php",
    "groupTitle": "创业天下"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/vip/assign",
    "title": "兑换码配额修改",
    "version": "4.0.0",
    "name": "_api_admin_v4_vip_assign",
    "group": "后台-VIP",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/vip/assign"
      }
    ],
    "description": "<p>兑换码配额修改</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "vip_id",
            "description": "<p>用户vip_id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1生效 2失效)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "edit",
              "add"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>添加或修改</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "assign_history_id",
            "description": "<p>历史记录的id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/VipController.php",
    "groupTitle": "后台-VIP"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/vip/create_vip",
    "title": "开通360或钻石",
    "version": "4.0.0",
    "name": "_api_admin_v4_vip_create_vip",
    "group": "后台-VIP",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/vip/create_vip"
      }
    ],
    "description": "<p>开通360或钻石</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "parent",
            "description": "<p>上级账号(添加360时可选)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>开通账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "send_money",
            "description": "<p>是否生成收益(添加360时可用.1生成,0不生成)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>类型(1是360 , 2是钻石)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "success_msg",
            "description": "<p>操作信息</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/VipController.php",
    "groupTitle": "后台-VIP"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/vip/list",
    "title": "列表与详情",
    "version": "4.0.0",
    "name": "_api_admin_v4_vip_list",
    "group": "后台-VIP",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/vip/list"
      }
    ],
    "description": "<p>列表与详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "0",
              "1",
              "2"
            ],
            "optional": true,
            "field": "level",
            "description": "<p>会员级别(0全部,1是360,2是经销商)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "username",
            "description": "<p>账号</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "new_level",
            "description": "<p>账号的当前会员信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "open_history",
            "description": "<p>开通记录</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "assign_count",
            "description": "<p>配额总数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "assign_history",
            "description": "<p>配额记录</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/VipController.php",
    "groupTitle": "后台-VIP"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/after_sales/list",
    "title": "售后列表和详情",
    "version": "4.0.0",
    "name": "_api_admin_v4_after_sales_list",
    "group": "后台-售后",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/after_sales/list"
      }
    ],
    "description": "<p>售后列表和详情</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "service_num",
            "description": "<p>售后订单</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user_info",
            "description": "<p>用户信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>订单来源(1安卓 2ios 3微信 )</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "pay_type",
            "description": "<p>支付渠道(1 微信端 2app微信 3app支付宝 4ios)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info_order",
            "description": "<p>售前单号信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>申请类型( 1退款 2退货)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reason_id",
            "description": "<p>申请原因(1,商品问题,2客服问题,3物流问题,4其他问题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "description",
            "description": "<p>申请描述</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "picture",
            "description": "<p>凭证</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>时间</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/AfterSalesController.php",
    "groupTitle": "后台-售后"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/after_sales/status_change",
    "title": "审核,鉴定",
    "version": "4.0.0",
    "name": "_api_admin_v4_after_sales_status_change",
    "group": "后台-售后",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/after_sales/status_change"
      }
    ],
    "description": "<p>审核,鉴定</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "check",
              "identify"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>check审核,identify鉴定</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": true,
            "field": "value",
            "description": "<p>审核时传,1通过 2拒绝</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "return_address_id",
            "description": "<p>退货地址id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/AfterSalesController.php",
    "groupTitle": "后台-售后"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/goods/add",
    "title": "添加商品",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_add",
    "group": "后台-商品管理",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/add"
      }
    ],
    "description": "<p>添加商品</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"category_id\": 1,\n\"name\": \"手机紧身的繁了\",\n\"subtitle\": \"一个还行的手机\",\n\"picture\": \"/phone/1.jpg\",\n\"freight_id\": 1,\n\"original_price\": 9999,\n\"price\": 999,\n\"keywords\": \"手机,智能,安卓\",\n\"content\": \"<p>图文简介啊发撒发撒地方</p>\",\n\"status\": 1,\n\"picture_list\": [\n{\n\"is_video\": 1,\n\"url\": \"/phone/video/1.mp4\",\n\"is_main\": 1,\n\"cover_img\": \"/phone/1mp4.jpg\"\n},\n{\n\"is_video\": 1,\n\"url\": \"/phone/video/2.mp4\",\n\"is_main\": 0,\n\"cover_img\": \"/phone/2mp4.jpg\"\n},\n{\n\"is_video\": 0,\n\"url\": \"/phone/4.jpg\",\n\"is_main\": 1\n},\n{\n\"is_video\": 0,\n\"url\": \"/phone/5.jpg\",\n\"is_main\": 0\n}\n],\n\"tos\": [\n1,\n2,\n3\n],\n\"sku_list\": [\n{\n\"picture\": \"/phone/hong.jpg\",\n\"original_price\": \"9999\",\n\"price\": \"999\",\n\"cost\": 6.6,\n\"promotion_cost\": 0,\n\"stock\": 100,\n\"warning_stock\": 10,\n\"status\": 1,\n\"weight\": 250,\n\"volume\": 100,\n\"erp_enterprise_code\": \"\",\n\"erp_goods_code\": \"\",\n\"value_list\": [\n{\n\"key_name\": \"颜色\",\n\"value_name\": \"红\"\n},\n{\n\"key_name\": \"材质\",\n\"value_name\": \"铁\"\n}\n]\n},\n{\n\"picture\": \"/phone/huang.jpg\",\n\"original_price\": \"9999\",\n\"price\": 888,\n\"cost\": 7,\n\"promotion_cost\": 0,\n\"stock\": 100,\n\"warning_stock\": 5,\n\"status\": 1,\n\"weight\": 250,\n\"volume\": 120,\n\"erp_enterprise_code\": \"\",\n\"erp_goods_code\": \"\",\n\"value_list\": [\n{\n\"key_name\": \"颜色\",\n\"value_name\": \"黄\"\n},\n{\n\"key_name\": \"材质\",\n\"value_name\": \"木头\"\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\":{}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/GoodsController.php",
    "groupTitle": "后台-商品管理"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/goods/category_list",
    "title": "商品分类列表",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_category_list",
    "group": "后台-商品管理",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/category_list"
      }
    ],
    "description": "<p>商品分类列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/GoodsController.php",
    "groupTitle": "后台-商品管理"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/goods/change_status",
    "title": "修改商品状态",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_change_status",
    "group": "后台-商品管理",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/change_status"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "off",
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>上下删</p>"
          }
        ]
      }
    },
    "description": "<p>修改商品状态</p>",
    "filename": "../app/Http/Controllers/Admin/V4/GoodsController.php",
    "groupTitle": "后台-商品管理"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/goods/change_stock",
    "title": "修改规格库存",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_change_stock",
    "group": "后台-商品管理",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/change_stock"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>数据</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "list.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "list.sku_number",
            "description": "<p>sku_number</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "list.stock",
            "description": "<p>库存(可以是0,不能是空)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"list\":[\n{\n\"goods_id\":48,\n\"sku_number\":\"1732637347\",\n\"stock\":11\n},\n{\n\"goods_id\":48,\n\"sku_number\":\"1733744984\",\n\"stock\":66\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "description": "<p>修改规格库存</p>",
    "filename": "../app/Http/Controllers/Admin/V4/GoodsController.php",
    "groupTitle": "后台-商品管理"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/goods/list",
    "title": "商品列表",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_list",
    "group": "后台-商品管理",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/list"
      }
    ],
    "description": "<p>商品列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/GoodsController.php",
    "groupTitle": "后台-商品管理"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/goods/add_robot_comment",
    "title": "添加虚拟评论",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_add_robot_comment",
    "group": "后台-商品评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/add_robot_comment"
      }
    ],
    "description": "<p>添加虚拟评论</p>",
    "parameter": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"goods_id\":474,\n\"sku_number\":\"1611238695\",\n\"list\":[\n{\n\"content\":\"好啊\",\n\"picture\":\"\"\n},\n{\n\"content\":\"好啊11\",\n\"picture\":\"\"\n},\n{\n\"content\":\"好啊11\",\n\"picture\":\"\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/MallCommentController.php",
    "groupTitle": "后台-商品评论"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/goods/add_robot_comment_for_works",
    "title": "添加课程讲座虚拟评论",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_add_robot_comment_for_works",
    "group": "后台-商品评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/add_robot_comment_for_works"
      }
    ],
    "description": "<p>添加课程讲座虚拟评论</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "2",
              "4"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(4是作品 2是讲座)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"id\":474,\n\"type\":2,\n\"list\":[\n{\n\"content\":\"好啊\"\n},\n{\n\"content\":\"好啊11\"\n},\n{\n\"content\":\"好啊11\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/MallCommentController.php",
    "groupTitle": "后台-商品评论"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/goods/comment_list",
    "title": "评论列表",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_comment_list",
    "group": "后台-商品评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/comment_list"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "is_robot",
            "description": "<p>1是虚拟评论 0不是</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "content",
            "description": "<p>评论内容</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "goods_name",
            "description": "<p>商品名称</p>"
          }
        ]
      }
    },
    "description": "<p>评论列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/MallCommentController.php",
    "groupTitle": "后台-商品评论"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/goods/comment_reply",
    "title": "回复评论",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_comment_reply",
    "group": "后台-商品评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/comment_reply"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "content",
            "description": "<p>回复评论</p>"
          }
        ]
      }
    },
    "description": "<p>评论列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/MallCommentController.php",
    "groupTitle": "后台-商品评论"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/goods/comment_status",
    "title": "评论状态变更",
    "version": "4.0.0",
    "name": "_api_admin_v4_goods_comment_status",
    "group": "后台-商品评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/goods/comment_status"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "off"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>on显示,off隐藏</p>"
          }
        ]
      }
    },
    "description": "<p>评论状态变更</p>",
    "filename": "../app/Http/Controllers/Admin/V4/MallCommentController.php",
    "groupTitle": "后台-商品评论"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/sub_helper/works_ojb_list",
    "title": "课程讲座列表",
    "version": "4.0.0",
    "name": "_api_admin_v4_sub_helper_works_ojb_list",
    "group": "后台-商品评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/sub_helper/works_ojb_list"
      }
    ],
    "description": "<p>课程讲座列表</p>",
    "parameter": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"id\":474,\n\"type\":2,\n\"list\":[\n{\n\"content\":\"好啊\"\n},\n{\n\"content\":\"好啊11\"\n},\n{\n\"content\":\"好啊11\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/SubHelperController.php",
    "groupTitle": "后台-商品评论"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_quick_reply/add",
    "title": "添加快捷回复",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_add",
    "group": "后台-快捷回复",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/add"
      }
    ],
    "description": "<p>添加快捷回复</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImQuickReply.php",
    "groupTitle": "后台-快捷回复"
  },
  {
    "type": "put",
    "url": "api/admin_v4/im_quick_reply/change_status",
    "title": "快捷回复状态修改",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_change_status",
    "group": "后台-快捷回复",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/change_status"
      }
    ],
    "description": "<p>快捷回复状态修改</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作(del:删除)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImQuickReply.php",
    "groupTitle": "后台-快捷回复"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_quick_reply/list",
    "title": "快捷回复列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_list",
    "group": "后台-快捷回复",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/list"
      }
    ],
    "description": "<p>快捷回复列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImQuickReply.php",
    "groupTitle": "后台-快捷回复"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/active/add",
    "title": "添加编辑",
    "version": "4.0.0",
    "name": "_api_admin_v4_active_add",
    "group": "后台-活动",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/active/add"
      }
    ],
    "description": "<p>添加编辑</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ActiveController.php",
    "groupTitle": "后台-活动"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/active/list",
    "title": "活动列表和详情",
    "version": "4.0.0",
    "name": "_api_admin_v4_active_list",
    "group": "后台-活动",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/active/list"
      }
    ],
    "description": "<p>活动列表和详情</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ActiveController.php",
    "groupTitle": "后台-活动"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/active/status_change",
    "title": "修改状态",
    "version": "4.0.0",
    "name": "_api_admin_v4_active_status_change",
    "group": "后台-活动",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/active/status_change"
      }
    ],
    "description": "<p>修改状态</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ActiveController.php",
    "groupTitle": "后台-活动"
  },
  {
    "type": "post",
    "url": "/api/v4/active/binding",
    "title": "添加模块和绑定商品",
    "version": "1.0.0",
    "name": "_api_v4_active_binding",
    "group": "后台-活动",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/active/binding"
      }
    ],
    "description": "<p>添加模块和绑定商品</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "data",
            "description": "<p>提交数据</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"active_id\": 4,\n\"module_list\": [\n{\n\"title\": \"板块1\",\n\"goods_list\": [1,2,3,4,5]\n},\n{\n\"title\": \"板块2\",\n\"goods_list\": [1,2,3,4,5]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\": {\n\"code\": true,\n\"msg\": \"成功\"\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ActiveController.php",
    "groupTitle": "后台-活动"
  },
  {
    "type": "get",
    "url": "api/admin_v4/task/index",
    "title": "任务列表",
    "version": "4.0.0",
    "name": "task_index",
    "group": "后台-消息任务",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/task/index"
      }
    ],
    "description": "<p>任务列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>类型</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>是否发送</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subject",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>类型</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/TaskController.php",
    "groupTitle": "后台-消息任务"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_user/friends_list",
    "title": "好友列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_user_friends_list",
    "group": "后台-用户列表与信息",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_user/friends_list"
      }
    ],
    "description": "<p>好友列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImUserController.php",
    "groupTitle": "后台-用户列表与信息"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_user/list",
    "title": "用户列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_user_list",
    "group": "后台-用户列表与信息",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_user/list"
      }
    ],
    "description": "<p>用户列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "id",
            "description": "<p>用户id,详情使用</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "-1",
              "0",
              "1",
              "2"
            ],
            "optional": true,
            "field": "sex",
            "description": "<p>性别(0位置,1男,2女,-1全部)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "0",
              "1",
              "2"
            ],
            "optional": true,
            "field": "order_type",
            "description": "<p>订单状态(0全部  1已完成  2未完成)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "created_at",
            "description": "<p>时间(1一个月内,2三个月内,其他格式(20201-01-01,2020-04-04))</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sex",
            "description": "<p>性别</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>注册时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "birthday",
            "description": "<p>生日</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "intro",
            "description": "<p>简介</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reply_num",
            "description": "<p>评论和@数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "fan_num",
            "description": "<p>粉丝人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "follow_num",
            "description": "<p>关注人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "fans_num",
            "description": "<p>新增粉丝人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "open_count",
            "description": "<p>vip开通次数</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "im_user",
            "description": "<p>im注册信息(如果空,表示没注册过im)</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "vip_user",
            "description": "<p>会员信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "vip_user.level",
            "description": "<p>级别</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "vip_user.created_at",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "vip_user.expire_time",
            "description": "<p>到期时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "statistics",
            "description": "<p>统计信息</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1627376395,\n\"data\": {\n\"list\": {\n\"current_page\": 1,\n\"data\": [\n{\n\"id\": 316307,\n\"phone\": \"13847752606\",\n\"nickname\": \"138****2606\",\n\"headimg\": \"image/202009/13f952e04c720a550193e5655534be86.jpg\",\n\"sex\": 0,\n\"created_at\": null,\n\"birthday\": null,\n\"intro\": \"\",\n\"is_staff\": 0,\n\"status\": 1,\n\"ios_balance\": \"0.00\",\n\"is_author\": 0,\n\"income_num\": 0,\n\"reply_num\": 0,\n\"fan_num\": 0,\n\"follow_num\": 0,\n\"fans_num\": 0,\n\"ref\": 0,\n\"is_test_pay\": 0,\n\"open_count\": 1,\n\"im_user\": null,\n\"vip_user\": {\n\"id\": 3779,\n\"user_id\": 316307,\n\"level\": 1,\n\"is_open_360\": 0,\n\"created_at\": \"2020-10-27 21:11:46\",\n\"expire_time\": \"2021-10-27 00:00:00\",\n\"time_begin_360\": null,\n\"time_end_360\": null\n}\n}\n],\n\"first_page_url\": \"http://127.0.0.1:8000/api/admin_v4/im_user/list?page=1\",\n\"from\": 1,\n\"last_page\": 1,\n\"last_page_url\": \"http://127.0.0.1:8000/api/admin_v4/im_user/list?page=1\",\n\"next_page_url\": null,\n\"path\": \"http://127.0.0.1:8000/api/admin_v4/im_user/list\",\n\"per_page\": 10,\n\"prev_page_url\": null,\n\"to\": 1,\n\"total\": 1\n},\n\"statistics\": {\n\"all\": 258354,\n\"man\": 16019,\n\"woman\": 32497,\n\"unknown\": 209815\n}\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImUserController.php",
    "groupTitle": "后台-用户列表与信息"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/auth/captcha",
    "title": "验证码",
    "version": "4.0.0",
    "name": "_api_admin_v4_auth_captcha",
    "group": "后台-登陆",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/auth/captcha"
      }
    ],
    "description": "<p>验证码</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "key",
            "description": "<p>验证码key</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "expire",
            "description": "<p>验证码过期时间戳,过期需刷新,验证错误需刷新</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/AuthController.php",
    "groupTitle": "后台-登陆"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/auth/login",
    "title": "登陆",
    "version": "4.0.0",
    "name": "_api_admin_v4_auth_login",
    "group": "后台-登陆",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/auth/login"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": "<p>密码</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "captcha",
            "description": "<p>验证码</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "key",
            "description": "<p>验证码key</p>"
          }
        ]
      }
    },
    "description": "<p>登陆</p>",
    "filename": "../app/Http/Controllers/Admin/V4/AuthController.php",
    "groupTitle": "后台-登陆"
  },
  {
    "type": "get",
    "url": "api/admin_v4/live/index",
    "title": "直播列表",
    "version": "4.0.0",
    "name": "live_index",
    "group": "后台-直播列表",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/live/index"
      }
    ],
    "description": "<p>直播列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_finish",
            "description": "<p>是否结束  1 是0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>直播状态 1:待审核  2:已取消 3:已驳回  4:通过</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/LiveController.php",
    "groupTitle": "后台-直播列表"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/bind_works",
    "title": "群绑定课程",
    "version": "4.0.0",
    "name": "api_admin_v4_im_group_bind_works",
    "group": "后台-社群",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/bind_works"
      }
    ],
    "description": "<p>群绑定课程</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "group_id",
            "description": "<p>群id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "works_id",
            "description": "<p>课程id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "后台-社群"
  },
  {
    "type": "put",
    "url": "api/admin_v4/im_group/change_top",
    "title": "设置或取消置顶",
    "version": "4.0.0",
    "name": "api_admin_v4_im_group_change_top",
    "group": "后台-社群",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/change_top"
      }
    ],
    "description": "<p>群列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "group_id",
            "description": "<p>群组id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "top",
              "cancel_top"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>操作</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "后台-社群"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/list",
    "title": "群列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_group_list",
    "group": "后台-社群",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/list"
      }
    ],
    "description": "<p>群列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "time_asc",
              "time_desc"
            ],
            "optional": true,
            "field": "ob",
            "description": "<p>排序</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "name",
            "description": "<p>群名</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": true,
            "field": "owner_type",
            "description": "<p>加入类型(1我创建的  2我加入的)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": true,
            "field": "group_role",
            "description": "<p>级别(1群主 2管理员 9全都是)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "0",
              "1",
              "2"
            ],
            "optional": true,
            "field": "status",
            "description": "<p>群状态</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>群id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "group_id",
            "description": "<p>腾讯群id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "owner_account",
            "description": "<p>群组用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>类型(群组类型 陌生人社交群（Public）,好友工作群（Work）,临时会议群（Meeting）,直播群（AVChatRoom）)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>群名</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态(1正常 2解散)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "owner_phone",
            "description": "<p>群组账号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "owner_id",
            "description": "<p>群组id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "owner_nickname",
            "description": "<p>群组昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "member_num",
            "description": "<p>群人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "is_top",
            "description": "<p>是否置顶(1是 0否)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "max_num",
            "description": "<p>最高人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "admin",
            "description": "<p>管理员列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "admin.phone",
            "description": "<p>管理员账号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "admin.group_account",
            "description": "<p>管理员id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "admin.group_role",
            "description": "<p>级别(1群组2管理员)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"id\": 56,\n\"group_id\": \"@TGS#2ICPIJJHB\",\n\"operator_account\": 211172,\n\"owner_account\": 211172,\n\"type\": \"Public\",\n\"name\": \"房思楠、鬼见愁、邢成\",\n\"status\": 1,\n\"created_at\": \"2021-07-22 15:31:45\",\n\"owner_phone\": \"15650701817\",\n\"owner_id\": 211172,\n\"owner_nickname\": \"房思楠\",\n\"member_num\": 5,\n\"is_top\": 1,\n\"max_num\": 2000,\n\"admin\": [\n{\n\"group_account\": \"211172\",\n\"phone\": \"15650701817\",\n\"nickname\": \"房思楠\",\n\"group_role\": 1\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "后台-社群"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_group/statistics",
    "title": "群列表统计信息",
    "version": "4.0.0",
    "name": "api_admin_v4_im_group_statistics",
    "group": "后台-社群",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_group/statistics"
      }
    ],
    "description": "<p>群列表统计信息</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImGroupController.php",
    "groupTitle": "后台-社群"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_doc/add",
    "title": "添加文案",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_add",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/add"
      }
    ],
    "description": "<p>添加文案</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(1商品 2附件 3文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type_info",
            "description": "<p>详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链  18线下课 19听书 21音频 22视频 23图片 24文件 31文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "obj_id",
            "description": "<p>目标id(当type=1时需要传)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容或名称(type=1如果是商品类型传商品的标题,外链类型传网址)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "subtitle",
            "description": "<p>副标题(外链类型传网址说明名称)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面图片(type=1必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "media_id",
            "description": "<p>媒体id(type=2时必传,如果是图片,可逗号拼接多个)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "[\n{\n\"type\": 1,\n\"type_info\": 11,\n\"obj_id\": 448,\n\"content\": \"44节科学探索课，开启孩子自然科学之门\",\n\"cover_img\": \"nlsg/authorpt/20201229114832542932.png\",\n\"subtitle\": \"浩瀚宇宙、海洋世界、恐龙时代、昆虫家族，精美视频动画展现前沿的科学知识，让孩子爱上自然科学\",\n\"status\": 1\n},\n{\n\"type\": 1,\n\"type_info\": 16,\n\"obj_id\": 517,\n\"content\": \"30天亲子训练营\",\n\"cover_img\": \"wechat/works/video/184528/8105_1527070171.png\",\n\"subtitle\": \"\",\n\"status\": 1\n},\n{\n\"type\": 2,\n\"type_info\": 21,\n\"content\": \"文件ing.mp3\",\n\"file_url\": \"https://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/f63da4f95285890780889058541/aaodecBf5FAA.mp3\",\n\"file_size\": 4426079,\n\"format\": \"mp3\",\n\"second\": 275,\n\"file_md5\": \"34131545324543\",\n\"status\": 1\n},\n{\n\"type\": 2,\n\"type_info\": 22,\n\"content\": \"视频.mp4\",\n\"file_url\": \"https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/345e2a389fe32d62fedad3d6d2150110.mp4\",\n\"file_size\": 1247117,\n\"format\": \"mp4\",\n\"second\": 7,\n\"file_md5\": \"3413154532454311\",\n\"cover_img\": \"https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/643665ba437cf198a9961f85795d8474.jpg?imageMogr2/\",\n\"img_size\": 277431,\n\"img_width\": 720,\n\"img_height\": 1600,\n\"img_format\": \"jpg\",\n\"img_md5\": \"14436454\",\n\"status\": 1\n},\n{\n\"type\": 3,\n\"type_info\": 31,\n\"content\": \"nihao\"\n}\n]",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_doc/category",
    "title": "分类",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_category",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/category"
      }
    ],
    "description": "<p>分类的列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id 0为全部</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_doc/category/product",
    "title": "分类筛选的商品列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_category_product",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/category/product"
      }
    ],
    "description": "<p>分类筛选的商品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id 0为全部</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360 7线下课</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "put",
    "url": "api/admin_v4/im_doc/change_job_status",
    "title": "发送任务状态修改",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_change_job_status",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/change_job_status"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>任务id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "off",
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作</p>"
          }
        ]
      }
    },
    "description": "<p>发送任务状态修改</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "put",
    "url": "api/admin_v4/im_doc/change_status",
    "title": "文案状态修改",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_change_status",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/change_status"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作(del:删除)</p>"
          }
        ]
      }
    },
    "description": "<p>文案状态修改</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_doc/group_list",
    "title": "群列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_group_list",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/group_list"
      }
    ],
    "description": "<p>群列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_doc/job_add",
    "title": "添加发送任务",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_job_add",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/job_add"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "doc_id",
            "description": "<p>文案id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "send_type",
            "description": "<p>发送时间类型(1立刻 2定时)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "send_at",
            "description": "<p>定时时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "info",
            "description": "<p>对象列表</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "info.send_obj_type",
            "description": "<p>目标对象类型(1群组 2个人 3标签)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "info.send_obj_id",
            "description": "<p>目标id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"doc_id\": 1,\n\"send_type\": 1,\n\"send_at\": \"\",\n\"info\": [\n{\n\"type\": 1,\n\"list\": [\n1,\n2,\n3\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "description": "<p>添加发送任务</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_doc/job_list",
    "title": "发送任务列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_job_list",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/job_list"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "send_obj_type",
            "description": "<p>发送目标类型(1群组 2个人 3标签)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "send_obj_id",
            "description": "<p>发送目标id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": true,
            "field": "doc_type",
            "description": "<p>文案类型(1商品 2附件 3文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "doc_type_info",
            "description": "<p>文案类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营21音频 22视频 23图片 31文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "0",
              "1",
              "2",
              "3",
              "4"
            ],
            "optional": true,
            "field": "is_done",
            "description": "<p>发送结果(1待发送  2发送中 3已完成 4无任务)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_doc/list",
    "title": "文案列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_list",
    "group": "后台-社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc/list"
      }
    ],
    "description": "<p>文案列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImDocController.php",
    "groupTitle": "后台-社群文案"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_doc_folder/add",
    "title": "添加文件夹",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_folder_add",
    "group": "后台-社群文案v2",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/add"
      }
    ],
    "description": "<p>添加文件夹</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "folder_name",
            "description": "<p>文件夹名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "pid",
            "description": "<p>上级文件夹id,顶级0</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocFolderController.php",
    "groupTitle": "后台-社群文案v2"
  },
  {
    "type": "post",
    "url": "api/admin_v4/im_doc_folder/add_doc",
    "title": "添加文案",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_folder_add_doc",
    "group": "后台-社群文案v2",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/add_doc"
      }
    ],
    "description": "<p>添加文案</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "folder_id",
            "description": "<p>文件夹id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(1商品 2附件 3文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type_info",
            "description": "<p>详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链  18线下课 19听书 21音频 22视频 23图片 24文件 31文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "obj_id",
            "description": "<p>目标id(当type=1时需要传)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容或名称(type=1如果是商品类型传商品的标题,外链类型传网址)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "subtitle",
            "description": "<p>副标题(外链类型传网址说明名称)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面图片(type=1必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "media_id",
            "description": "<p>媒体id(type=2时必传,如果是图片,可逗号拼接多个)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocFolderController.php",
    "groupTitle": "后台-社群文案v2"
  },
  {
    "type": "put",
    "url": "api/admin_v4/im_doc_folder/change_doc_status",
    "title": "修改文案状态(删除,移动)",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_folder_change_doc_status",
    "group": "后台-社群文案v2",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/change_doc_status"
      }
    ],
    "description": "<p>修改文案状态(删除,移动)</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>文案</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del",
              "remove"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作(删除,移动)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "folder_id",
            "description": "<p>文案所属文件夹id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "pid",
            "description": "<p>目标id (remove时需要,pid为目标文件夹id)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocFolderController.php",
    "groupTitle": "后台-社群文案v2"
  },
  {
    "type": "put",
    "url": "api/admin_v4/im_doc_folder/change_status",
    "title": "修改文件夹状态(删除,移动,复制)",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_folder_change_status",
    "group": "后台-社群文案v2",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/change_status"
      }
    ],
    "description": "<p>修改文件夹状态(删除,移动,复制)</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>文件夹id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del",
              "remove",
              "copy"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作(删除,移动,复制)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "pid",
            "description": "<p>目标id (remove时需要,pid为目标文件夹id)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/ImDocFolderController.php",
    "groupTitle": "后台-社群文案v2"
  },
  {
    "type": "get",
    "url": "api/admin_v4/im_doc_folder/list",
    "title": "文件夹列表",
    "version": "4.0.0",
    "name": "api_admin_v4_im_doc_folder_list",
    "group": "后台-社群文案v2",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/im_doc_folder/list"
      }
    ],
    "description": "<p>文件夹列表</p>",
    "filename": "../app/Http/Controllers/Admin/V4/ImDocFolderController.php",
    "groupTitle": "后台-社群文案v2"
  },
  {
    "type": "get",
    "url": "api/admin_v4/order/detail",
    "title": "精品课-订单详情",
    "version": "4.0.0",
    "name": "order_detial",
    "group": "后台-虚拟订单",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/order/detail"
      }
    ],
    "description": "<p>精品课-订单详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>1 安卓 2ios 3微信</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "pay_price",
            "description": "<p>支付价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>支付时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>下单用户信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>精品课相关</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.user",
            "description": "<p>精品课作者</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/OrderController.php",
    "groupTitle": "后台-虚拟订单"
  },
  {
    "type": "get",
    "url": "api/admin_v4/order/lecture",
    "title": "讲座订单",
    "version": "4.0.0",
    "name": "order_lecture",
    "group": "后台-虚拟订单",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/order/lecture"
      }
    ],
    "description": "<p>讲座订单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>0 待支付  1已支付</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>订单来源</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pay_type",
            "description": "<p>支付方式</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "level",
            "description": "<p>推者类型</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/OrderController.php",
    "groupTitle": "后台-虚拟订单"
  },
  {
    "type": "get",
    "url": "api/admin_v4/order/reward",
    "title": "打赏订单",
    "version": "4.0.0",
    "name": "order_lecture",
    "group": "后台-虚拟订单",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/order/reward"
      }
    ],
    "description": "<p>打赏订单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>0 待支付  1已支付</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>订单来源</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pay_type",
            "description": "<p>支付方式</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "reward_type",
            "description": "<p>打赏类型  1专栏  2课程  3想法 4 百科  5直播礼物</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "level",
            "description": "<p>推者类型</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/OrderController.php",
    "groupTitle": "后台-虚拟订单"
  },
  {
    "type": "get",
    "url": "api/admin_v4/order/list",
    "title": "精品课订单",
    "version": "4.0.0",
    "name": "order_list",
    "group": "后台-虚拟订单",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/order/list"
      }
    ],
    "description": "<p>精品课订单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>0 待支付  1已支付</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>订单来源</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pay_type",
            "description": "<p>支付方式</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "level",
            "description": "<p>推者类型</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "rank",
            "description": "<p>排行列表</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/OrderController.php",
    "groupTitle": "后台-虚拟订单"
  },
  {
    "type": "get",
    "url": "api/admin_v4/order/statistic",
    "title": "订单统计",
    "version": "4.0.0",
    "name": "order_statistic",
    "group": "后台-虚拟订单",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/order/statistic"
      }
    ],
    "description": "<p>订单统计</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1 专栏 2 会员  3充值  4财务打款 5 打赏 6分享赚钱 7支付宝提现 8微信提现  9精品课  10直播回放 12直播预约   13能量币  14 线下产品(门票类)  16新vip</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_num",
            "description": "<p>总订单数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_price",
            "description": "<p>总订单金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "today_num",
            "description": "<p>今日订单数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "totday_price",
            "description": "<p>今日订单金额</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/OrderController.php",
    "groupTitle": "后台-虚拟订单"
  },
  {
    "type": "get",
    "url": "api/admin_v4/order/user",
    "title": "会员订单",
    "version": "4.0.0",
    "name": "order_user",
    "group": "后台-虚拟订单",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/order/user"
      }
    ],
    "description": "<p>会员订单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>0 待支付  1已支付</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>订单来源</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pay_type",
            "description": "<p>支付方式</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "vip_order_type",
            "description": "<p>1开通 2续费 3升级</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "level",
            "description": "<p>1 早期366老会员 2 推客 3黑钻 4皇钻 5代理</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/OrderController.php",
    "groupTitle": "后台-虚拟订单"
  },
  {
    "type": "get",
    "url": "api/admin_v4/order/vip",
    "title": "360订单",
    "version": "4.0.0",
    "name": "order_vip",
    "group": "后台-虚拟订单",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/order/vip"
      }
    ],
    "description": "<p>360订单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>0 待支付  1已支付</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>订单来源</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pay_type",
            "description": "<p>支付方式</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "level",
            "description": "<p>推者类型</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/OrderController.php",
    "groupTitle": "后台-虚拟订单"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/add-camp",
    "title": "创建训练营",
    "version": "4.0.0",
    "name": "add_camp",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-camp"
      }
    ],
    "description": "<p>创建训练营</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>训练营名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "index_pic",
            "description": "<p>训练营首页图</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_pic",
            "description": "<p>封面图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "details_pic",
            "description": "<p>详情图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "message",
            "description": "<p>推荐语</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>作者</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>定价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "is_start",
            "description": "<p>是否开营</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "show_info_num",
            "description": "<p>章节数量</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/wiki/add",
    "title": "创建/编辑百科",
    "version": "4.0.0",
    "name": "add_column",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/wiki/add"
      }
    ],
    "description": "<p>创建专栏</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "intro",
            "description": "<p>简介</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "detail_img",
            "description": "<p>详情图片</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态 1上架  2下架</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/add-column",
    "title": "创建专栏",
    "version": "4.0.0",
    "name": "add_column",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-column"
      }
    ],
    "description": "<p>创建专栏</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>专栏名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_pic",
            "description": "<p>封面图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "details_pic",
            "description": "<p>详情图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "message",
            "description": "<p>推荐语</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>作者</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>定价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/add-lecture",
    "title": "创建讲座",
    "version": "4.0.0",
    "name": "add_lecture",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-lecture"
      }
    ],
    "description": "<p>创建讲座</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>专栏名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_pic",
            "description": "<p>封面图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "details_pic",
            "description": "<p>详情图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "message",
            "description": "<p>推荐语</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>作者</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>定价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/add-listen",
    "title": "创建/编辑听书",
    "version": "4.0.0",
    "name": "add_listen",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-listen"
      }
    ],
    "description": "<p>创建/编辑听书</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>听书id  id存在为编辑</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>作者</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>定价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "is_end",
            "description": "<p>是否完结</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "timing_online",
            "description": "<p>是否自动上架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>上架状态</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>简介</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "message",
            "description": "<p>推荐语</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/add-works",
    "title": "创建精品课",
    "version": "4.0.0",
    "name": "add_works",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-works"
      }
    ],
    "description": "<p>创建精品课</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>作者</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>定价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "is_end",
            "description": "<p>是否完结</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "timing_online",
            "description": "<p>是否自动上架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>上架状态</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1 视频 2音频 3 文章</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/add-works-chapter",
    "title": "增加/编辑章节",
    "version": "4.0.0",
    "name": "add_works_chapter",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/add-works-chapter"
      }
    ],
    "description": "<p>增加/编辑章节</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>章节id  存在为编辑</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "section",
            "description": "<p>第几节</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "introduce",
            "description": "<p>简介</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>音视频url</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态   0 删除 1 未审核 2 拒绝  3通过 4上架 5下架</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "video_id",
            "description": "<p>视频id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "free_trial",
            "description": "<p>是否免费 0 否 1 是</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "timing_online",
            "description": "<p>是否自动上线 0 否 1是</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "timing_time",
            "description": "<p>自动上线时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "share_img",
            "description": "<p>章节图片</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/banner/add",
    "title": "创建广告",
    "version": "4.0.0",
    "name": "banner_add",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/banner/add"
      }
    ],
    "description": "<p>创建广告</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>广告id(编辑操作)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pic",
            "description": "<p>图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>h5地址</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "rank",
            "description": "<p>排序</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>跳转类型</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "obj_id",
            "description": "<p>跳转id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/BannerController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/banner/edit",
    "title": "广告编辑",
    "version": "4.0.0",
    "name": "banner_edit",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/banner/edit"
      }
    ],
    "description": "<p>广告编辑</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pic",
            "description": "<p>图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>h5地址</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "rank",
            "description": "<p>排序</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>位置</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "jump_type",
            "description": "<p>跳转类型</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "obj_id",
            "description": "<p>跳转id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"dat\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/BannerController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/camp",
    "title": "训练营列表",
    "version": "4.0.0",
    "name": "camp",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/camp"
      }
    ],
    "description": "<p>训练营列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>上下架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>专栏名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>作者相关</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info_num",
            "description": "<p>作品数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/chapter/delete",
    "title": "删除章节",
    "version": "4.0.0",
    "name": "chapter_delete",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/chapter/delete"
      }
    ],
    "description": "<p>删除章节</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>章节id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/column",
    "title": "专栏列表",
    "version": "4.0.0",
    "name": "column",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/column"
      }
    ],
    "description": "<p>专栏列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>上下架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>专栏名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>作者相关</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info_num",
            "description": "<p>作品数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/column/delete",
    "title": "删除专栏/讲座",
    "version": "4.0.0",
    "name": "column_delete",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/column/delete"
      }
    ],
    "description": "<p>删除专栏/讲座</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>专栏id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/get-camp-list",
    "title": "训练营详情",
    "version": "4.0.0",
    "name": "get_camp_list",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-camp-list"
      }
    ],
    "description": "<p>训练营详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "camp_id",
            "description": "<p>训练营id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>作者相关</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>定价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>上架状态</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "message",
            "description": "<p>推荐语</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "index_pic",
            "description": "<p>首页图</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "is_start",
            "description": "<p>是否开营</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "show_info_num",
            "description": "<p>章节数量</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/get-chapter-info",
    "title": "章节详情",
    "version": "4.0.0",
    "name": "get_chapter_info",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-chapter-info"
      }
    ],
    "description": "<p>章节详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>章节id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1 视频 2音频 3 文章</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "rank",
            "description": "<p>排序</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "section",
            "description": "<p>小节</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "introduce",
            "description": "<p>简介</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "url",
            "description": "<p>视频  音频 地址url</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "timing_time",
            "description": "<p>自动上架时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "video_id",
            "description": "<p>视频id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "free_trial",
            "description": "<p>是否免费</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "timing_online",
            "description": "<p>是否自动上架  1自动 0手动</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/class/get-column-list",
    "title": "专栏详情",
    "version": "4.0.0",
    "name": "get_column_list",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-column-list"
      }
    ],
    "description": "<p>专栏详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>作者相关</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>定价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>上架状态</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "message",
            "description": "<p>推荐语</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/get-column-work-list",
    "title": "专栏作品列表",
    "version": "4.0.0",
    "name": "get_column_work_list",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-column-work-list"
      }
    ],
    "description": "<p>专栏作品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>专栏id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>1 视频 2音频 3 文章</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "view_num",
            "description": "<p>浏览数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "obj_id",
            "description": "<p>跳转id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>上架时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/get-lecture-work-list",
    "title": "专栏作品信息",
    "version": "4.0.0",
    "name": "get_lecture_work_list",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-lecture-work-list"
      }
    ],
    "description": "<p>专栏作品信息</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>专栏id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>1 视频 2音频 3 文章</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "view_num",
            "description": "<p>浏览数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "obj_id",
            "description": "<p>跳转id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>上架时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/get-work-chapter-list",
    "title": "作品章节列表",
    "version": "4.0.0",
    "name": "get_work_chapter_list",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-work-chapter-list"
      }
    ],
    "description": "<p>作品章节列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "work_id",
            "description": "<p>作品id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "rank",
            "description": "<p>排序</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "view_num",
            "description": "<p>观看量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "size",
            "description": "<p>文件大小</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "free_trial",
            "description": "<p>是否免费</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "timing_time",
            "description": "<p>自动上架时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/get-work-list",
    "title": "作品详情",
    "version": "4.0.0",
    "name": "get_work_list",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/get-work-list"
      }
    ],
    "description": "<p>作品详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "detail_img",
            "description": "<p>详细图片</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>作者id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>售价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "original_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>上架状态</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "is_end",
            "description": "<p>是否完结</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "view_num",
            "description": "<p>浏览数</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"dat\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/lecture",
    "title": "座列表",
    "version": "4.0.0",
    "name": "lecture",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/lecture"
      }
    ],
    "description": "<p>讲座列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>上下架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>专栏名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>作者相关</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info_num",
            "description": "<p>作品数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/banner/list",
    "title": "广告列表",
    "version": "4.0.0",
    "name": "list",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/banner/list"
      }
    ],
    "description": "<p>专栏列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<ol> <li>首页   (50段商城预留)51.商城首页轮播  52.分类下方推荐位  53.爆款推荐</li> </ol>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "rank",
            "description": "<p>排序</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "jump_type",
            "description": "<p>跳转类型 1:h5(走url,其他都object_id)  2:商品  3:优惠券领取页面4精品课 5.讲座 6.听书 7 360</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "url",
            "description": "<p>h5链接</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "obj_id",
            "description": "<p>跳转id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/BannerController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/listen",
    "title": "听书",
    "version": "4.0.0",
    "name": "listen",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/listen"
      }
    ],
    "description": "<p>听书</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "work_id",
            "description": "<p>编号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>上下架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "category",
            "description": "<p>分类</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "user",
            "description": "<p>作者</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "chapter_num",
            "description": "<p>章节数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_end",
            "description": "<p>是否完结</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>0 删除 1 待审核 2 拒绝  3通过 4上架 5下架</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/operate/chapter",
    "title": "操作章节",
    "version": "4.0.0",
    "name": "operate_chapter",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/operate/chapter"
      }
    ],
    "description": "<p>操作章节</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>章节id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1 上线 2 下线 3 免费 4 不免费</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/search/category",
    "title": "作品分类",
    "version": "4.0.0",
    "name": "search_category",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/search/category"
      }
    ],
    "description": "<p>作品分类</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/wiki",
    "title": "百科列表",
    "version": "4.0.0",
    "name": "wiki",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/wiki"
      }
    ],
    "description": "<p>百科列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>上下架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>作者相关</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info_num",
            "description": "<p>作品数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/wiki/category",
    "title": "百科分类",
    "version": "4.0.0",
    "name": "wiki_category",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/wiki/category"
      }
    ],
    "description": "<p>百科分类</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "api/admin_v4/class/works",
    "title": "作品列表",
    "version": "4.0.0",
    "name": "works",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/class/works"
      }
    ],
    "description": "<p>作品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "work_id",
            "description": "<p>编号</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "is_pay",
            "description": "<p>是否精品课 1 是 0 否</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>上下架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "author",
            "description": "<p>作者名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型 1 视频 2音频 3 文章</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "category",
            "description": "<p>分类</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "user",
            "description": "<p>作者</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "chapter_num",
            "description": "<p>章节数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_end",
            "description": "<p>是否完结</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_pay",
            "description": "<p>是否精品课 1 是 0 否</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>0 删除 1 待审核 2 拒绝  3通过 4上架 5下架</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/works/works_category_data",
    "title": "作品分类",
    "name": "works_category_data",
    "version": "1.0.0",
    "group": "后台-虚拟课程",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "result",
            "description": "<p>json</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\ncode: 200,\nmsg: \"成功\",\ndata: [\n{\nid: 1,\nname: \"父母关系\",\npid: 0,\nlevel: 1,\nson: [\n{\nid: 3,\nname: \"母子亲密关系\",\npid: 1,\nlevel: 2,\nson: [ ]\n}\n]\n},\n{\nid: 2,\nname: \"亲子关系\",\npid: 0,\nlevel: 1,\nson: [ ]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "post",
    "url": "api/admin_v4/works/delete",
    "title": "删除听书/讲座",
    "version": "4.0.0",
    "name": "works_delete",
    "group": "后台-虚拟课程",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/works/delete"
      }
    ],
    "description": "<p>删除听书/讲座</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>作品id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ClassController.php",
    "groupTitle": "后台-虚拟课程"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/admin_user/list",
    "title": "后台用户 列表",
    "version": "1.0.0",
    "name": "_api_admin_v4_admin_user_list",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/admin_user/list"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "role_id",
            "description": "<p>角色id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>用户账号</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "role_info",
            "description": "<p>用户角色</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "role_info.name",
            "description": "<p>角色名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live_role_bind",
            "description": "<p>绑定的手机号</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/admin_user/list_status",
    "title": "后台用户 修改密码和角色",
    "version": "1.0.0",
    "name": "_api_admin_v4_admin_user_list_status",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/admin_user/list_status"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "role",
              "pwd",
              "live_role"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作(角色或密码,直播角色)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "role_id",
            "description": "<p>角色id(修改角色时候需要)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "21",
              "23"
            ],
            "optional": false,
            "field": "live_role_id",
            "description": "<p>直播角色id(修改直播角色时候需要,21老师23校长)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "pwd",
            "description": "<p>密码(修改密码是需要)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "re_pwd",
            "description": "<p>确认密码(修改密码是需要)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "bind_phone",
            "description": "<p>绑定手机号(修改直播角色时候需要,可以是多条)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/role/create",
    "title": "角色添加修改",
    "version": "1.0.0",
    "name": "_api_admin_v4_role_create",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/role/create"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "id",
            "description": "<p>角色id,编辑时候传</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>角色名称</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/role/node_list",
    "title": "菜单和接口列表",
    "version": "1.0.0",
    "name": "_api_admin_v4_role_node_list",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/role/node_list"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "role_id",
            "description": "<p>查看角色绑定了那些,传这个</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "pid",
            "description": "<p>父级id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "name",
            "description": "<p>目录或接口名称</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "path",
            "description": "<p>目录或接口地址</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_menu",
            "description": "<p>1是api  2是目录</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "checked",
            "description": "<p>当传role_id的时候,该值=1表示已选择该权限</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "menu",
            "description": "<p>该目录的子目录</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "api",
            "description": "<p>该目录下属接口</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1619406898,\n\"data\": [\n{\n\"id\": 1,\n\"pid\": 0,\n\"name\": \"首页\",\n\"path\": \"#\",\n\"is_menu\": 2,\n\"status\": 1,\n\"menu\": [],\n\"api\": []\n},\n{\n\"id\": 2,\n\"pid\": 0,\n\"name\": \"内容管理\",\n\"path\": \"#\",\n\"is_menu\": 2,\n\"status\": 1,\n\"menu\": [\n{\n\"id\": 3,\n\"pid\": 2,\n\"name\": \"专栏\",\n\"path\": \"class/column\",\n\"is_menu\": 2,\n\"status\": 1,\n\"menu\": [],\n\"api\": []\n},\n{\n\"id\": 4,\n\"pid\": 2,\n\"name\": \"讲座\",\n\"path\": \"class/lecture\",\n\"is_menu\": 2,\n\"status\": 1,\n\"menu\": [],\n\"api\": [\n{\n\"id\": 5,\n\"pid\": 4,\n\"name\": \"讲座列表接口\",\n\"path\": \"class/list\",\n\"is_menu\": 1,\n\"status\": 1,\n\"menu\": [],\n\"api\": []\n},\n{\n\"id\": 6,\n\"pid\": 4,\n\"name\": \"讲座详情\",\n\"path\": \"class/info\",\n\"is_menu\": 1,\n\"status\": 1,\n\"menu\": [],\n\"api\": []\n},\n{\n\"id\": 7,\n\"pid\": 4,\n\"name\": \"讲座删除\",\n\"path\": \"class/del\",\n\"is_menu\": 1,\n\"status\": 1,\n\"menu\": [],\n\"api\": []\n}\n]\n}\n],\n\"api\": []\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/role/node_list_create",
    "title": "添加修改接口或菜单",
    "version": "1.0.0",
    "name": "_api_admin_v4_role_node_list_create",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/role/node_list_create"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>编辑的时候使用</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>菜单或者接口名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "path",
            "description": "<p>路径或地址</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "pid",
            "description": "<p>父类id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "is_menu",
            "description": "<p>类型(1是接口 2是菜单)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/role/node_list_status",
    "title": "菜单和接口 删除和排序",
    "version": "1.0.0",
    "name": "_api_admin_v4_role_node_list_status",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/role/node_list_status"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>编辑的时候使用</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del",
              "rank"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作,删除或排序</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "rank",
            "description": "<p>排序时传,1-99之间</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/role/role_list",
    "title": "角色 列表",
    "version": "1.0.0",
    "name": "_api_admin_v4_role_role_list",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/role/role_list"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "role",
            "description": "<p>下属角色</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/role/role_node_bind",
    "title": "角色和菜单接口的绑定",
    "version": "1.0.0",
    "name": "_api_admin_v4_role_role_node_bind",
    "group": "后台-角色权限配置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/role/role_node_bind"
      }
    ],
    "description": "<p>角色权限配置</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "role_id",
            "description": "<p>角色id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "node_id",
            "description": "<p>模块的id,数组列表均可 1,2,3,4</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/RoleController.php",
    "groupTitle": "后台-角色权限配置"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/mall_order/list",
    "title": "订单列表和详情",
    "version": "4.0.0",
    "name": "_api_admin_v4_mall_order_list",
    "group": "后台-订单管理",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/mall_order/list"
      }
    ],
    "description": "<p>可申请售后订单和商品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "0",
              "1"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>0列表,1详情</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>页数,默认1</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>条数,默认10</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "created_at",
            "description": "<p>订单时间范围(2020-01-01,2022-02-02)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "pay_time",
            "description": "<p>支付时间范围</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "pay_type",
            "description": "<p>支付渠道(1微信端 2app微信 3app支付宝 4ios)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "os_type",
            "description": "<p>客户端(客户端:1安卓 2ios 3微信 )</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "goods_name",
            "description": "<p>品名</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "status",
            "description": "<p>状态(参考前端订单接口文档)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "'normal'",
              "'flash_sale'",
              "'group_buy'"
            ],
            "optional": false,
            "field": "order_type",
            "description": "<p>订单类型:普通,秒杀,团购</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\":{}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/MallOrderController.php",
    "groupTitle": "后台-订单管理"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/mall_order/send",
    "title": "发货",
    "version": "4.0.0",
    "name": "_api_admin_v4_mall_order_send",
    "group": "后台-订单管理",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/mall_order/send"
      }
    ],
    "description": "<p>发货</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "strint",
            "optional": false,
            "field": "express_id",
            "description": "<p>快递公司id</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": false,
            "field": "num",
            "description": "<p>快递单号</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": false,
            "field": "order_id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": false,
            "field": "order_detail_id",
            "description": "<p>订单详情id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "[\n{\n\"express_id\": 2,\n\"num\": \"YT4538526006366\",\n\"order_id\": 9526,\n\"order_detail_id\": 10323\n},\n{\n\"express_id\": 2,\n\"num\": \"YT4506367161457\",\n\"order_id\": 9526,\n\"order_detail_id\": 10324\n}\n]",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"data\":{}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/MallOrderController.php",
    "groupTitle": "后台-订单管理"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/config/edit_mall_keywords",
    "title": "修改商城搜索词热词",
    "version": "4.0.0",
    "name": "_api_admin_v4_config_edit_mall_keywords",
    "group": "后台-设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/config/edit_mall_keywords"
      }
    ],
    "description": "<p>修改商城搜索词热词</p>",
    "parameter": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"keywords\": [\n\"王琨\",\n\"教育\",\n\"育儿\",\n\"孩子\",\n\"玩具\",\n\"夫妻关系\",\n\"成长\",\n\"教具\",\n\"亲子\",\n\"心理学\"\n],\n\"hot_words\": [\n{\n\"on_fire\": 1,\n\"val\": \"王琨\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ConfigController.php",
    "groupTitle": "后台-设置"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/config/mall_keywords",
    "title": "商城搜索词热词列表",
    "version": "4.0.0",
    "name": "_api_admin_v4_config_mall_keywords",
    "group": "后台-设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/config/mall_keywords"
      }
    ],
    "description": "<p>商城搜索词热词列表</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"keywords\": [\n\"王琨\",\n\"教育\",\n\"育儿\",\n\"孩子\",\n\"玩具\",\n\"夫妻关系\",\n\"成长\",\n\"教具\",\n\"亲子\",\n\"心理学\"\n],\n\"hot_words\": [\n{\n\"on_fire\": 1,\n\"val\": \"王琨\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/ConfigController.php",
    "groupTitle": "后台-设置"
  },
  {
    "type": "post",
    "url": "api/admin_v4/comment/forbid",
    "title": "删除想法",
    "version": "4.0.0",
    "name": "comment",
    "group": "后台-评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/comment/forbid 删除想法"
      }
    ],
    "description": "<p>删除想法</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/CommentController.php",
    "groupTitle": "后台-评论"
  },
  {
    "type": "post",
    "url": "api/admin_v4/comment/reply",
    "title": "评论想法",
    "version": "4.0.0",
    "name": "comment",
    "group": "后台-评论",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/comment/reply"
      }
    ],
    "description": "<p>评论想法</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/CommentController.php",
    "groupTitle": "后台-评论"
  },
  {
    "type": "post",
    "url": "api/admin_v4/redeem_code/create",
    "title": "创建兑换码",
    "version": "4.0.0",
    "name": "redeem_code_create",
    "group": "后台-课程兑换码",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/redeem_code/create"
      }
    ],
    "description": "<p>生成课程讲座兑换码</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "2",
              "3"
            ],
            "optional": false,
            "field": "redeem_type",
            "description": "<p>兑换类型2是课程3是讲座</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>目标id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "number",
            "description": "<p>生成数量,一次最多1000</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/RedeemCodeController.php",
    "groupTitle": "后台-课程兑换码"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/freight/add_shop",
    "title": "添加退货和自提地址",
    "version": "1.0.0",
    "name": "_api_admin_v4_freight_add_shop",
    "group": "后台-运费模板",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/freight/add_shop"
      }
    ],
    "description": "<p>添加退货和自提地址</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "2",
              "3"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(2自提3退货)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1上架2下架)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "admin_name",
            "description": "<p>联系人</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "admin_phone",
            "description": "<p>联系电话</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "details",
            "description": "<p>详细地址</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "province",
            "description": "<p>省区划码</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "city",
            "description": "<p>市</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "area",
            "description": "<p>区/县</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "\n{\n\"type\": 2,\n\"name\": \"台铭自提点\",\n\"admin_name\": \"库锁\",\n\"admin_phone\": 1232456,\n\"details\": \"台铭国际企业花园\",\n\"status\": 1,\n\"province\": 110000,\n\"city\": 110105,\n\"area\": 0\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1598335384,\n\"data\": {\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/FreightController.php",
    "groupTitle": "后台-运费模板"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/freight/list",
    "title": "运费模板",
    "version": "1.0.0",
    "name": "_api_admin_v4_freight_list",
    "group": "后台-运费模板",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/freight/list"
      }
    ],
    "description": "<p>运费模板</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "nane",
            "description": "<p>名称</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1598335384,\n\"data\": {\n\"current_page\": 1,\n\"data\": [\n{\n\"id\": 14,\n\"type\": 1,\n\"name\": \"北京发货3-件数\"\n}\n],\n\"first_page_url\": \"http://127.0.0.1:8000/api/admin_v4/freight/list?page=1\",\n\"from\": 1,\n\"last_page\": 1,\n\"last_page_url\": \"http://127.0.0.1:8000/api/admin_v4/freight/list?page=1\",\n\"next_page_url\": null,\n\"path\": \"http://127.0.0.1:8000/api/admin_v4/freight/list\",\n\"per_page\": 10,\n\"prev_page_url\": null,\n\"to\": 3,\n\"total\": 3\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/FreightController.php",
    "groupTitle": "后台-运费模板"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/freight/shop_list",
    "title": "退货和自提地址",
    "version": "1.0.0",
    "name": "_api_admin_v4_freight_shop_list",
    "group": "后台-运费模板",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/freight/shop_list"
      }
    ],
    "description": "<p>退货和自提地址</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "2",
              "3"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(2:自提点 3:退货点)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "nane",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "admin_name",
            "description": "<p>管理员</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "admin_phone",
            "description": "<p>管理员电话</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1598335514,\n\"data\": {\n\"current_page\": 1,\n\"data\": [\n{\n\"id\": 9,\n\"type\": 2,\n\"name\": \"自提点2\",\n\"admin_name\": \"李四\",\n\"admin_phone\": \"112331\",\n\"phone\": \"112331\",\n\"province\": 110000,\n\"city\": 110105,\n\"area\": 0,\n\"details\": \"朝阳路85号\",\n\"start_time\": \"2020-06-15 17:50:54\",\n\"end_time\": \"2037-01-01 00:00:00\",\n\"province_name\": \"北京\",\n\"city_name\": \"朝阳\",\n\"area_name\": \"\"\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/FreightController.php",
    "groupTitle": "后台-运费模板"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/get-goods",
    "title": "选择商品",
    "version": "4.0.0",
    "name": "add_goods",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-goods"
      }
    ],
    "description": "<p>选择商品</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/add-lists",
    "title": "增加/更新推荐书单",
    "version": "4.0.0",
    "name": "add_lists",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-lists"
      }
    ],
    "description": "<p>增加/编辑推荐书单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>1上架  2下架</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "details_pic",
            "description": "<p>详情图</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>位置</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/add-listwork",
    "title": "增加/更新推荐作品",
    "version": "4.0.0",
    "name": "add_listwork",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-listwork"
      }
    ],
    "description": "<p>增加/编辑推荐课程</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "lists_id",
            "description": "<p>书单id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>位置</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "state",
            "description": "<p>状态 1 上架 2下架</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/add-lives",
    "title": "增加/更新推荐直播",
    "version": "4.0.0",
    "name": "add_lives",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-lives"
      }
    ],
    "description": "<p>增加/编辑推荐直播</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/add-wiki",
    "title": "增加/更新推荐百科",
    "version": "4.0.0",
    "name": "add_wiki",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-wiki"
      }
    ],
    "description": "<p>增加/编辑推荐百科</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "wiki_id",
            "description": "<p>百科id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>位置</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/add-works",
    "title": "增加/更新推荐课程",
    "version": "4.0.0",
    "name": "add_works",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-works"
      }
    ],
    "description": "<p>增加/编辑推荐课程</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "work_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>位置</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/edit-list-work",
    "title": "编辑书单作品",
    "version": "4.0.0",
    "name": "edit_list_work",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/edit-list-work"
      }
    ],
    "description": "<p>编辑书单作品</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>作品id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "lists_id",
            "description": "<p>书单id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works_id",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>排序</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "state",
            "description": "<p>状态</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/edit-lists",
    "title": "编辑推荐书单",
    "version": "4.0.0",
    "name": "edit_lists",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/edit-lists"
      }
    ],
    "description": "<p>编辑推荐百科</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>书单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/edit-works",
    "title": "编辑推荐课程",
    "version": "4.0.0",
    "name": "edit_works",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/edit-works"
      }
    ],
    "description": "<p>编辑推荐百科</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>推荐id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "relation_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>位置</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/admin_v4/index/get-lives",
    "title": "选择直播",
    "version": "4.0.0",
    "name": "get_lives",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-lives"
      }
    ],
    "description": "<p>选择直播</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/get-rank-works",
    "title": "选择榜单作品",
    "version": "4.0.0",
    "name": "get_rank_works",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-rank-works"
      }
    ],
    "description": "<p>选择作品</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/admin_v4/index/get-wiki",
    "title": "选择百科",
    "version": "4.0.0",
    "name": "get_wiki",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-wiki"
      }
    ],
    "description": "<p>选择百科</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/get-works",
    "title": "选择作品",
    "version": "4.0.0",
    "name": "get_works",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/get-works"
      }
    ],
    "description": "<p>选择作品</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "api/admin_v4/index/add-goods",
    "title": "增加/更新推荐商品",
    "version": "4.0.0",
    "name": "index_add_goods",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/add-goods"
      }
    ],
    "description": "<p>增加/编辑推荐好物</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>位置</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/v4/index/course",
    "title": "首页-推荐课程集合【教育宝典】",
    "version": "4.0.0",
    "name": "index_course",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/course"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "state",
            "description": "<p>状态 1上架 下架</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>听书作品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>作品标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>作品封面</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/v4/index/goods",
    "title": "首页-推荐商品",
    "version": "4.0.0",
    "name": "index_goods",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/goods"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>排序</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods",
            "description": "<p>商品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods.price",
            "description": "<p>价格</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/v4/index/lists",
    "title": "首页-书单推荐",
    "version": "4.0.0",
    "name": "index_lists",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/lists"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "num",
            "description": "<p>数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态  1上架 2下架</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/v4/index/live",
    "title": "推荐直播",
    "version": "4.0.0",
    "name": "index_live",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/live"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/v4/index/rank",
    "title": "首页-排行榜",
    "version": "4.0.0",
    "name": "index_rank",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/rank"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>4 课程 9 百科 10商品</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "state",
            "description": "<p>状态 1上架 下架</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>听书作品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>作品标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>作品封面</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/v4/index/wiki",
    "title": "首页-推荐百科",
    "version": "4.0.0",
    "name": "index_wiki",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/index/wiki"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "sort",
            "description": "<p>排序</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods",
            "description": "<p>商品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods.name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "goods.price",
            "description": "<p>价格</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/admin_v4/index/works",
    "title": "精选课程",
    "version": "4.0.0",
    "name": "index_works",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/index/works"
      }
    ],
    "description": "<p>精选课程</p>",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面图</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>创建时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "get",
    "url": "api/v4/list/works",
    "title": "书单的作品列表",
    "version": "4.0.0",
    "name": "list_works",
    "group": "后台-首页推荐",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/list/works"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list_id",
            "description": "<p>书单id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "state",
            "description": "<p>状态 1上架 下架</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works",
            "description": "<p>听书作品</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.works_id",
            "description": "<p>作品id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.title",
            "description": "<p>作品标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "works.cover_img",
            "description": "<p>作品封面</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/IndexController.php",
    "groupTitle": "后台-首页推荐"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/special_price/add_flash_sale",
    "title": "添加秒杀活动",
    "version": "1.0.0",
    "name": "_api_admin_v4_special_price_add_flash_sale",
    "group": "后台管理-商品价格设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/add_flash_sale"
      }
    ],
    "description": "<p>添加秒杀活动</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "team_id",
            "description": "<p>时间段id(1(9:00-12:59),2(13:00-18:59),3(19:00 - 20:59),4(21:00-次日8:59))</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "date",
            "description": "<p>日期(2020-11-11)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1上架2下架)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "group_name",
            "description": "<p>如果编辑的时候,需要额外传它</p>"
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>sku列表</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.goods_price",
            "description": "<p>秒杀价格</p>"
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "list.list",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.list.sku_number",
            "description": "<p>规格</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.list.sku_price",
            "description": "<p>规格收加</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"team_id\": 4,\n\"date\": \"2020-11-11\",\n\"status\": 1,\n\"group_name\":\"48roQ1604475214\",\n\"list\": [\n{\n\"goods_id\": 57,\n\"goods_price\": 1.4,\n\"list\": [\n{\n\"sku_number\": 1825350558,\n\"sku_price\": 1.4\n}\n]\n},\n{\n\"goods_id\": 330,\n\"goods_price\": 0.67,\n\"list\": [\n{\n\"sku_number\": 1835215184,\n\"sku_price\": 0.67\n},\n{\n\"sku_number\": 1607088243,\n\"sku_price\": 0.68\n},\n{\n\"sku_number\": 1835215184,\n\"sku_price\": 2.1\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1598421645,\n\"data\": {\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/SpecialPriceController.php",
    "groupTitle": "后台管理-商品价格设置"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/special_price/add_group_buy",
    "title": "添加拼团",
    "version": "1.0.0",
    "name": "_api_admin_v4_special_price_add_group_buy",
    "group": "后台管理-商品价格设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/add_group_buy"
      }
    ],
    "description": "<p>添加拼团</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "2"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(固定4)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_price",
            "description": "<p>商品价格</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "goods_original_price",
            "description": "<p>商品原价(可不传)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1上架2下架)</p>"
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>sku列表</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.sku_number",
            "description": "<p>sku</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.group_price",
            "description": "<p>拼团价格</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.group_num",
            "description": "<p>成团人数</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end_time",
            "description": "<p>结束时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"goods_id\": 91,\n\"type\": 4,\n\"goods_price\": 7.7,\n\"list\": [\n{\n\"sku_number\": 1612728266,\n\"group_price\": 7.7,\n\"group_num\": 2\n}\n],\n\"begin_time\": \"2020-09-15 14:00:00\",\n\"end_time\": \"2020-10-01 23:59:59\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1598421645,\n\"data\": {\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/SpecialPriceController.php",
    "groupTitle": "后台管理-商品价格设置"
  },
  {
    "type": "post",
    "url": "/api/admin_v4/special_price/add_normal",
    "title": "添加优惠活动",
    "version": "1.0.0",
    "name": "_api_admin_v4_special_price_add_normal",
    "group": "后台管理-商品价格设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/add_normal"
      }
    ],
    "description": "<p>添加优惠活动</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_id",
            "description": "<p>商品id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(固定2)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "goods_price",
            "description": "<p>商品价格</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "goods_original_price",
            "description": "<p>商品原价(可不传)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1上架2下架)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>sku列表</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.sku_number",
            "description": "<p>sku</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "list.sku_price",
            "description": "<p>购买价格</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "list.sku_price_black",
            "description": "<p>黑钻购买价格</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "list.sku_price_yellow",
            "description": "<p>皇钻购买价格</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "list.sku_price_dealer",
            "description": "<p>经销商购买价格</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": false,
            "field": "list.is_set_t_money",
            "description": "<p>是否单独设置推客收益(1设2不设)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "list.t_money",
            "description": "<p>普通推客收益(is_set_t_money=1时传该值)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "list.t_money_black",
            "description": "<p>黑钻收益(is_set_t_money=1时传该值)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "list.t_money_yellow",
            "description": "<p>皇钻收益(is_set_t_money=1时传该值)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "list.t_money_dealer",
            "description": "<p>经销商收益(is_set_t_money=1时传该值)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"goods_id\": 91,\n\"type\": 1,\n\"goods_price\": 7.7,\n\"status\": 1,\n\"list\": [\n{\n\"sku_number\": 1612728266,\n\"sku_price\": 11,\n\"sku_price_black\": 12,\n\"sku_price_yellow\": 13,\n\"sku_price_dealer\": 14,\n\"is_set_t_money\": 1,\n\"t_money\": 1,\n\"t_money_black\": 1.1,\n\"t_money_yellow\": 1.2,\n\"t_money_dealer\": 1.3\n}\n],\n\"begin_time\": \"2020-09-15 14:00:00\",\n\"end_time\": \"2020-10-01 23:59:59\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1598421645,\n\"data\": {\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Admin/V4/SpecialPriceController.php",
    "groupTitle": "后台管理-商品价格设置"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/special_price/flash_sale_list",
    "title": "秒杀的列表",
    "version": "1.0.0",
    "name": "_api_admin_v4_special_price_flash_sale_list",
    "group": "后台管理-商品价格设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/flash_sale_list"
      }
    ],
    "description": "<p>秒杀的列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1上架2下架)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "group_name",
            "description": "<p>获取详情和编辑时用(id没用)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/SpecialPriceController.php",
    "groupTitle": "后台管理-商品价格设置"
  },
  {
    "type": "get",
    "url": "/api/admin_v4/special_price/list",
    "title": "列表",
    "version": "1.0.0",
    "name": "_api_admin_v4_special_price_list",
    "group": "后台管理-商品价格设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/list"
      }
    ],
    "description": "<p>列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "4"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(1优惠4拼团)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "goods_name",
            "description": "<p>商品名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "begin_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "status",
            "description": "<p>状态(1上架2下架)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/SpecialPriceController.php",
    "groupTitle": "后台管理-商品价格设置"
  },
  {
    "type": "put",
    "url": "/api/admin_v4/special_price/status_change",
    "title": "修改状态",
    "version": "1.0.0",
    "name": "_api_admin_v4_special_price_status_change",
    "group": "后台管理-商品价格设置",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/admin_v4/special_price/status_change"
      }
    ],
    "description": "<p>修改状态</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "'on'",
              "'off'",
              "'del'"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>依次上架下架删除</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Admin/V4/SpecialPriceController.php",
    "groupTitle": "后台管理-商品价格设置"
  },
  {
    "type": "post",
    "url": "/api/v4/live_console/add",
    "title": "创建直播",
    "version": "4.0.0",
    "name": "_api_v4_live_console_add",
    "group": "我的直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/add"
      }
    ],
    "description": "<p>创建直播</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>直播间名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "describe",
            "description": "<p>简介</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "playback_price",
            "description": "<p>回放价格</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "twitter_money",
            "description": "<p>分校金额</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": false,
            "field": "is_free",
            "description": "<p>是否免费  1免费0收费</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": false,
            "field": "is_show",
            "description": "<p>是否公开  1公开</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": "<p>密码</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "0"
            ],
            "optional": false,
            "field": "can_push",
            "description": "<p>能否推广 1能</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "helper",
            "description": "<p>助手手机号,可多条</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "msg",
            "description": "<p>公告</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容介绍</p>"
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>直播时间列表</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.begin_at",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "list.length",
            "description": "<p>持续时长</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"title\": \"直播间名称11\",\n\"describe\": \"简介\",\n\"cover_img\": \"封面.jpg\",\n\"price\": 10,\n\"is_free\": 0,\n\"is_show\": 1,\n\"password\": \"652635\",\n\"can_push\": 1,\n\"helper\": \"1522222222\",\n\"msg\": \"直播预约公告\",\n\"content\": \"直播内容介绍\",\n\"list\": [\n{\n\"begin_at\": \"2020-09-25 20:30:00\",\n\"length\": 1.5\n},\n{\n\"begin_at\": \"2020-10-25 20:30:00\",\n\"length\": 2\n},\n{\n\"begin_at\": \"2020-10-20 20:30:00\",\n\"length\": 1.5\n},\n{\n\"begin_at\": \"2020-10-21 20:30:00\",\n\"length\": 2.2\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "我的直播"
  },
  {
    "type": "put",
    "url": "/api/v4/live_console/change_status",
    "title": "修改状态",
    "version": "4.0.0",
    "name": "_api_v4_live_console_change_status",
    "group": "我的直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/change_status"
      }
    ],
    "description": "<p>修改状态</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播间id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del",
              "off"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>操作</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "我的直播"
  },
  {
    "type": "post",
    "url": "/api/v4/live_console/check_helper",
    "title": "检查助手手机号",
    "version": "4.0.0",
    "name": "_api_v4_live_console_check_helper",
    "group": "我的直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/check_helper"
      }
    ],
    "description": "<p>检查助手手机号</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "helper",
            "description": "<p>手机号,可多条</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "我的直播"
  },
  {
    "type": "get",
    "url": "/api/v4/live_console/info",
    "title": "详情",
    "version": "4.0.0",
    "name": "_api_v4_live_console_info",
    "group": "我的直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/info"
      }
    ],
    "description": "<p>详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播间id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "describe",
            "description": "<p>简介</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态( 1:待审核  2:已取消 3:已驳回  4:通过)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "msg",
            "description": "<p>公告</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>直播内容介绍</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "reason",
            "description": "<p>驳回原因</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "check_time",
            "description": "<p>驳回或通过时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "playback_price",
            "description": "<p>回放价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_finish",
            "description": "<p>当status=4的时候  is_finish=1表示已结束 0表示待直播</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "helper",
            "description": "<p>助手</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_free",
            "description": "<p>是否免费</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_show",
            "description": "<p>是否公开</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "can_push",
            "description": "<p>是否退光</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "statistics",
            "description": "<p>相关统计</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "info_list",
            "description": "<p>场次列表</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info_list.begin_at",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "info_list.length",
            "description": "<p>时长</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1602818012,\n\"data\": {\n\"id\": 223,\n\"title\": \"直播间名称11\",\n\"describe\": \"简介\",\n\"cover_img\": \"封面.jpg\",\n\"status\": 2,\n\"msg\": \"直播预约公告\",\n\"content\": \"直播内容介绍\",\n\"reason\": \"\",\n\"check_time\": null,\n\"price\": \"10.00\",\n\"helper\": \"18624078563,18500065188,15081920892\",\n\"is_free\": 0,\n\"is_show\": 1,\n\"can_push\": 1,\n\"info_list\": [\n{\n\"id\": 339,\n\"begin_at\": \"2020-10-20 20:30:00\",\n\"end_at\": \"2020-10-20 22:00:00\",\n\"length\": 1.5,\n\"live_pid\": 223\n},\n{\n\"id\": 340,\n\"begin_at\": \"2020-10-21 20:30:00\",\n\"end_at\": \"2020-10-21 22:42:00\",\n\"length\": 2.2,\n\"live_pid\": 223\n},\n{\n\"id\": 341,\n\"begin_at\": \"2020-10-25 20:30:00\",\n\"end_at\": \"2020-10-25 22:30:00\",\n\"length\": 2,\n\"live_pid\": 223\n}\n]\n}\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "我的直播"
  },
  {
    "type": "get",
    "url": "/api/v4/live_console/list",
    "title": "列表",
    "version": "4.0.0",
    "name": "_api_v4_live_console_list",
    "group": "我的直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/list"
      }
    ],
    "description": "<p>列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3",
              "4"
            ],
            "optional": false,
            "field": "list_flag",
            "description": "<p>列表类型(1待审核 2已取消 3待直播 4已结束)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>page</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>size</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>直播状态(1:待审核,2:已取消,3:已驳回,4:通过)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1602838648,\n\"data\": [\n{\n\"id\": 223,\n\"title\": \"直播间名称11\",\n\"describe\": \"简介\",\n\"cover_img\": \"封面.jpg\",\n\"status\": 2,\n\"msg\": \"直播预约公告\",\n\"content\": \"直播内容介绍\",\n\"reason\": \"\",\n\"check_time\": null,\n\"price\": \"10.00\",\n\"helper\": \"18624078563,18500065188,15081920892\",\n\"is_free\": 0,\n\"is_show\": 1,\n\"can_push\": 1,\n\"nickname\": \"chandler\",\n\"end_at\": \"2020-10-25 22:30:00\",\n\"all_pass_flag\": 0,\n\"list_flag\": 2,\n\"info_list\": [\n{\n\"id\": 339,\n\"begin_at\": \"2020-10-20 20:30:00\",\n\"end_at\": \"2020-10-20 22:00:00\",\n\"length\": 1.5,\n\"live_pid\": 223,\n\"playback_url\": \"\"\n},\n{\n\"id\": 340,\n\"begin_at\": \"2020-10-21 20:30:00\",\n\"end_at\": \"2020-10-21 22:42:00\",\n\"length\": 2.2,\n\"live_pid\": 223,\n\"playback_url\": \"\"\n},\n{\n\"id\": 341,\n\"begin_at\": \"2020-10-25 20:30:00\",\n\"end_at\": \"2020-10-25 22:30:00\",\n\"length\": 2,\n\"live_pid\": 223,\n\"playback_url\": \"\"\n}\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "我的直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/back_lists",
    "title": "回放更多列表",
    "version": "4.0.0",
    "name": "back_lists",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/back_lists"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>同直播首页返回值</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":[\n               {\n\"id\": 136,\n\"user_id\": 161904,\n\"title\": \"测试57\",\n\"describe\": \"行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油\",\n\"price\": \"0.00\",\n\"cover_img\": \"/nlsg/works/20200611095034263657.jpg\",\n\"begin_at\": \"2020-10-01 15:02:00\",\n\"type\": 1,\n\"user\": {\n\"id\": 161904,\n\"nickname\": \"王琨\"\n},\n\"live_time\": \"2020.10.01 15:02\"\n}\n        ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/channels",
    "title": "直播场次列表",
    "version": "4.0.0",
    "name": "channels",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/channels"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播期数id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live_time",
            "description": "<p>直播时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live_status",
            "description": "<p>直播状态 1 未开始 2已结束 3正在进行</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user",
            "description": "<p>直播用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live",
            "description": "<p>直播相关</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live.title",
            "description": "<p>直播标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live.price",
            "description": "<p>直播价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live.cover_img",
            "description": "<p>直播封面</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":[\n              {\n\"id\": 11,\n\"user_id\": 161904,\n\"live_pid\": 1,\n\"begin_at\": \"2020-10-17 10:00:00\",\n\"end_at\": null,\n\"user\": {\n\"id\": 161904,\n\"nickname\": \"王琨\"\n},\n\"live\": {\n\"id\": 1,\n\"title\": \"第85期《经营能量》直播\",\n\"price\": \"0.00\",\n\"cover_img\": \"/live/look_back/live-1-9.jpg\"\n},\n\"live_status\": \"未开始\",\n\"live_time\": \"2020.10.17 10:00\"\n}\n        ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "post",
    "url": "api/v4/live/check_password",
    "title": "直播验证密码",
    "version": "4.0.0",
    "name": "check_password",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/check_password"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "password",
            "description": "<p>密码</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "POST",
    "url": "api/v4/live/free_order",
    "title": "免费预约",
    "version": "4.0.0",
    "name": "free_order",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/free_order"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播间id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "token",
            "description": "<p>用户认证</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/get_qr_code",
    "title": "二维码弹窗",
    "version": "4.0.0",
    "name": "get_qr_code",
    "group": "直播",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "relation_type",
            "description": "<p>类型 1.精品课程2.商城3.直播   4 购买360</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "relation_id",
            "description": "<p>数据id 课程id  商品id  直播id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/index",
    "title": "直播首页",
    "version": "4.0.0",
    "name": "index",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/index"
      }
    ],
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists",
            "description": "<p>直播列表</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.title",
            "description": "<p>直播标题</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.price",
            "description": "<p>直播价格</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.cover_img",
            "description": "<p>直播封面</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.type",
            "description": "<p>直播类型 1单场 2多场</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.user",
            "description": "<p>直播用户信息</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.is_password",
            "description": "<p>是否需要房间密码 1是 0否</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.live_time",
            "description": "<p>直播时间</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "live_lists.live_status",
            "description": "<p>直播状态 1未开始 2已结束 3正在直播</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "back_lists",
            "description": "<p>回放列表</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "offline",
            "description": "<p>线下课程</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "offline.title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "offline.subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "offline.total_price",
            "description": "<p>原价</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "offline.price",
            "description": "<p>现价</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "offline.cover_img",
            "description": "<p>封面</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "recommend",
            "description": "<p>推荐</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "recommend.type",
            "description": "<p>类型 1专栏 2讲座 3听书 4精品课  5线下课 6商品</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":[\n               \"data\": {\n\"live_lists\": [\n{\n\"id\": 136,\n\"user_id\": 161904,\n\"title\": \"测试57\",\n\"describe\": \"行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油\",\n\"price\": \"0.00\",\n\"cover_img\": \"/nlsg/works/20200611095034263657.jpg\",\n\"begin_at\": \"2020-10-01 15:02:00\",\n\"type\": 1,\n\"user\": {\n\"id\": 161904,\n\"nickname\": \"王琨\"\n},\n\"live_time\": \"2020.10.01 15:02\",\n\"live_status\": \"3\"\n}\n],\n\"back_lists\": [\n{\n\"id\": 136,\n\"user_id\": 161904,\n\"title\": \"测试57\",\n\"describe\": \"行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油\",\n\"price\": \"0.00\",\n\"cover_img\": \"/nlsg/works/20200611095034263657.jpg\",\n\"begin_at\": \"2020-10-01 15:02:00\",\n\"type\": 1,\n\"user\": {\n\"id\": 161904,\n\"nickname\": \"王琨\"\n},\n\"live_time\": \"2020.10.01 15:02\"\n},\n{\n\"id\": 137,\n\"user_id\": 255446,\n\"title\": \"测试\",\n\"describe\": \"测试\",\n\"price\": \"1.00\",\n\"cover_img\": \"/nlsg/works/20200611172548507266.jpg\",\n\"begin_at\": \"2020-10-01 15:02:00\",\n\"type\": 1,\n\"user\": null,\n\"live_time\": \"2020.10.01 15:02\"\n}\n]\n}\n        ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/offline/info",
    "title": "线下课程详情",
    "version": "4.0.0",
    "name": "info",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/offline/info"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>课程id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subtitle",
            "description": "<p>副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "describe",
            "description": "<p>内容</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "total_price",
            "description": "<p>总价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>现价</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "image",
            "description": "<p>详情图</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n              \"id\": 274,\n              \"pic\": \"https://image.nlsgapp.com/nlsg/banner/20191118184425289911.jpg\",\n              \"title\": \"电商弹窗课程日历套装\",\n              \"url\": \"/mall/shop-detailsgoods_id=448&time=201911091925\"\n          },\n          {\n              \"id\": 296,\n              \"pic\": \"https://image.nlsgapp.com/nlsg/banner/20191227171346601666.jpg\",\n              \"title\": \"心里学\",\n              \"url\": \"/mall/shop-details?goods_id=479\"\n          }\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/lists",
    "title": "直播更多列表",
    "version": "4.0.0",
    "name": "lists",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/lists"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>同直播首页返回值</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":[\n               {\n\"id\": 136,\n\"user_id\": 161904,\n\"title\": \"测试57\",\n\"describe\": \"行字节处理知牛哥教学楼哦咯咯娄哦咯加油加油加油\",\n\"price\": \"0.00\",\n\"cover_img\": \"/nlsg/works/20200611095034263657.jpg\",\n\"begin_at\": \"2020-10-01 15:02:00\",\n\"type\": 1,\n\"user\": {\n\"id\": 161904,\n\"nickname\": \"王琨\"\n},\n\"live_time\": \"2020.10.01 15:02\",\n\"live_status\": \"正在直播\"\n}\n        ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "post",
    "url": "api/v4/live/live_comment_his",
    "title": "直播间评论上滑",
    "version": "4.0.0",
    "name": "live_comment_his",
    "group": "直播",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播间id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_son_flag",
            "description": "<p>渠道</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/live_push_one",
    "title": "直播间推送最后一条",
    "version": "4.0.0",
    "name": "live_push_one",
    "group": "直播",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播间id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/offline/order",
    "title": "线下课程报名记录",
    "version": "4.0.0",
    "name": "order",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/offline/order"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "token",
            "description": "<p>当前用户</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>支付定金</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>状态 0 待支付  1已支付  2取消</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "product",
            "description": "<p>线下课程</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "product.title",
            "description": "<p>课程标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "product.cover_img",
            "description": "<p>课程封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "product.total_price",
            "description": "<p>课程总价</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\":[\n              {\n\"relation_id\": 1,\n\"price\": \"99.00\",\n\"ordernum\": \"20091100211190416747499\",\n\"product\": {\n\"id\": 1,\n\"title\": \"经营能量线下品牌课\",\n\"cover_img\": \"/live/jynl/jynltjlb.jpg\",\n\"total_price\": \"1000.00\"\n}\n}\n        ]\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "post",
    "url": "api/v4/live/pay_order",
    "title": "付费预约",
    "version": "4.0.0",
    "name": "pay_order",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/pay_order"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播房间id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "token",
            "description": "<p>当前用户</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/ranking",
    "title": "排行榜",
    "version": "4.0.0",
    "name": "ranking",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/ranking"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "liveinfo_id",
            "description": "<p>直播info_id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user_ranking",
            "description": "<p>自己排名</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user_invite_num",
            "description": "<p>自己邀请数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ranking",
            "description": "<p>排行</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ranking.username",
            "description": "<p>用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ranking.headimg",
            "description": "<p>用户头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ranking.invite_num",
            "description": "<p>邀请数量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "ranking.is_self",
            "description": "<p>是否是当前用户</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "    HTTP/1.1 200 OK\n    {\n      \"code\": 200,\n      \"msg\" : '成功',\n      \"data\": {\n\"user_ranking\": 2,\n\"user_invite_num\": 10,\n\"ranking\": [\n{\n\"username\": \"亚梦想\",\n\"headimg\": \"/wechat/authorpt/lzh.png\",\n\"invite_num\": 30\n},\n{\n\"username\": \"小雨衣\",\n\"headimg\": \"/wechat/authorpt/lzh.png\",\n\"invite_num\": 20\n}\n]\n}\n    }",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/v4/live/show",
    "title": "直播详情",
    "version": "4.0.0",
    "name": "show",
    "group": "直播",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live/show"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info",
            "description": "<p>直播相关</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_sub_column",
            "description": "<p>是否订阅专栏</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_sub",
            "description": "<p>是否付费订阅</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_appmt",
            "description": "<p>是否免费订阅</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_forbid",
            "description": "<p>是否全体禁言(1禁了,0没禁)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_silence",
            "description": "<p>当前用户是否禁言中(0没有 其他剩余秒数)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.level",
            "description": "<p>当前用户等级</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_begin",
            "description": "<p>1是直播中</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_admin",
            "description": "<p>1是管理员(包括创建人和助手) 0不是</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.column_id",
            "description": "<p>专栏id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.begin_at",
            "description": "<p>直播开始时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.end_at",
            "description": "<p>直播结束时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.length",
            "description": "<p>直播时长</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.user",
            "description": "<p>用户</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.user.nickname",
            "description": "<p>用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.user.headimg",
            "description": "<p>用户头像</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.user.intro",
            "description": "<p>用户简介</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.is_password",
            "description": "<p>是否需要密码 0 不需要 1需要</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live",
            "description": "<p>直播</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.title",
            "description": "<p>直播标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.cover_img",
            "description": "<p>直播封面</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.is_free",
            "description": "<p>是否免费 0免费 1付费</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.twitter_money",
            "description": "<p>分销金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.playback_price",
            "description": "<p>回放金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.is_show",
            "description": "<p>是否公开 1显示  0不显示</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.helper",
            "description": "<p>助理电话</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.msg",
            "description": "<p>直播预约公告</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.describe",
            "description": "<p>直播简介</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.content",
            "description": "<p>直播内容</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "info.live.can_push",
            "description": "<p>允许推送 1允许 2不允许</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "recommend.list",
            "description": "<p>推荐</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "recommend.list.title",
            "description": "<p>推荐标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "recommend.list.subtitle",
            "description": "<p>推荐副标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "recommend.list.original_price",
            "description": "<p>原价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "recommend.list.price",
            "description": "<p>推荐价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "recommend.list.cover_pic",
            "description": "<p>推荐封面图</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n          {\n              \"id\": 274,\n              \"pic\": \"https://image.nlsgapp.com/nlsg/banner/20191118184425289911.jpg\",\n              \"title\": \"电商弹窗课程日历套装\",\n              \"url\": \"/mall/shop-detailsgoods_id=448&time=201911091925\"\n          },\n          {\n              \"id\": 296,\n              \"pic\": \"https://image.nlsgapp.com/nlsg/banner/20191227171346601666.jpg\",\n              \"title\": \"心里学\",\n              \"url\": \"/mall/shop-details?goods_id=479\"\n          }\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveController.php",
    "groupTitle": "直播"
  },
  {
    "type": "get",
    "url": "api/live_v4/index/data",
    "title": "直播分析",
    "version": "4.0.0",
    "name": "index_data",
    "group": "直播后台-分析",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/index/data"
      }
    ],
    "description": "<p>直播分析</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台-分析"
  },
  {
    "type": "get",
    "url": "api/live_v4/index/statistics",
    "title": "数据统计",
    "version": "4.0.0",
    "name": "index_statistics",
    "group": "直播后台-数据统计",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/index/statistics"
      }
    ],
    "description": "<p>数据统计</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台-数据统计"
  },
  {
    "type": "get",
    "url": "api/live_v4/index/check_helper",
    "title": "检验助手",
    "version": "4.0.0",
    "name": "index_check_helper",
    "group": "直播后台-检验助手",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/index/check_helper"
      }
    ],
    "description": "<p>检验助手</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "helper",
            "description": "<p>检验助手</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台-检验助手"
  },
  {
    "type": "get",
    "url": "api/live_v4/index/lives",
    "title": "直播列表",
    "version": "4.0.0",
    "name": "index_lives",
    "group": "直播后台-直播列表",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/index/lives"
      }
    ],
    "description": "<p>直播列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "status",
            "description": "<p>1未开始 2已结束 3正在直播</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "order_num",
            "description": "<p>预约人数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "live_status",
            "description": "<p>直播状态 1未开始 2已结束 3正在直播</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "live_price_sum",
            "description": "<p>直播收益</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "live_twitter_price_sum",
            "description": "<p>推客收益</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台-直播列表"
  },
  {
    "type": "post",
    "url": "api/live_v4/index/create",
    "title": "直播创建/编辑",
    "version": "4.0.0",
    "name": "index_data",
    "group": "直播后台-直播创建/编辑",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/index/create"
      }
    ],
    "description": "<p>直播创建/编辑</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover",
            "description": "<p>封面</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>主播账号</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "begin_at",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "end_at",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "price",
            "description": "<p>价格</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "twitter_money",
            "description": "<p>分销金额</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "helper",
            "description": "<p>直播助手</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>直播内容</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台-直播创建/编辑"
  },
  {
    "type": "post",
    "url": "api/live_v4/live/delete",
    "title": "直播删除",
    "version": "4.0.0",
    "name": "live_delete",
    "group": "直播后台-直播删除",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live/delete"
      }
    ],
    "description": "<p>直播删除</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台-直播删除"
  },
  {
    "type": "get",
    "url": "api/live_v4/order/list",
    "title": "订单列表和详情",
    "version": "4.0.0",
    "name": "order_list",
    "group": "直播后台-订单列表和详情",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/order/list"
      }
    ],
    "description": "<p>订单列表和详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "id",
            "description": "<p>单条详情传id获取</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": true,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": true,
            "field": "created_at",
            "description": "<p>订单时间范围(2020-01-01,2022-02-02)</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": true,
            "field": "pay_type",
            "description": "<p>支付渠道(1微信端 2app微信 3app支付宝 4ios)</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": true,
            "field": "os_type",
            "description": "<p>客户端(客户端:1安卓 2ios 3微信 )</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": true,
            "field": "phone",
            "description": "<p>账号</p>"
          },
          {
            "group": "Parameter",
            "type": "strint",
            "optional": true,
            "field": "title",
            "description": "<p>直播标题</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "9",
              "10",
              "14",
              "15",
              "16"
            ],
            "optional": true,
            "field": "type",
            "description": "<p>订单类型(9精品课,10直播,14线下产品,15讲座,16新vip)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "goods",
            "description": "<p>商品信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "pay_record",
            "description": "<p>支付信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "pay_record_detail",
            "description": "<p>收益信息,当指定id时返回</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "live",
            "description": "<p>所属直播信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "user",
            "description": "<p>购买者信息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>订单id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "type",
            "description": "<p>订单类型(9精品课,10直播,14线下产品,15讲座,16新vip)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "price",
            "description": "<p>商品价格</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "pay_price",
            "description": "<p>支付金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>支付状态  0 待支付  1已支付  2取消</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "pay_type",
            "description": "<p>支付渠道</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "os_type",
            "description": "<p>客户端</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "[\n{\n\"id\": 167376,\n\"type\": 10,\n\"relation_id\": \"0\",\n\"pay_time\": \"2020-04-30 15:05:16\",\n\"price\": \"99.00\",\n\"user_id\": 313125,\n\"pay_price\": \"99.00\",\n\"pay_type\": 0,\n\"ordernum\": \"202004301505044830\",\n\"live_id\": 17,\n\"os_type\": 3,\n\"goods\": {\n\"goods_id\": 0,\n\"title\": \"数据错误\",\n\"subtitle\": \"\",\n\"cover_img\": \"\",\n\"detail_img\": \"\",\n\"price\": \"价格数据错误\"\n},\n\"pay_record\": {\n\"ordernum\": \"202004301505044830\",\n\"price\": \"99.00\",\n\"type\": 1,\n\"created_at\": \"2020-04-30 15:05:16\"\n},\n\"pay_record_detail\": {\n\"id\": 27001,\n\"type\": 10,\n\"ordernum\": \"202004301505044830\",\n\"user_id\": 234586,\n\"user\": {\n\"id\": 234586,\n\"phone\": \"15305396370\",\n\"nickname\": \"慧宇教育-王秀翠\"\n}\n},\n\"live\": {\n\"id\": 17,\n\"title\": \"经营家庭和孩子的秘密——发现婚姻的小幸福，成就育儿的大智慧\",\n\"describe\": \"王琨老师本人视频直播课，帮助你拥有幸福的婚姻、成为智慧的父母、培养优秀的孩子！\",\n\"begin_at\": \"2021-01-21 19:00:00\",\n\"cover_img\": \"/live/liveinfo30/20200121.png\"\n},\n\"user\": {\n\"id\": 313125,\n\"phone\": \"15042623555\",\n\"nickname\": \"清然一平常心\"\n}\n}\n]",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/OrderController.php",
    "groupTitle": "直播后台-订单列表和详情"
  },
  {
    "type": "get",
    "url": "api/live_v4/comment/index",
    "title": "评论列表",
    "version": "4.0.0",
    "name": "comment_index",
    "group": "直播后台-评论列表",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/comment/index"
      }
    ],
    "description": "<p>评论列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>名称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "nicknake",
            "description": "<p>用户账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>评论内容</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "start",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "end",
            "description": "<p>结束时间</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/CommentController.php",
    "groupTitle": "直播后台-评论列表"
  },
  {
    "type": "get",
    "url": "api/live_v4/sub/index",
    "title": "预约列表",
    "version": "4.0.0",
    "name": "sub_index",
    "group": "直播后台-评论列表",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/sub/index"
      }
    ],
    "description": "<p>预约列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>分页</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "ordernum",
            "description": "<p>订单号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>直播标题</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "phone",
            "description": "<p>用户账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "twitter_phone",
            "description": "<p>推客账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "date",
            "description": "<p>支付时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>下单时间</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/SubscribeController.php",
    "groupTitle": "直播后台-评论列表"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_comment/listExcel",
    "title": "评论下载",
    "version": "4.0.0",
    "name": "comment_index",
    "group": "直播后台-评论列表下载",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/comment/index"
      }
    ],
    "description": "<p>评论列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_flag",
            "description": "<p>直播渠道</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/CommentController.php",
    "groupTitle": "直播后台-评论列表下载"
  },
  {
    "type": "get",
    "url": "api/live_v4/comment/show",
    "title": "评论查看",
    "version": "4.0.0",
    "name": "comment_show",
    "group": "直播后台-评论查看",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/comment/show"
      }
    ],
    "description": "<p>评论查看</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>评论id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/CommentController.php",
    "groupTitle": "直播后台-评论查看"
  },
  {
    "type": "get",
    "url": "api/live_v4/index/live_users",
    "title": "主播账号",
    "version": "4.0.0",
    "name": "index_live_users",
    "group": "直播后台_-主播账号",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/index/live_users"
      }
    ],
    "description": "<p>主播账号</p>",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台_-主播账号"
  },
  {
    "type": "get",
    "url": "api/live_v4/live/info",
    "title": "直播详情",
    "version": "4.0.0",
    "name": "live_info",
    "group": "直播后台_-直播详情",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live/info"
      }
    ],
    "description": "<p>直播详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播间id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台_-直播详情"
  },
  {
    "type": "post",
    "url": "api/live_v4/comment/delete",
    "title": "直播评论删除",
    "version": "4.0.0",
    "name": "comment_delete",
    "group": "直播后台",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/comment/delete"
      }
    ],
    "description": "<p>直播评论删除</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>直播评论id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/CommentController.php",
    "groupTitle": "直播后台"
  },
  {
    "type": "get",
    "url": "api/live_v4/index/statistics_img_data",
    "title": "折线图数据",
    "version": "4.0.0",
    "name": "index_statistics_img_data",
    "group": "直播后台",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/index/statistics_img_data"
      }
    ],
    "description": "<p>折线图数据</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "str_time",
            "description": "<p>开始时间</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "end_time",
            "description": "<p>结束时间</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Live/V4/IndexController.php",
    "groupTitle": "直播后台"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/flag_poster_list",
    "title": "海报列表",
    "version": "4.0.0",
    "name": "live_info_flag_poster_list",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/flag_poster_list"
      }
    ],
    "description": "<p>海报列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "son_id",
            "description": "<p>渠道用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "status",
            "description": "<p>状态(待开启  2开启  3关闭)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "son_flag",
            "description": "<p>渠道账号</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/flag_poster_status",
    "title": "海报状态修改",
    "version": "4.0.0",
    "name": "live_info_flag_poster_status",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/flag_poster_status"
      }
    ],
    "description": "<p>海报状态修改</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "off",
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/live_order",
    "title": "预约订单",
    "version": "4.0.0",
    "name": "live_info_live_order",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_order"
      }
    ],
    "description": "<p>预约订单</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "excel_flag",
            "defaultValue": "1,0",
            "description": "<p>是否未导出请求(1是)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "phone",
            "description": "<p>用户u账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "t_nickname",
            "description": "<p>推荐昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "t_phone",
            "description": "<p>推荐账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "son_flag",
            "description": "<p>别名</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/live_order_kun",
    "title": "王琨直播间的成交数据",
    "version": "4.0.0",
    "name": "live_info_live_order_kun",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_order_kun"
      }
    ],
    "description": "<p>王琨直播间的成交数据</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "excel_flag",
            "defaultValue": "1,0",
            "description": "<p>是否未导出请求(1是)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "ordernum",
            "description": "<p>订单编号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "phone",
            "description": "<p>用户账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "invite_phone",
            "description": "<p>推荐人手机号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "protect_phone",
            "description": "<p>保护人手机号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "diamond_phone",
            "description": "<p>别名</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": true,
            "field": "qd",
            "description": "<p>渠道(1抖音 2李婷 3自有平台)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/live_sub_order",
    "title": "邀约",
    "version": "4.0.0",
    "name": "live_info_live_sub_order",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/live_sub_order"
      }
    ],
    "description": "<p>邀约</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "excel_flag",
            "defaultValue": "1,0",
            "description": "<p>是否未导出请求(1是)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "nickname",
            "description": "<p>昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "user_id",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "phone",
            "description": "<p>用户u账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "t_nickname",
            "description": "<p>推荐昵称</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "t_user_id",
            "description": "<p>推荐用户id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "t_phone",
            "description": "<p>推荐账号</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "son_flag",
            "description": "<p>别名</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/online_num",
    "title": "在线人数",
    "version": "4.0.0",
    "name": "live_info_online_num",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/online_num"
      }
    ],
    "description": "<p>在线人数</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "son_id",
            "description": "<p>渠道过滤(son_flag对应的son_id)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "son_flag",
            "description": "<p>渠道列表,如果有,则显示过滤选项</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>内容</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/online_num_info",
    "title": "在线人数详情",
    "version": "4.0.0",
    "name": "live_info_online_num_info",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/online_num_info"
      }
    ],
    "description": "<p>在线人数详情</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "date",
            "description": "<p>时间,精确到分钟(2021-01-01 10:00:01)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/statistics",
    "title": "统计数据",
    "version": "4.0.0",
    "name": "live_info_statistics",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/statistics"
      }
    ],
    "description": "<p>统计数据</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>头像,</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>老师,</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "user_id",
            "description": "<p>老师id,</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "begin_at",
            "description": "<p>直播开始,</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "end_at",
            "description": "<p>直播结束,</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "live_login",
            "description": "<p>人气,</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_num",
            "description": "<p>总预约人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "watch_counts",
            "description": "<p>观看人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "not_watch_counts",
            "description": "<p>未观看人数,</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_order",
            "description": "<p>成交单数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_order_money",
            "description": "<p>&quot;:总金额</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_order_user",
            "description": "<p>购买人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_sub_count",
            "description": "<p>总预约人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_not_buy",
            "description": "<p>为购买人数</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "more_than_30m",
            "description": "<p>大于30分钟</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "more_than_60m",
            "description": "<p>小于30分钟</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_login",
            "description": "<p>累计人次</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "total_sub",
            "description": "<p>累计人数</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "get",
    "url": "api/live_v4/live_info/user_watch",
    "title": "(未)进入直播间用户列表",
    "version": "4.0.0",
    "name": "live_info_user_watch",
    "group": "直播后台新增",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/live_v4/live_info/user_watch"
      }
    ],
    "description": "<p>(未)进入直播间用户列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>标记,1是进入了,2是没进入</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "excel_flag",
            "defaultValue": "1,0",
            "description": "<p>是否未导出请求(1是)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Live/V4/InfoController.php",
    "groupTitle": "直播后台新增"
  },
  {
    "type": "put",
    "url": "/api/v4/live_console/change_info_status",
    "title": "开始,结束直播",
    "version": "4.0.0",
    "name": "_api_v4_live_console_change_info_status",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/change_info_status"
      }
    ],
    "description": "<p>开始,结束直播</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播期数id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_info_id",
            "description": "<p>直播场次id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "finish"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>操作(开始,结束)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "put",
    "url": "/api/v4/live_console/change_push_msg_state",
    "title": "推送消息-状态修改",
    "version": "4.0.0",
    "name": "_api_v4_live_console_change_push_msg_state",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/change_push_msg_state"
      }
    ],
    "description": "<p>推送消息-状态修改</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>推送记录id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>操作(取消,删除)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "get",
    "url": "/api/v4/live_console/push_msg_list",
    "title": "推送消息-列表",
    "version": "4.0.0",
    "name": "_api_v4_live_console_push_msg_list",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/push_msg_list"
      }
    ],
    "description": "<p>推送消息-列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>page</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>size</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "id",
            "description": "<p>id(获取单条)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>live_id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_info_id",
            "description": "<p>live_info_id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>推送id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "live_info_id",
            "description": "<p>場次id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "push_type",
            "description": "<p>商品類型</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "push_gid",
            "description": "<p>目標id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "click_num",
            "description": "<p>點擊數</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "close_num",
            "description": "<p>关闭数</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_push",
            "description": "<p>是否推送 0已取消</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "push_at",
            "description": "<p>预设推送时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_done",
            "description": "<p>是否完成(1完成)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "done_at",
            "description": "<p>完成时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_self",
            "description": "<p>是不是自己的(自己的能编辑删除)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "order_count",
            "description": "<p>单量</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "money_count",
            "description": "<p>收益</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "info",
            "description": "<p>目标信息</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1603272248,\n\"data\": [\n{\n\"id\": 9,\n\"live_id\": 224,\n\"live_info_id\": 346,\n\"push_type\": 8,\n\"push_gid\": 553,\n\"click_num\": 0,\n\"close_num\": 0,\n\"is_push\": 0,\n\"push_at\": \"2020-10-21 14:50\",\n\"is_self\": 1,\n\"info\": {\n\"id\": 553,\n\"title\": \"孩子，把你的手给我\",\n\"subtitle\": \"《孩子把你的手给我》的作者是海姆·G.吉诺特，此书是畅高居美国各大图书排行榜榜首。\",\n\"cover_img\": \"/nlsg/works/20191118162916177457.png\",\n\"price\": \"0.00\",\n\"with_type\": 8\n},\n\"order_count\": \"暂无单\",\n\"money_count\": \"¥暂无\"\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "post",
    "url": "/api/v4/live_console/push_msg_to_live",
    "title": "推送消息-添加(修改)",
    "version": "4.0.0",
    "name": "_api_v4_live_console_push_msg_to_live",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_console/push_msg_to_live"
      }
    ],
    "description": "<p>推送消息-添加(修改)</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播期数id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "ive_info_id",
            "description": "<p>直播场次id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3",
              "4",
              "6",
              "7",
              "8"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型( 1专栏 2精品课 3商品 4 线下产品门票类 6新会员 7:讲座 8:听书)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "gid",
            "description": "<p>目标id(type=6时,1是360)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "time",
            "description": "<p>推送时间(2020-01-01 01:00)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "post",
    "url": "/api/v4/live_forbid/add",
    "title": "禁言",
    "version": "4.0.0",
    "name": "_api_v4_live_forbid_add",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_forbid/add"
      }
    ],
    "description": "<p>禁言</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播期数id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "ive_info_id",
            "description": "<p>直播场次id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "user_id",
            "description": "<p>目标任务id(全体就是0)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "off"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>on开启禁言,off关闭禁言</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "post",
    "url": "/api/v4/live_notice/add",
    "title": "公告和笔记-添加",
    "version": "4.0.0",
    "name": "_api_v4_live_notice_add",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_notice/add"
      }
    ],
    "description": "<p>公告和笔记-添加</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播期数id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "ive_info_id",
            "description": "<p>直播场次id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(1公告 2笔记)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容(最多300字)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "length",
            "description": "<p>公告的持续时长(秒)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "send_at",
            "description": "<p>推送时间,不传默认为下一分钟</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "put",
    "url": "/api/v4/live_notice/change_state",
    "title": "公告和笔记-修改状态",
    "version": "4.0.0",
    "name": "_api_v4_live_notice_change_state",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_notice/change_state"
      }
    ],
    "description": "<p>公告和笔记-修改状态</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>推送记录id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "off",
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>操作(取消,删除)</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "get",
    "url": "/api/v4/live_notice/list",
    "title": "公告和笔记-列表",
    "version": "4.0.0",
    "name": "_api_v4_live_notice_list",
    "group": "直播画面页",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/live_notice/list"
      }
    ],
    "description": "<p>公告和笔记-列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "page",
            "description": "<p>page</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "size",
            "description": "<p>size</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "id",
            "description": "<p>id(获取单条)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>live_id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "live_info_id",
            "description": "<p>live_info_id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": true,
            "field": "type",
            "description": "<p>类型(1公告 2笔记)</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>推送id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "live_id",
            "description": "<p>直播id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "live_info_id",
            "description": "<p>場次id</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "content",
            "description": "<p>内容</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "length",
            "description": "<p>时长(秒)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_send",
            "description": "<p>是否推送 0已取消</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "send_at",
            "description": "<p>预设推送时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_done",
            "description": "<p>是否完成(1完成)</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "done_at",
            "description": "<p>完成时间</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "is_self",
            "description": "<p>是不是自己的(自己的能编辑删除)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"code\": 200,\n\"msg\": \"成功\",\n\"now\": 1603350636,\n\"data\": [\n{\n\"id\": 350,\n\"live_id\": 224,\n\"live_info_id\": 346,\n\"content\": \"笔记法撒旦飞洒\",\n\"length\": 300,\n\"send_at\": \"2020-10-22 14:55:00\",\n\"is_send\": 1,\n\"is_done\": 0,\n\"done_at\": null,\n\"is_self\": 1\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/LiveConsoleController.php",
    "groupTitle": "直播画面页"
  },
  {
    "type": "post",
    "url": "api/v4/im_doc/add",
    "title": "添加文案",
    "version": "4.0.0",
    "name": "api_v4_im_doc_add",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/add"
      }
    ],
    "description": "<p>添加文案</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(1商品 2附件 3文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type_info",
            "description": "<p>详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链  18线下课 19听书 21音频 22视频 23图片 24文件 31文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "obj_id",
            "description": "<p>目标id(当type=1时需要传)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容或名称(type=1如果是商品类型传商品的标题,外链类型传网址标题)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "subtitle",
            "description": "<p>副标题(外链类型传网址说明名称)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面图片(type=1必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "media_id",
            "description": "<p>媒体id(type=2时必传,如果是图片,可逗号拼接多个)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "url",
            "description": "<p>外链的地址</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "post",
    "url": "api/v4/im_doc/add_for_app",
    "title": "(废弃)添加文案",
    "version": "4.0.0",
    "name": "api_v4_im_doc_add_for_app",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/add_for_app"
      }
    ],
    "description": "<p>添加文案</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>类型(1商品 2附件 3文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type_info",
            "description": "<p>详细类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营 17外链 21音频 22视频 23图片 24文件 31文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": true,
            "field": "obj_id",
            "description": "<p>目标id(当type=1时需要传)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "content",
            "description": "<p>内容或名称(如果是商品类型传商品的标题,外链类型传网址)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "subtitle",
            "description": "<p>副标题(外链类型传网址说明名称)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "cover_img",
            "description": "<p>封面图片(type_info等于22,11-16必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "second",
            "description": "<p>视频音频的时长(秒)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "format",
            "description": "<p>格式后缀名</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "file_url",
            "description": "<p>附件地址,当type=2时需要传(如果是图片,格式url,size,width,height,md5;多个图片用分号隔开)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "file_md5",
            "description": "<p>文件md5(type_info=22)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "file_size",
            "description": "<p>文件大小(type_info=21,22,24)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "img_size",
            "description": "<p>图片大小(type=22时必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "img_width",
            "description": "<p>图片宽度(type=22时必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "img_height",
            "description": "<p>图片高度(type=22时必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "img_format",
            "description": "<p>图片格式类型(type=22时必穿)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "img_md5",
            "description": "<p>图片md5(type=22时必穿)</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "[\n{\n\"type\": 1,\n\"type_info\": 11,\n\"obj_id\": 448,\n\"content\": \"44节科学探索课，开启孩子自然科学之门\",\n\"cover_img\": \"nlsg/authorpt/20201229114832542932.png\",\n\"subtitle\": \"浩瀚宇宙、海洋世界、恐龙时代、昆虫家族，精美视频动画展现前沿的科学知识，让孩子爱上自然科学\",\n\"status\": 1\n},\n{\n\"type\": 1,\n\"type_info\": 16,\n\"obj_id\": 517,\n\"content\": \"30天亲子训练营\",\n\"cover_img\": \"wechat/works/video/184528/8105_1527070171.png\",\n\"subtitle\": \"\",\n\"status\": 1\n},\n{\n\"type\": 2,\n\"type_info\": 21,\n\"content\": \"文件ing.mp3\",\n\"file_url\": \"https://1253639599.vod2.myqcloud.com/32a152b3vodgzp1253639599/f63da4f95285890780889058541/aaodecBf5FAA.mp3\",\n\"file_size\": 4426079,\n\"format\": \"mp3\",\n\"second\": 275,\n\"file_md5\": \"34131545324543\",\n\"status\": 1\n},\n{\n\"type\": 2,\n\"type_info\": 22,\n\"content\": \"视频.mp4\",\n\"file_url\": \"https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/345e2a389fe32d62fedad3d6d2150110.mp4\",\n\"file_size\": 1247117,\n\"format\": \"mp4\",\n\"second\": 7,\n\"file_md5\": \"3413154532454311\",\n\"cover_img\": \"https://cos.ap-shanghai.myqcloud.com/240b-shanghai-030-shared-08-1256635546/751d-1400536432/a4d8-425232/643665ba437cf198a9961f85795d8474.jpg?imageMogr2/\",\n\"img_size\": 277431,\n\"img_width\": 720,\n\"img_height\": 1600,\n\"img_format\": \"jpg\",\n\"img_md5\": \"14436454\",\n\"status\": 1\n},\n{\n\"type\": 3,\n\"type_info\": 31,\n\"content\": \"nihao\"\n}\n]",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "get",
    "url": "api/v4/im_doc/category",
    "title": "分类",
    "version": "4.0.0",
    "name": "api_v4_im_doc_category",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/category"
      }
    ],
    "description": "<p>分类的列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id 0为全部</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "get",
    "url": "api/v4/im_doc/category/product",
    "title": "分类筛选的商品列表",
    "version": "4.0.0",
    "name": "api_v4_im_doc_category_product",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/category/product"
      }
    ],
    "description": "<p>分类筛选的商品列表</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "category_id",
            "description": "<p>分类id 0为全部</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "type",
            "description": "<p>类型  1.精品课 2 讲座 3 商品 4 直播 5训练营 6幸福360</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "put",
    "url": "api/v4/im_doc/change_job_status",
    "title": "发送任务状态修改",
    "version": "4.0.0",
    "name": "api_v4_im_doc_change_job_status",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/change_job_status"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>任务id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "on",
              "off",
              "del",
              "send"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作</p>"
          }
        ]
      }
    },
    "description": "<p>发送任务状态修改</p>",
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "put",
    "url": "api/v4/im_doc/change_status",
    "title": "文案状态修改",
    "version": "4.0.0",
    "name": "api_v4_im_doc_change_status",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/change_status"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "id",
            "description": "<p>id</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "del"
            ],
            "optional": false,
            "field": "flag",
            "description": "<p>动作(del:删除)</p>"
          }
        ]
      }
    },
    "description": "<p>文案状态修改</p>",
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "post",
    "url": "api/v4/im_doc/group_list",
    "title": "群列表",
    "version": "4.0.0",
    "name": "api_v4_im_doc_group_list",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/group_list"
      }
    ],
    "description": "<p>群列表</p>",
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "post",
    "url": "api/v4/im_doc/job_add",
    "title": "添加发送任务",
    "version": "4.0.0",
    "name": "api_v4_im_doc_job_add",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/job_add"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "doc_id",
            "description": "<p>文案id</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2"
            ],
            "optional": false,
            "field": "send_type",
            "description": "<p>发送时间类型(1立刻 2定时)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": true,
            "field": "send_at",
            "description": "<p>定时时间</p>"
          },
          {
            "group": "Parameter",
            "type": "string[]",
            "optional": false,
            "field": "info",
            "description": "<p>对象列表的json</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "info.type",
            "description": "<p>目标对象类型(1群组 2个人 3标签)</p>"
          },
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "info.list",
            "description": "<p>目标id</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n\"doc_id\": 1,\n\"send_type\": 1,\n\"send_at\": \"\",\n\"info\": [\n{\n\"type\": 1,\n\"list\": [\n1,\n2,\n3\n]\n}\n]\n}",
          "type": "json"
        }
      ]
    },
    "description": "<p>添加发送任务</p>",
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "post",
    "url": "api/v4/im_doc/job_list",
    "title": "(废弃)发送任务列表",
    "version": "4.0.0",
    "name": "api_v4_im_doc_job_list",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/job_list"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "doc_type",
            "description": "<p>文案类型(1商品 2附件 3文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "doc_type_info",
            "description": "<p>文案类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营21音频 22视频 23图片 31文本)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "0",
              "1",
              "2",
              "3",
              "4"
            ],
            "optional": false,
            "field": "is_done",
            "description": "<p>发送结果(1待发送  2发送中 3已完成 4无任务)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "send_obj_type",
            "description": "<p>发送目标类型(1群组 2个人 3标签)</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "send_obj_id",
            "description": "<p>发送目标id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "get",
    "url": "api/v4/im_doc/job_list_for_app",
    "title": "发送任务列表",
    "version": "4.0.0",
    "name": "api_v4_im_doc_job_list_for_app",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/job_list_for_app"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "size",
            "description": "<p>条数</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "page",
            "description": "<p>页数</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "month",
            "description": "<p>月份分组</p>"
          },
          {
            "group": "Success 200",
            "type": "string[]",
            "optional": false,
            "field": "list",
            "description": "<p>列表</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "list.doc_type",
            "description": "<p>文案类型(1商品 2附件 3文本)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.doc_type_info",
            "description": "<p>文案类型(类型 11:讲座 12课程 13商品 14会员 15直播 16训练营21音频 22视频 23图片 31文本)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "0",
              "1",
              "2",
              "3",
              "4"
            ],
            "optional": false,
            "field": "list.is_done",
            "description": "<p>发送结果(1待发送  2发送中 3已完成 4无任务)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.status",
            "description": "<p>任务状态(1有效 2无效 3删除)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "allowedValues": [
              "1",
              "2",
              "3"
            ],
            "optional": false,
            "field": "list.send_obj_type",
            "description": "<p>发送目标类型(1群组 2个人 3标签)</p>"
          },
          {
            "group": "Success 200",
            "type": "number",
            "optional": false,
            "field": "list.send_obj_id",
            "description": "<p>发送目标id</p>"
          }
        ]
      }
    },
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "get",
    "url": "api/v4/im_doc/list",
    "title": "文案列表",
    "version": "4.0.0",
    "name": "api_v4_im_doc_list",
    "group": "社群文案",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/im_doc/list"
      }
    ],
    "description": "<p>文案列表</p>",
    "filename": "../app/Http/Controllers/Api/V4/ImDocController.php",
    "groupTitle": "社群文案"
  },
  {
    "type": "get",
    "url": "api/v4/notify/course",
    "title": "更新消息",
    "version": "4.0.0",
    "name": "course",
    "group": "通知",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/notify/systerm"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>消息类型标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subject",
            "description": "<p>消息标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "source_id",
            "description": "<p>来源id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "create_time",
            "description": "<p>时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": [\n             {\n                 \"subject\": \"您订阅的《王琨专栏》即将到期\",\n                 \"source_id\": 来源id,\n                 \"title\": \"过期提醒\",\n                 \"create_time\": \"1小时前\",\n             }\n         ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/NotifyController.php",
    "groupTitle": "通知"
  },
  {
    "type": "get",
    "url": "api/v4/notify/fans",
    "title": "新增粉丝",
    "version": "4.0.0",
    "name": "fans",
    "group": "通知",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/notify/fans"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "nickname",
            "description": "<p>用户昵称</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "from_uid",
            "description": "<p>用户id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "headimg",
            "description": "<p>用户头像</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": [\n             {\n                 \"from_uid\": 211185,\n                 \"to_uid\": 303681,\n                 \"nickname\": \"丹丹\",\n                 \"headimg\": \"http://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eq1iamPt3zKARVHsQMMqap77msicttX4libSBgCIgfrqumbm73uxwwlicAomRHCiawmNBd68TBicUh9IWGQ/132\",\n                 \"pivot\": {\n                     \"to_uid\": 303681,\n                     \"from_uid\": 211185\n                 },\n                 \"is_follow\": 1\n             }\n         ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/NotifyController.php",
    "groupTitle": "通知"
  },
  {
    "type": "get",
    "url": "api/v4/notify/list",
    "title": "消息通知",
    "version": "4.0.0",
    "name": "list",
    "group": "通知",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/notify/list"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "optional": false,
            "field": "type",
            "description": "<p>1.喜欢精选  2. 评论和@ 3更新消息 4.收益动态 5.系统消息</p>"
          },
          {
            "group": "Parameter",
            "optional": false,
            "field": "token",
            "description": "<p>用户认证</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subject",
            "description": "<p>标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "create_time",
            "description": "<p>时间</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "from_user",
            "description": "<p>用户相关</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n    \"data\": [\n         {\n            1.  subject 标题\n                create_time 时间\n                chapter  章节\n                from_user 用户相关\n            2.  subject 标题\n                content.summary  回复内容\n                from_user  用户相关\n                create_time 时间\n            3.  subject 标题\n                works  作品相关\n                works.cover_img 封面\n                works.title 标题\n            4.  subject 标题\n             content.price  价格\n             create_time  时间\n            5.  subject 标题\n             relation_type   5.到期提醒 6.订单提醒 7.审核提醒\n             create_time 时间\n         }\n     ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/NotifyController.php",
    "groupTitle": "通知"
  },
  {
    "type": "get",
    "url": "api/v4/user/notify_settings",
    "title": "用户通知设置",
    "version": "4.0.0",
    "name": "notify_settings",
    "group": "通知",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/user/notify_settings*"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "token",
            "description": "<p>当前用户</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "systerm",
            "description": "<p>系统消息</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "update",
            "description": "<p>更新消息</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": {\n\n   }\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/NotifyController.php",
    "groupTitle": "通知"
  },
  {
    "type": "POST",
    "url": "api/v4/notify/settings",
    "title": "更新通知设置",
    "version": "4.0.0",
    "name": "settings",
    "group": "通知",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/notify/settings"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>当前用户</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "is_comment",
            "description": "<p>是否评论   type=1</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "is_reply",
            "description": "<p>是否回复  0 否 1是  type=2</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "is_like",
            "description": "<p>是否精选 0 否 1是  type=3</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "is_fans",
            "description": "<p>是否粉丝  0 否 1是  type=5</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "is_income",
            "description": "<p>是否收益 0 否 1是  type=4</p>"
          },
          {
            "group": "Parameter",
            "type": "number",
            "optional": false,
            "field": "is_update",
            "description": "<p>是否更新 0 否 1是  type=6</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\":[\n\n    ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/NotifyController.php",
    "groupTitle": "通知"
  },
  {
    "type": "get",
    "url": "api/v4/notify/systerm",
    "title": "系统消息",
    "version": "4.0.0",
    "name": "systerm",
    "group": "通知",
    "sampleRequest": [
      {
        "url": "http://app.v4.api.nlsgapp.com/api/v4/notify/systerm"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": ""
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "title",
            "description": "<p>消息类型标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "subject",
            "description": "<p>消息标题</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "source_id",
            "description": "<p>来源id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "create_time",
            "description": "<p>时间</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "HTTP/1.1 200 OK\n{\n  \"code\": 200,\n  \"msg\" : '成功',\n  \"data\": [\n             {\n                 \"subject\": \"您订阅的《王琨专栏》即将到期\",\n                 \"source_id\": 来源id,\n                 \"title\": \"过期提醒\",\n                 \"create_time\": \"1小时前\",\n             }\n         ]\n}",
          "type": "json"
        }
      ]
    },
    "filename": "../app/Http/Controllers/Api/V4/NotifyController.php",
    "groupTitle": "通知"
  }
] });
