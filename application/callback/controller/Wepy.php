<?php
/**
 * Created by PhpStorm.
 * User: ZhaoXianFang
 * Date: 2018/9/11
 * Time: 9:24
 */



use app\common\controller\Base;
use think\facade\Log;
/**
 * 微信接入
 */
class Wepy extends Base
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
        $this->request->param('');
        echo 'hello';
    }



}
