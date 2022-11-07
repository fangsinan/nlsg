<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\HelpAnswer;
use App\Models\Message\Message;
use App\Models\Talk;
use App\Models\TalkList;
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
        if(empty($message)) $no_answer = "未匹配到";
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
        // $uid = $this->user['id'];
        $uid = $request->input("user_id");
        if( empty($message) || empty($uid)){
            return $this->error(0,'参数错误',[]);
        }

        //处理会话id
        $talk_id = Talk::getTalkId($uid);

        TalkList::insert([
            "talk_id"    => $talk_id,
            "type"       => 1,
            "user_id"    => $uid,
            "content"    => $message,
        ]);
        //发送通知
        Message::pushMessage(0,$uid,'SYS_USER_SEND_HELP',[]);
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
        TalkList::where(["user_id",$uid,'status'=>1])->update([
            'status' =>2
        ]);
        return $this->success();
    }


}
