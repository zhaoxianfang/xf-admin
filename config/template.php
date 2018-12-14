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
    // 模板引擎类型 支持 php think 支持扩展
    'type'               => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'          => 1,
    // 模板路径
    'view_path'          => '',
    // 模板后缀
    'view_suffix'        => 'html',
    // 模板文件名分隔符
    'view_depr'          => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'          => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'            => '}',
    // 标签库标签开始标记
    'taglib_begin'       => '{',
    // 标签库标签结束标记
    'taglib_end'         => '}',

    'layout_on'          => true,
    'layout_name'        => 'layout/default',
    'tpl_cache'          => true,

    'tpl_replace_string' => [
        '__PUBLIC__'   => PUBLIC_PATH,
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
