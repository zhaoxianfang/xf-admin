<?php
namespace app\callback\controller;

use app\common\controller\Base;
use zxf\Qqlogin\QC;

/**
 * 腾讯QQ 登录
 */
class Tencent extends Base
{
    public function index()
    {
        die('非法请求');
    }

    /**
     * 处理qq登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @param    string       $jumpUrl      [登录完成后跳转的地址]
     * @param    string       $loginModel   [登录作用的模块、不同模块session名称不同]
     * @return   [type]                     [description]
     */
    public function login($jumpUrl = '', $loginModel = 'index')
    {
        try {
            $qq  = new QC(config('callback.qq'));
            $url = $qq->qq_login();
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        if ($jumpUrl) {
            session('qq_callback', $jumpUrl);
            session('qq_login_model', $loginModel);
        }
        $this->redirect($url);
    }

    // qq登录回调函数
    public function callback()
    {
        try {
            $qq = new QC(config('callback.qq'));
            $qq->qq_callback();
            $openId = $qq->get_openid();
            $qq     = new QC(config('callback.qq'));
            $data   = $qq->get_user_info();
        } catch (\Exception $e) {

            return $this->error('出错啦: ' . $e->getMessage());
        }
        $loginModel = session('qq_login_model') ? session('qq_login_model') : 'admin';
        // 拿到用户信息后的处理
        // 快速登录
        $loginUserInfo = $this->logicUser->fastLogin($openId, $data, 'qq', $loginModel);
        //回调地址
        $callUrl = session('qq_callback') ? session('qq_callback') : url('admin/index/index');
        if ($callUrl) {
            $this->redirect($callUrl);
        }
        return json(['msg' => '登录成功', 'code' => 0, 'data' => $loginUserInfo]);
    }
}
