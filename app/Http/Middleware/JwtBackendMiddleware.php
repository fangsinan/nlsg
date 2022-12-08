<?php


namespace App\Http\Middleware;

use Closure;
use JWTAuth;

class JwtBackendMiddleware
{
    public function handle($request, Closure $next)
    {

        $token     = $request->header('authorization');
        $url_token = $request->input('token', '');
        if (empty($token) && !empty($url_token)) {
            $request->headers->set('Authorization', 'Bearer ' . $url_token);
        }

        if (auth('backendApi')->check()) {
            //$route= Route::current();
            //$route->uri; 当前路由
            //$route->controller->user; 当前用户信息

            return $next($request);
        } else {
            return response()->json(['msg' => '没有登录', 'code' => 401]);
        }


//        dd(auth('backendApi')->user());
//        dd(auth('backendApi')->check());
//
//        try {
//            $user = JWTAuth::parseToken()->authenticate();
//            dd($user->toArray());
//            $check_backend = BackendUser::where('username','=',$user->phone)->first();
//            if (empty($check_backend)){
//                return response()->json(['msg' => '无权限', 'code'=>401]);
//            }
//        } catch (Exception $e) {
//            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
//                return response()->json(['msg' => 'TOKEN无效','code'=>401]);
//            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
//
//                return response()->json(['msg' => 'TOKEN已过期', 'code'=>401]);
//            } else{
//                return response()->json(['msg' => '没有登录','code'=> 401]);
//            }
//        }
//        return $next($request);
    }
}
