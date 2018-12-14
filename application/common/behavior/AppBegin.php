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

use think\Facade;
use think\facade\Env;
use think\Loader;
use think\facade\Request;

/**
 * 初始化基础信息行为
 */
class AppBegin
{

    /**
     * 初始化行为入口
     */
    public function run()
    {

        // 初始化常量
        $this->initConst();
        // 初始化配置
        $this->initConfig();
        // 初始化动态配置信息
        $this->initTmconfig();
        //Facade 绑定
        $this->facadeBind();

    }

    /**
     * 初始化常量
     */
    private function initConst()
    {

        // 初始化系统常量
        $this->initSystemConst();

        // 初始化路径常量
        $this->initPathConst();

    }

    /**
     * 初始化路径常量
     */
    private function initPathConst()
    {

        defined("DS")          || define('DS', DIRECTORY_SEPARATOR);
        defined("ROOT_PATH")   || define('ROOT_PATH', Env::get('root_path'));
        defined("PUBLIC_PATH") || define('PUBLIC_PATH', ROOT_PATH . 'public' . DS);

    }

    /**
     * 初始化系统常量
     */
    private function initSystemConst()
    {
        define('SYS_ADMINISTRATOR_ID', 1); //超级管理员id
    }

    /**
     * 初始化配置信息
     */
    private function initConfig()
    {
        $model = model('common/Config');

        $config_list = auto_cache('config_list', create_closure($model, 'all'));

        foreach ($config_list as $info) {

            $config_array[$info['name']] = $info['value'];
        }

        config(['sys' => $config_array]);

    }

    /**
     * 初始化动态配置信息
     */
    private function initTmconfig()
    {

    }

    //Facade 绑定
    private function facadeBind()
    {

        // 注册核心类的静态代理
        Facade::bind(config('facade.facade'));

        // 注册类库别名
        Loader::addClassAlias(config('facade.loader'));

    }

}
