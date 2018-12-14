<?php
// +---------------------------------------------------------------------
// | 系统公共基础类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\controller;

use think\Controller;
use think\facade\Config;
use think\facade\Request;
use util\Random;

// use util\Auth;

class Base extends Controller
{
    /**
     * 权限控制类
     * @var Auth
     */
    protected $auth = null;
    /**
     * 布局模板
     * @var string
     */
    protected $layout = 'default';

    /**
     * 请求信息
     * @var \think\RequestRequest实例
     */
    protected $request;

    /**
     * 基础控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();

        // $this->auth = Auth::instance();
        $this->auth    = $this->logicAuth::instance();
        $this->request = Request::instance();

        // 初始化请求信息
        $this->initRequestInfo();

        // 初始化全局静态资源
        $this->initCommonStatic();

        // 初始化响应类型
        $this->initResponseType();

        // 如果有使用模板布局
        !empty($this->layout) && $this->setLayout();

    }

    /**
     * 初始化请求信息
     */
    final private function initRequestInfo()
    {
        date_default_timezone_set('PRC'); //设置中国时区

        defined('IS_POST') or define('IS_POST', $this->request->isPost());
        defined('IS_GET') or define('IS_GET', $this->request->isGet());
        defined('IS_AJAX') or define('IS_AJAX', $this->request->isAjax());
        defined('IS_PJAX') or define('IS_PJAX', $this->request->isPjax());
        defined('IS_MOBILE') or define('IS_MOBILE', $this->request->isMobile());
        defined('MODULE_NAME') or define('MODULE_NAME', strtolower($this->request->module()));
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', strtolower($this->request->controller()));
        defined('ACTION_NAME') or define('ACTION_NAME', strtolower($this->request->action()));
        defined('URL_TRUE') or define('URL_TRUE', $this->request->url(true));
        defined('DOMAIN') or define('DOMAIN', $this->request->domain());
        defined('URL_ROOT') or define('URL_ROOT', $this->request->root());

        $this->param = $this->request->param();
    }

    /**
     * 获取逻辑层实例  重写获取器
     */
    public function __get($name)
    {
        $layer = str_prefix($name, 'logic');

        if (false === $layer) {
            try {
                return $this->$name;
            } catch (\Exception $e) {
                return new \think\exception\ThrowableError($e);
            }
        } else {
            return model(sr($name, 'logic'), 'logic');
        }
    }

    /**
     * 系统通用跳转
     */
    final protected function jump($jump_type = null, $message = null, $url = null)
    {
        try {
            if (is_bool($jump_type) || (is_numeric($jump_type) && empty($message))) {
                $data = ($jump_type === true || $jump_type >= 0) ? ['jump_type' => 10, 'message' => '操作成功', 'url' => ''] : ['jump_type' => 11, 'message' => '操作失败', 'url' => ''];
            } else {
                $data = is_array($jump_type) ? $this->parseJumpArray($jump_type) : $this->parseJumpArray([$jump_type, $message, $url]);
            }

            $success  = 0; //成功
            $error    = 1; //失败
            $redirect = -1; //重定向跳转

            $success_close = 10; //成功 并关闭
            $error_close   = 11; //失败 并关闭

            $u = 'url';
            $m = 'message';

            !empty($data[$u]) && $data[$u] = $this->request->domain() . $data[$u];

            switch ($data['jump_type']) {
                // case $success:$this->success($data[$m], $data[$u]);
                case $success:$this->success($data[$m], null);
                    break;
                case $error:$this->error($data[$m], $data[$u]);
                    break;
                case $redirect:$this->redirect($data[$u]);
                    break;
                case $success_close:$this->success($data[$m], $data[$u], ['close' => 1]);
                    break;
                case $error_close:$this->error($data[$m], $data[$u], ['close' => 1]);
                    break;
                default:intval($data['jump_type']) > 0 ? $this->success('操作成功', '', ['close' => 1]) : exception('系统跳转失败:' . $data[$u]);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 解析跳转数组
     */
    final protected function parseJumpArray($jump_array = [])
    {
        (isset($jump_array[0]) && !isset($jump_array[1])) && ($jump_array[1] = ($jump_array[0] == 1) ? '操作失败' : '操作成功');
        (!isset($jump_array[2]) || empty($jump_array[2])) && $jump_array[2] = null;
        return ['jump_type' => $jump_array[0], 'message' => $jump_array[1], 'url' => $jump_array[2]];
    }

    /**
     * 初始化响应类型
     */
    final private function initResponseType()
    {

        IS_AJAX && !IS_PJAX ? config('default_ajax_return', 'json') : config('default_ajax_return', 'html');
    }

    /**
     * 初始化全局静态资源
     */
    final protected function initCommonStatic()
    {

        $this->assign('loading_icon', config('sys.loading_icon'));

        $this->assign('pjax_mode', config('sys.pjax_mode'));
    }

    /**
     * 设置模板布局
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-26
     */
    final protected function setLayout()
    {
        $this->view->engine->layout('layout/' . $this->layout);

    }
    /**
     * 启用 layout
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-26
     * @param    boolean      $flag [默认关闭]
     * @return   [type]             [description]
     */
    public function onLayout($flag = false)
    {
        $this->view->engine->layout($flag);
    }
}
