<?php
// +----------------------------------------------------------------------
// | 数据同步管理
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-11-07
// +----------------------------------------------------------------------

namespace app\api\controller\sync;
use app\api\controller\ApiBase;


/**
 * 数据同步管理
 */
class Index extends ApiBase
{

    protected $model            = null;

    public function _initialize()
    {
    	parent::_initialize();
    }

    public function index()
    {
    	return $this->apiReturn(['code'=>0,'msg'=>'This is a test interface']);
    }

}