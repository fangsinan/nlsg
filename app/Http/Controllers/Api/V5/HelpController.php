<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\FeedbackNew;
use App\Models\FeedbackTarget;
use App\Models\FeedbackType;
use App\Models\HelpAnswer;
use App\Models\Talk;
use App\Models\TalkList;
use App\Models\TalkUserStatistics;
use Illuminate\Http\Request;

class HelpController extends Controller
{


    /**
     *
     * getHelpLists  获取问答帮助列表
     *  {get}  /api/v5/help/get_help_list
     */
    function getHelpLists(Request $request){

        $question = $request->input("question");
        if(empty($question)){
            return $this->error(0,'参数错误',[]);
        }
        $answers = HelpAnswer::GetAnswersByQuestion($question);
        $returns = [];
        $message = [];   //多个问答的结构


        foreach($answers['list'] as $val){
            if($answers['is_show_qr'] == 1){
                if(!empty($val['qr_code'])){
                    //带图片的结构
                    $returns[] = [
                        "qr_code"=>$val['qr_code'],
                        "message"=>[],
                    ];
                }
            }
            $message[] =  [
                "question"  =>  $val['question'],
                "answer"    =>  $val['answer'],
            ];
        }
        if(empty($message)) $no_answer = "很抱歉，暂未识别出您的问题，您可以选择留言，客服看到后会及时为您解答 点击留言";
        $returns[] =[
            "qr_code"=>"",
            "no_answer" => $no_answer??"",
            "message"=>$message
        ];

        return $this->success($returns);
    }



    /**
     * sendMessage  客户发送留言
     * {get}  /api/v5/help/send_message?message=解决问题&user_id=211172
     */
    function sendMessage(Request $request){


        $message = $request->input("message");
        $image = $request->input("image");
        // $uid = $this->user['id'];
        $uid = $request->input("user_id");
        if( (empty($image) && empty($message)) || empty($uid)){
            return $this->error(0,'参数错误',[]);
        }
        //处理会话id
        $talk_id = Talk::getTalkId($uid);

        $list = TalkList::where("user_id",$uid)->orderBy("id",'desc')->first();
        $add_type_3_line = date('Y-m-d H:i:s', strtotime('-5 minute'));

        if(!empty($list) && $list->type!==3 && $list->created_at <= $add_type_3_line ){
            TalkList::insert([
                "talk_id"    => $talk_id,
                "type"       => 3,
                "user_id"    => $uid,
                "content"    => date('Y-m-d H:i'),
            ]);
        }

        if (empty($list)){
            TalkList::insert([
                "talk_id"    => $talk_id,
                "type"       => 3,
                "user_id"    => $uid,
                "content"    => date('Y-m-d H:i'),
            ]);
        }


        TalkList::insert([
            "talk_id"    => $talk_id,
            "type"       => 1,
            "user_id"    => $uid,
            "content"    => $message ??'',
            "image"      => $image??'',
        ]);
        // 统计次数 num
        TalkUserStatistics::msgCount($uid);

        return $this->success();
    }




    /**
     * getHelpMessage  获取留言message对话列表
     * {get} /api/v5/help/get_message?user_id=211172
     */
    function getMessage(Request $request){


        // $uid = $this->user['id'];
        $uid = $request->input("user_id");
        $list = TalkList::GetListByUserId($uid);
        return $this->success($list);
    }




    /**
     * delMessage  客户清空留言
     * {get} /api/v5/help/get_message?user_id=211172
     */
    function delMessage(Request $request){

        // $uid = $this->user['id'];
        $uid = $request->input("user_id");
        TalkList::where(["user_id"=>$uid,'status'=>1])->update([
            'status' =>2
        ]);
        return $this->success();
    }



    /**
     * /api/v5/help/get_feedback_type  提意见类型
     */
    function getFeedBackType(){
        return $this->success(FeedbackType::getFeedbackType(1));

        // $types =  FeedbackType::where(['type'=>1])->get();
        // return $this->success($types);
    }


    /**
     *  {get} api/v4/user/feedback 我要吐槽
     * @apiVersion 4.0.0
     * @apiParam {string} type 10:使用建议 11:内容漏缺 12:购物相关 13:物流配送 14:客服体验 15:节约相关
     * @apiParam {string} content 内容  不能大于200字
     * @apiParam {string} pic  图片url(数组格式)
     * @apiGroup Api
     */
    public function feedback(Request $request)
    {
        $input = $request->all();
        if (!$input['content']) {
            return $this->error(1000, '描述不能为空');
        }
        if( !empty($input['pic']) ){
            $pics  = explode(',', $input['pic']);
            if (count($pics) > 9) {
                return $this->error(1000,'图片过多');
            }
        }
//        $res = FeedBack::create([
//            'type' => $input['type'],
//            'user_id' => $this->user['id']??0,
//            'content' => $input['content'],
//            'pic' => $input['pic']
//        ]);

        $res = FeedbackNew::query()
            ->create([
                'type'    => $input['type'],
                'user_id' => $this->user['id'],
                'os_type' => $input['os_type'],
                'content' => $input['content'],
                'picture' => $input['pic']
            ]);
        if ($res) {
            return $this->success();
        }

    }

    /**
     * /api/v5/help/get_report_type  提意见类型
     */
    function getReportType(){
        return $this->success(FeedbackType::getFeedbackType(3));
    }
    /**
     *  {get} api/v5/help/report 举报功能
     * @apiVersion 4.0.0
     * @apiParam {string} type help/get_report_type 获取到的id
     */
    public function report(Request $request)
    {
        $input = $request->all();
        if (empty($input['live_id'])) {
            return $this->error(1000, '直播ID不能为空');
        }

        if (empty($input['type'])) {
            return $this->error(1000, '举报类型不能为空');
        }

        if (empty($input['user_id'])) {
            return $this->error(1000, '举报用户不能为空');
        }

        if (empty($input['live_comment'])) {
            return $this->error(1000, '举报评论不能为空');
        }

        $edit = [
            'type'    => $input['type'],
            'user_id' => $this->user['id']??211172,
            'os_type' => $input['os_type']??1,
            'picture' => $input['pic']??'',
            'live_id' => $input['live_id'],
            'content' => $input['content']??'',
        ];

        $target_id = FeedbackTarget::insertGetId([
            'type'          => 1,
            'live_id'       => $input['live_id'],
            'target_id'     => $input['user_id'],
            'comment'       => $input['live_comment'],
        ]);

        $edit['target'] = $target_id;

        FeedbackNew::create($edit);

        return $this->success();
    }

}
