<?php
// +---------------------------------------------------------------------+
// | Api 路由请求错误                                      			     |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+
// | Date: 2018-08-15										   			|
// +----------------------------------------------------------------------

namespace app\api\behavior;

use think\Loader;
use think\facade\Env;

/**
 * 路由请求错误行为
 */
class ApiRootErr
{
	/**
     * 初始化行为入口
     */
    public function run($request)
    {
        // dump($request);
        // TODO 
        
    }
}