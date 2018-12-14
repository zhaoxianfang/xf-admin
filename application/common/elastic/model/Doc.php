<?php
// +---------------------------------------------------------------------
// | ES类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\elastic\model;

use app\common\elastic\lib\Model;
use app\common\elastic\exception\ModelException;

/**
 * 文档信息模型
 */
class Doc extends Model
{
    /**
     * 数据主键ID
     *
     * @var string
     */
    protected $pk = 'id';

    /**
     * 设置es索引
     */
    protected function setIndex()
    {
        $this->index = 'doc';
    }

    /**
     * 设置ES类型值
     */
    protected function setType()
    {
        $this->type = 'document';
    }

    protected function init()
    {

    }

    public function getPk()
    {
        return $this->pk;
    }

    /**
     * 获取ES字段
     */
    public function getField()
    {
        return [
            'id',
            'title',
            'column',
            'source',
            'url',
            'source_id',
            'publish_time',
            'author',
            'url',
            'creare_time',
            'status'
        ];
    }
}