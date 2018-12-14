<?php
// +----------------------------------------------------------------------
// | Api模型基础逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\model;

use think\Model;
use think\Db;

/**
 * 模型基类
 */
class ApiModel extends Model
{
    
    /**
     * 重写获取器 兼容 模型|逻辑|验证|服务 层实例获取
     */
    public function __get($name)
    {
        
        $layer = $this->getLayerPrefix($name);
        
        if (false === $layer) {
            
            return parent::__get($name);
        }
        
        $model = sr($name, $layer);
        
        return 'validate' == $layer ? validate($model) : model($model, $layer);
    }
    
    /**
     * 获取层前缀
     */
    public function getLayerPrefix($name)
    {
        
        $layer = false;
        
        $layer_array = ['model', 'logic', 'validate', 'service'];
        
        foreach ($layer_array as $v)
        {
            if (str_prefix($name, $v)) {
                
                $layer = $v;
                
                break;
            }
        }
        
        return $layer;
    }
}
