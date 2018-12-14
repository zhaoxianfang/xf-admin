<?php
// +---------------------------------------------------------------------
// | 系统初始化加载
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\behavior;
use app\facade\Log;
/**
 * 应用结束行为
 */
class AppEnd
{

    /**
     * 行为入口
     */
    public function run()
    {
    	//写入日志
        if (request()->isPost() || Log::checkWrite())
        {
            Log::record();
        }
    }
}
