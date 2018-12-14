<?php
// +---------------------------------------------------------------------
// | ES 查询器
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\elastic\lib;

use app\common\elastic\lib\query\BuildIndex;
use app\common\elastic\lib\query\BuildAggs;
use app\common\elastic\lib\query\BuildQuery;

/**
 * es请求体构造器
 */
class Query
{
    protected $option;
    
    /**
     * 构建参数
     */
    public function build()
    {
        return [];
    }

    /**
     * 实例化构造器
     */
    public static function getInstance($name = '')
    {
        switch ($name) {
            case 'index':
                $obj = new BuildIndex;
                break;
            case 'aggs':
                $obj = new BuildAggs;
                break;
            case 'query' :
                $obj = new BuildQuery;
                break;
            default:
                $obj = false;
                break;
        }

        return $obj;
    }
}