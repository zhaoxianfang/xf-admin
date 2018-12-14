<?php
// +---------------------------------------------------------------------
// | 后台逻辑基础类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\logic;

use app\common\logic\Base;
use think\facade\Hook;

/**
 * Admin基础逻辑
 */
class AdminLogic extends Base
{
    protected $sessionPrefix = '';

    // 模型初始化
    public function initialize()
    {
        $this->sessionPrefix = $this->logicAuth->getSessionPrefix();
        //TODO:初始化内容
    }
    /**
     * 权限检测
     */
    public function authCheck($noNeedLogin = [], $noNeedRight = [])
    {
        $path = str_replace('.', '/', CONTROLLER_NAME) . '/' . ACTION_NAME;

        if (!$this->logicAuth->match($noNeedLogin)) {
            //检测是否登录
            if (!$this->logicAuth->isLogin()) {
                Hook::listen('admin_nologin', $this);
                return [1, '请先登录'];
            }
            // 判断是否需要验证权限
            if (!$this->logicAuth->match($noNeedRight)) {
                // 判断控制器和方法判断是否有对应权限
                if (!$this->logicAuth->check($path)) {
                    Hook::listen('admin_nopermission', $this);
                    return [1, '无权访问'];
                }
            }
        }
        return [0, '验证通过'];

    }

}
