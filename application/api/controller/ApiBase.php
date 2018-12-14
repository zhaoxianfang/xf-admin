<?php
// +----------------------------------------------------------------------
// | API 基础控制
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Controller;
// use app\api\library\Auth;
use app\common\controller\Base;

class ApiBase extends Base
{

    // 请求参数
    protected $param;
    // 权限
    protected $auth;
    protected $token_user_id = 0; // 如果接口是通过token来解析user_id才有值

    public function initialize()
    {
        parent::initialize();
        
        $this->view->engine->layout(false);

        debug('api_begin');
        // 定义是否AJAX请求
        !defined('IS_AJAX') && define('IS_AJAX', $this->request->isAjax());

        $this->logicApiBase->checkParam($this->param);
        
    }

    /**
     * 基类初始化
     */
    public function __construct()
    {
        parent::__construct();
        $this->param         = $this->request->param();

        if(isset($this->param['token_user_id'])){
            $this->token_user_id = $this->param['token_user_id'];
        }
        $this->view->assign('sys_title', '文明网');

    }

    /**
     * 空方法 定义了控制器但是没有定义方法
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @param    [type]       $name    [description]
     * @param    Request      $request [description]
     * @return   [type]                [description]
     */
    public function _empty()
    {
        return $this->logicApiBase->apiReturn(['code' => 500, 'msg' => 'No interface found.']);
    }

    /**
     * API返回数据
     */
    public function apiReturn($code_data = [], $return_data = [], $return_type = 'json')
    {

        return $this->logicApiBase->apiReturn($code_data, $return_data, $return_type);
    }

    /**
     * 获取逻辑层实例
     */
    public function __get($name)
    {
        if (!str_prefix($name, 'logic')) {
            try {
                return $this->$name;
            } catch (\Exception $e) {
                return new \think\exception\ThrowableError($e);
            }
        }
        return model(sr($name, 'logic'), 'logic');
    }

}
