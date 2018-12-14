<?php
// +----------------------------------------------------------------------
// | API 列表
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\controller;

use think\Hook;
use think\facade\Session;
use think\facade\Config;

class Index extends ApiBase
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 后台首页  主要操作菜单模块
     * @Author   ZhaoXianFang
     * @DateTime 2018-02-27
     * @return   [type]       [description]
     */
    public function index()
    {
        //app 列表
        $appList = $this->logicApiApp->getList();
        $this->view->assign('app_list',$appList);
        return $this->fetch();
    }
	

}
