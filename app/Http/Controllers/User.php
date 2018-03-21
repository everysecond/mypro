<?php
/**
 * support
 *
 * User: Wudi<wudi@51idc.com>
 * Date: 16/5/4 17:10
 */

namespace Itsm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class User extends Controller
{
    /**
     * 用户登出
     *
     * @param Request $req
     * @param Response $res
     */
    public function logout(Request $req, Response $res)
    {
        $req->session()->flush();
        $req->session()->regenerate(true);

        /** @var \phpCAS $cas */
        $cas = app('CAS');
        $cas->logout(["service" => env("APP_URL")]);
    }

}