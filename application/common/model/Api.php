<?php
// +---------------------------------------------------------------------
// | 
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\model;

/**
 * Api模型
 */
class Api extends Base
{
	// 设置当前模型对应的完整数据表名称
    protected $name = 'api_list';
	/**
	 * 设置模块存储的名称（session前缀）
	 * @Author   ZhaoXianFang
	 * @DateTime 2018-10-16
	 * @return   [type]       [description]
	 */
    public function getSessionPrefix()
    {
        return 'api';
    }

}
