<?php

namespace Itsm\Http\Middleware;

use Itsm\Model\Auth\Authorities;
use Itsm\Model\Usercenter\Userlogin;
use Itsm\Model\Usercenter\UserLoginHis;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CAS {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null) {
        $casUser = $request->session()->get('cas_user');
        $user = $request->session()->get('user');

        if (empty($user)) {
            if (!$casUser) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['error' => 'Unauthorized.',], 401);
                }
                /** @var \phpCAS $cas */
                $cas = app('CAS');
                if (!$cas->isAuthenticated()) {
                    $cas->forceAuthentication();
                }

                $casUser = $cas->getUser();

                //添加登录记录
                $memo = "{ContextPath:" . app_path() . ",ServletPath:/home}" . "browser:" . $request->header('user-agent');
                UserLoginHis::insert([
                    'LoginId' => $casUser,
                    'LoginIP' => $request->ip(),
                    'LoginTs' => date('Y-m-d H:i:s'),
                    'LoginSource' => 'itsm',
                    'memo' => $memo
                ]);
            }

            //wangcq 2016-05-05 添加登陆客户session
            $user = UserLogin::where('userlogin.LoginId', $casUser)
                    ->leftJoin('cuslogin', 'userlogin.Id', '=', 'cuslogin.UserLoginId')
                    ->where('userlogin.Disabled', Userlogin::DISABLED_NO)//判断是非禁止登陆客户
                    ->first(['userlogin.*', 'cuslogin.CusInfId']);
            if ($user && $user->Id < 500000) { //判断登陆人是不是员工
                $request->session()->put('user', $user);
                //获取当前登陆账号的权限
                $authRoleList = Authorities::where('authorities.username', $casUser)
                        ->select('authorities.*')
                        ->orderBy('authorities.id', 'desc');
                $authRoleList = $authRoleList->paginate(1000);
                $request->session()->put('authRoleList', $authRoleList);
            } else {
                $request->session()->flush();
                $request->session()->regenerate(true);

                /** @var \phpCAS $cas */
                $cas = app('CAS');
                $cas->logout(["service" => env("APP_URL")]);
            }
        }
        if (!empty($user)) {//添加登录人员缓存，记录在线状态
            $expiresAt = Carbon::now()->addMinutes(10);
            $userid = $user->Id;
            $cachekey = ITSM_LOGIN . $userid;
            $list = [
                "userid" => $userid,//登录用户id
                "lastLoginTime" => time()//最后登录时间
            ];
            $list = json_encode($list);
            Cache::put($cachekey, $list, $expiresAt);
        }
        view()->share('username', $casUser);

        return $next($request);
    }

}
