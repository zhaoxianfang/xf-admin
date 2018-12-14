<?php
namespace app\callback\controller;

use app\common\controller\Base;
use think\facade\Log;
/**
 * 微信接入
 */
class Wechat extends Base
{
    /**
     * Wechat类
     * @var Auth
     */
    protected $wechat = null;
    protected $config;

    public function initialize()
    {
        parent::initialize();

        // $config = config('callback.wechat');
        // // dump($this->config);die;
        // $this->wechat = new \zxf\WeChat\Receive($config);
        // die('d');
        // $this->wechat = new \zxf\wechat\Wechat($config);

        $this->config = config('callback.wechat');

    }
    public function test()
    {
        $redirect_uri ='https://www.itzxf.com/callback/wechat/getuserinfo';
        $url = '//https://open.weixin.qq.com/connect/qrconnect?appid=wx8229750d9939c3dc&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope=snsapi_login&state=3d6be0a4035d839573b04816624a415e#wechat_redirect';
        $this->redirect($url,302);
    }

    /**
     * 微信回复
     * @Author   ZhaoxianFang
     * @DateTime 2017-05-17
     * @return   function     [description]
     */
    public function index()
    {
        $config = config('callback.wechat');
        // // dump($this->config);die;
        try {
            $this->wechat = new \WeChat\Receive($config);

            // 实例接口，同时实现接口配置验证与解密处理
            $api = $this->wechat;

            // 获取当前推送接口类型 ( text,image,loction,event... )
            // $msgType = $api->getMsgType();

            // 获取当前推送来源用户的openid
            $openid = $api->getOpenid();

            // 获取当前推送的所有数据
            try {
                $content = $api->getReceive('Content');
                // 回复文本消息
                if ($content) {
                    $api->text('你发送了:' . $content)->reply();
                }
            } catch (\Exception $e) {
                $api->text('系统出错了:' . $e->getMessage())->reply();
            }
            // Log::record('测试日志信息---------------------',json_encode($data));

            // $content = '测试回复';
            // // 回复文本消息
            // $api->text($content)->reply();

            // 回复图文消息（高级图文或普通图文，数组）
            // $api->news($news)->reply();

            // 回复图片消息（需先上传到微信服务器生成 media_id）
            // $api->image($media_id)->reply();

            // 回复语音消息（需先上传到微信服务器生成 media_id）
            // $api->voice($media_id)->reply();

            // 回复视频消息（需先上传到微信服务器生成 media_id）
            // $api->video($media_id, $title, $desc)->reply();

            // 回复音乐消息
            // $api->music($title, $desc, $musicUrl, $hgMusicUrl, $thumbe)->reply();

            // 将消息转发给多客服务
            // $api->transferCustomerService($account)->reply();

        } catch (\Exception $e) {
            // 处理异常
            echo $e->getMessage();
        }
    }

    //判断是否为微信浏览器
    public function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) { 
            return true; 
        } return false; 
    }

    /**
     * 登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $jumpUrl      [登录成功后跳转地址]
     * @param    string       $loginModel   [登录作用的模块、不同模块session名称不同]
     * @return   [type]                     [description]
     */
    public function login($jumpUrl = '', $loginModel = 'index')
    {
        if($this->is_weixin()){
            session('weixn_login_model',$loginModel);
            session('weixn_callback',$jumpUrl);
            $this->OauthJump();
        }else{
            //https://open.weixin.qq.com/connect/qrconnect?appid=wxbdc5610cc59c1631&redirect_uri=https%3A%2F%2Fpassport.yhd.com%2Fwechat%2Fcallback.do&response_type=code&scope=snsapi_login&state=3d6be0a4035d839573b04816624a415e#wechat_redirect
            echo '请在微信中使用微信登录功能';
        }
    }

    //Oauth授权跳转接口
    // @param string $redirect_url 授权回跳地址
    // @param string $state 为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
    // @param string $scope 授权类类型(可选值snsapi_base|snsapi_userinfo)
    public function OauthJump()
    {
        try {

            // 实例接口
            $wechat = new \WeChat\Oauth($this->config);
            $scope = 'snsapi_userinfo';
            $length =15;
            $state = substr(md5(time()), 0, $length);//md5加密，time()当前时间戳  
            // $redirect_url = 'https://www.itzxf.com/callback/wechat/getuserinfo';
            $redirect_url = 'https://www.itzxf.com/callback/wechat/getUser';
            // 执行操作
            $result = $wechat->getOauthRedirect($redirect_url, $state, $scope);
            
            $this->redirect($result,302);
        } catch (Exception $e){

            // 异常处理
            echo  $e->getMessage();
            
        }
    }

    public function getUser()
    {
        try {
            // 实例接口
            $wechat = new \WeChat\Oauth($this->config);

            //一、获取 access_token、openid
            // 执行操作
            $result = $wechat->getOauthAccessToken();
            
            //二、 获取用户信息
            $lang = 'zh_CN';
            // 执行操作
            $user = $wechat->getUserInfo($result['access_token'],$result['openid'], $lang);
            
        } catch (Exception $e){

            // 异常处理
            echo  $e->getMessage();
            
        }
        
        // 拿到用户信息后的处理
        //回调地址
        $loginModel = session('weixn_login_model');
        $callUrl = session('weixn_callback');
        // dump($loginModel);
        // dump($callUrl);
        // die;
        // 快速登录
        $loginUserInfo = $this->logicUser->fastLogin($user['openid'], $user, 'weixin',$loginModel);
        if($callUrl){
            $this->redirect($callUrl);
        }
        return json(['msg'=>'登录成功','code'=>0,'data'=>$loginUserInfo]);
    }
    

    public function getuserinfo()
    {
        $param = $this->request->param();
        dump($param );
    }

}
