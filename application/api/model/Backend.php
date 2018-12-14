<?php

namespace app\api\model;

// use app\admin\model\UserLog;

use think\Model;

class Backend extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    //假删除字段 1 即为存在 -1 表示删除
    public $del_field ='';
    
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    public function getNameAttr($value, $data)
    {
        return $value;
    }

    // 处理时间
    public function getCreateTimeAttr($value, $data)
    {
        return date('Y-m-d H:i', $value);
    }

    public function getUpdateTimeAttr($value, $data)
    {
        return date('Y-m-d H:i', $value);
    }

    /**
     * 重写get
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-08
     * @param    [type]       $data  [description]
     * @param    array        $with  [description]
     * @param    boolean      $cache [description]
     * @return   [type]              [description]
     */
    final public static function get($data, $with = [], $cache = false)
    {
        $model                                       = new static(get_class(new static()));
        $model->del_field ? $data[$model->del_field] = 1 : '';
        $query                                       = static::parseQuery($data, $with, $cache);
        return $query->find($data);
    }
}
