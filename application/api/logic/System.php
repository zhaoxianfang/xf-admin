<?php
// +----------------------------------------------------------------------
// | 系统逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-23
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\api\library\Auth;
use think\Config;

/**
 * 系统
 */
class System extends ApiLogic
{

    /**
     * 关于我们
     * @DateTime 2018-09-05
     * @return   [array]  $info  信息
     */
    public function about()
    {
        $info['about_us'] = Config::get('wxapi.about_us') ? : '';
        $info['about_py'] = Config::get('wxapi.about_py') ? : '';

        return $info;
    }
}