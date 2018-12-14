<?php
// +----------------------------------------------------------------------
// | API 请求错误
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\facade\Hook;
use think\Request;

/**
 * 接口请求错误
 */
class Error
{
    public function index(Request $request)
    {
        $this->apiRootErr($request);
        $controller = $request->controller();
        $msg        = 'Interface request address error';
        if ('index' == strtolower($controller)) {
            $msg = 'No choice of application';
        }
        return json(['code' => 500, 'msg' => $msg]);
    }

    /**
     * 空方法 没有定义控制器，也没有定义方法
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @param    [type]       $name    [description]
     * @param    Request      $request [description]
     * @return   [type]                [description]
     */
    public function _empty($name, Request $request)
    {
        $this->apiRootErr($request);
        return json(['code' => 500, 'msg' => 'interface no found']);
    }

    /**
     * Api 路由请求错误
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @param    [type]       $request [description]
     * @return   [type]                [description]
     */
    public function apiRootErr($request)
    {
        Hook::listen('api_root_err', $request);
    }
}
