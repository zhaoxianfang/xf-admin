<?php
// +---------------------------------------------------------------------
// | 后台首页
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller;

use app\common\elastic\service\Doc;
use think\facade\Cache;
use think\facade\Env;
use util\Page;

class Example extends AdminBase
{
    protected $noNeedRight       = [''];
    protected $noNeedLogin       = [''];
    protected $showContentHeader = false;

    /**
     * 基础控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $this->title = '其他功能模块';
        return $this->fetch();
    }
}
