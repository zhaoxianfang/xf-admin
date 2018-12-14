<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用行为扩展定义文件
return [
	//应用初始化
	// 'app_init'     => ['app\\api\\behavior\\ApiInit'],
	// 模块初始化
    'module_init'  => ['app\\api\\behavior\\Common','app\\api\\behavior\\ApiInit'],
    // 'module_init'  => ['app\\api\\behavior\\Common'],
    // API 路由请求错误
    'api_root_err' => ['app\\api\\behavior\\ApiRootErr'],
    // APP 重新定义 app_end
    'app_end' => ['app\\api\\behavior\\ApiEnd'],
    
];
