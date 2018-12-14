<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------
use think\facade\Request;

defined("PUBLIC_PATH") || define('PUBLIC_PATH', Request::root());

return [

    'layout_on'          => false,
    'layout_name'        => 'layout/default',
    'tpl_cache'          => true,

    'tpl_replace_string' => [
    	'__PUBLIC__'   => PUBLIC_PATH,
        '__API__'      => PUBLIC_PATH . '/static/api',
        '__STATIC__'   => PUBLIC_PATH . '/static',
        '__LIBS__'     => PUBLIC_PATH . '/static/libs',
        '__ADMINLTE__' => PUBLIC_PATH . '/static/libs/adminlte',
        '__BS__'       => PUBLIC_PATH . '/static/libs/bootstrap',
        '__FONT__'     => PUBLIC_PATH . '/static/libs/font-awesome',
        '__ICHECK__'   => PUBLIC_PATH . '/static/libs/iCheck',
        '__ICONS__'    => PUBLIC_PATH . '/static/libs/Ionicons',
        '__IE__'       => PUBLIC_PATH . '/static/libs/low_ie',
        '__JQ__'       => PUBLIC_PATH . '/static/libs/jquery',
        '__PJAX__'     => PUBLIC_PATH . '/static/libs/pjax',
        '__LAYER__'    => PUBLIC_PATH . '/static/libs/layer',
        '__ADMIN__'    => PUBLIC_PATH . '/static/module/admin',
        '__INDEX__'    => PUBLIC_PATH . '/static/module/index',
        '__COMMON__'   => PUBLIC_PATH . '/static/module/common',
    ],
];
