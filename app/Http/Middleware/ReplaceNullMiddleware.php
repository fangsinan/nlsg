<?php

namespace App\Http\Middleware;

use Closure;

class ReplaceNullMiddleware
{
    
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $data = json_decode($response->getContent(), true);
        if(isset($data['data'])){
            $newData = $this->unsetNull($data['data']);
            $data['data'] = $newData;
            return $response->setContent(json_encode($data));
        }
        return $response;
    }




    //递归方式把数组或字符串 null转换为空''字符串
    function unsetNull($arr){
        if($arr !== null){
            if(is_array($arr)){
                if(!empty($arr)){
                    foreach($arr as $key => $value){
                        if($value === null){
                            $arr[$key] = '';
                        }else{
                            $arr[$key] = $this->unsetNull($value);      //递归再去执行
                        }
                    }
                }
            }else{
                if($arr === null){ $arr = ''; }         //注意三个等号
            }
        }else{ $arr = ''; }
        return $arr;
    }
}