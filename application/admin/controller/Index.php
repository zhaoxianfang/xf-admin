<?php
// +---------------------------------------------------------------------
// | 后台首页
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller;

use app\common\elastic\service\Doc;
use think\facade\Cache;
use think\facade\Env;
use util\Page;

class Index extends AdminBase
{
    protected $noNeedRight       = ['index', 'wipecache', 'autocomplete'];
    protected $noNeedLogin       = [''];
    protected $showContentHeader = false;

    
    /**
     * 基础控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();

    }

    public function index()
    {
        $this->title = '欢迎';
        return $this->fetch();
    }

    /**
     * 清空系统缓存
     */
    public function wipecache()
    {
        $wipe_cache_type = [Env::get('runtime_path') . 'temp/', Env::get('runtime_path') . 'log/', Env::get('runtime_path') . 'cache/'];
        try {
            foreach ($wipe_cache_type as $item) {
                if (!is_dir($item)) {
                    continue;
                }
                rmdirs($item);
            }
            Cache::clear();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success();
    }
}
