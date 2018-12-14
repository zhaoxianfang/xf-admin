<?php
namespace app\index\controller;


class Index extends IndexBase
{
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
        //获取权限列表
        return $this->fetch();
    }
}
