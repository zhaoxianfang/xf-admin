<?php
namespace app\callback\controller;

use app\common\controller\Base;
use zxf\sina\SaeTOAuthV2;
use zxf\sina\SaeTClientV2;

/**
 * 新浪微博 登录
 */
class Sina extends Base
{

    public function index()
    {
        die('非法请求');
    }

    /**
     * 微博回调
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $value [description]
     */
    public function callback()
    {
        
        if (!isset($_REQUEST['code'])) {
            return $this->error('非法请求');
        }

        $wbAkey = config('callback.sina.wb_akey');
        $wbSkey = config('callback.sina.wb_skey');
        $wbCallbackUrl = config('callback.sina.wb_callback_url');

        $o = new SaeTOAuthV2( $wbAkey , $wbSkey );
        $keys = array();
        $keys['code'] = $_REQUEST['code'];
        $keys['redirect_uri'] = $wbCallbackUrl;
        try {
            $token = $o->getAccessToken( 'code', $keys ) ;
        } catch (\Exception $e) {
            return $this->error("获取AccessToken失败.");
        }
        if(!$token){
            return $this->error('授权失败');
        }

        try {
            $c = new SaeTClientV2( $wbAkey , $wbSkey , $token['access_token'] );
            $ms  = $c->home_timeline(); // done
            $uid_get = $c->get_uid();
            $uid = $uid_get['uid'];
            $user_message = $c->show_user_by_id( $uid);//根据ID获取用户等基本信息
        } catch (\Exception $e) {
            return $this->error('出错啦: ' . $e->getMessage());
        }
        $loginModel = session('sina_login_model');
        // 拿到用户信息后的处理
        // 快速登录
        $loginUserInfo = $this->logicUser->fastLogin($uid, $user_message, 'sina',$loginModel);
        //回调地址
        $callUrl = session('sina_callback');
        if($callUrl){
            $this->redirect($callUrl);
        }
        return json(['msg'=>'登录成功','code'=>0,'data'=>$loginUserInfo]);
        
    }

    /**
     * 登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $jumpUrl      [登录成功后跳转地址]
     * @param    string       $loginModel   [登录作用的模块、不同模块session名称不同]
     * @return   [type]                     [description]
     */
    public function login($jumpUrl = '',$loginModel='index')
    {
        try {
            $wbAkey = config('callback.sina.wb_akey');
            $wbSkey = config('callback.sina.wb_skey');
            $wbCallbackUrl = config('callback.sina.wb_callback_url');

            $o = new SaeTOAuthV2( $wbAkey , $wbSkey );

            $code_url = $o->getAuthorizeURL( $wbCallbackUrl );
        } catch (\Exception $e) {

            return $this->error('出错啦: ' . $e->getMessage());
        }
        if($jumpUrl){
            session('sina_callback',$jumpUrl);
            session('sina_login_model',$loginModel);
        }
        //跳转到授权页面
        $this->redirect($code_url);
        
        
    }

    // 回调函数
    public function unauth()
    {
        die("取消授权");
    }
}
