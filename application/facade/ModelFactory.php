<?php
// +---------------------------------------------------------------------
// | 门面 工厂
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\facade;

use think\Facade;

/**
 * Class ModelFactory
 * @package app\facade
 */
class ModelFactory extends Facade
{
    private static $ModelName;
    public function __construct($ModelName)
    {
        self::$ModelName = $ModelName;
    }

    protected static function getFacadeClass()
    {
        $ModelName = 'app\common\model\\' . self::$ModelName;
        return $ModelName;
    }
}
