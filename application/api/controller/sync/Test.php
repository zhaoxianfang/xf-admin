<?php
// +----------------------------------------------------------------------
// | 测试
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
 *  测试
 */
class Test extends ApiBase
{

    protected $model            = null;

    public function initialize()
    {
    	parent::initialize();
    	// dump(ACTION_NAME);
    	// dump(CONTROLLER_NAME);
    	// die;
    }

    public function index()
    {
    	return $this->apiReturn();
    }

    public function addAll()
    {
        db('temp')->insert(['content'=>json_encode($this->param)]);
    	db('temp')->insert(['content'=>json_encode($this->request)]);
    	return $this->apiReturn();
    }

}