<?php
// +---------------------------------------------------------------------
// | 后台登录
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller;


use think\facade\Cookie;

class Login extends AdminBase
{
    protected $noNeedRight = ['*'];
    protected $noNeedLogin = ['*'];

    protected $layout = 'login';

    public function index()
    {

        // dump(config());die;

        // Log::setTitle('写入日志');
        // echo User::get(1);
        // echo Admin::get(1);die;
        // // echo User::hello('d');
        // return $this->fetch();

        //已经登录
        if ($this->logicAuth->isLogin()) {
            $this->redirect('index/index');

        }
        if ($this->request->isPost()) {

            $username = $this->request->param('username', '');
            $password = $this->request->param('password', '');
            $verify   = $this->request->param('verify', '');
            $remember = $this->request->param('remember', '') ? true : false;

            $this->jump($this->logicLogin->loginHandle($username, $password, $verify, $remember));
        }
        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->logicLogin->autologin()) {
            $this->redirect('index/index');
        }
        return $this->fetch();
    }

    //退出
    public function logout()
    {
        $this->jump($this->logicLogin->logout());
    }

    // 处理qq登录
    public function qqlogin()
    {
        $qq = controller('callback/Tencent');
        $qq->login(url('index/index'), 'admin'); //qq登录

    }

    //微博登录
    public function sinalogin()
    {
        $sina = controller('callback/Sina');
        $sina->login(url('index/index'), 'admin'); //微博登录

    }

    //微信登录
    public function wechatlogin()
    {
        $wechat = controller('callback/Wechat');
        $wechat->login(url('index/index'), 'admin'); //微博登录

    }

}
