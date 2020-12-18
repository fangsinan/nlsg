<?php


namespace App\Http\Middleware;
use App\Models\BackendUser;
use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtBackendMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
//            dd($user->toArray());
//            $check_backend = BackendUser::where('username','=',$user->phone)->first();
//            if (empty($check_backend)){
//                return response()->json(['msg' => '无权限', 'code'=>401]);
//            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['msg' => 'TOKEN无效','code'=>401]);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){

                return response()->json(['msg' => 'TOKEN已过期', 'code'=>401]);
            } else{
                return response()->json(['msg' => '没有登录','code'=> 401]);
            }
        }
        return $next($request);
    }
}
