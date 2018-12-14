<?php
// +---------------------------------------------------------------------
// | 门面配置
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

return [
    //注册静态代理
    'facade' => [
        'app\facade\Admin'    => 'app\common\model\Admin', //管理后台模型门面
        'app\facade\User'     => 'app\common\model\User', //用户模型门面
        'app\facade\Log'      => 'app\common\model\Log', //日志模型门面
        'app\facade\Config'   => 'app\common\model\Config', //配置模型门面
        'app\facade\Keywords' => 'app\common\model\Keywords', //搜索关键字模型门面
        'app\facade\Index'    => 'app\common\model\Index', //前台模型门面
        'app\facade\Callback'    => 'app\common\model\Callback', //回调模型门面
    ],
    // 注册类库别名
    'loader' => [
        'Admin'    => 'app\common\model\Admin',
        'User'     => 'app\common\model\User',
        'Log'      => 'app\common\model\Log',
        'Config'   => 'app\common\model\Config',
        'Api'      => 'app\common\model\Api',
        'Keywords' => 'app\common\model\Keywords',
        'Index' => 'app\common\model\Index',
        'Callback' => 'app\common\model\Callback',
    ],

];
