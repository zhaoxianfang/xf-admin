<?php
// +---------------------------------------------------------------------+
// | Api 路由请求错误                                                       |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+
// | Date: 2018-08-15                                                       |
// +----------------------------------------------------------------------

namespace app\api\behavior;

/**
 * 路由请求错误行为
 */
class ApiInit
{
    /**
     * 初始化行为入口
     */
    public function run()
    {
        // 初始化系统常量
        $this->initSystemConst();
        // 初始化API常量
        $this->initApiConst();

    }

    /**
     * 初始化系统常量
     */
    private function initSystemConst()
    {

    }

    /**
     * 初始化API常量
     */
    private function initApiConst()
    {
        define('API_KEY', 'wlyq2018');
        define('API_CODE_NAME', 'code');
        define('API_MSG_NAME', 'msg');
    }

}
