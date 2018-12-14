<?php
// +---------------------------------------------------------------------
// | ES 通用查询
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\elastic\lib\query;

use app\common\elastic\lib\Query;

/**
 * es查询构造器
 */
class BuildQuery extends Query
{
    /**
     * es查询条件
     *
     * @var array
     */
    protected $option;

    /**
     * es子查询
     *
     * @var array
     */
    protected $childOption;

    /**
     * 指定查询数量
     * 
     * @param integer $offset 起始位置
     * @param integer $length 查询数量
     * 
     * @return $this
     */
    public function limit($offset = 0, $length = 15)
    {
        $this->option['from'] = $offset;
        $this->option['size'] = $length;
        return $this;
    }

    /**
     * 排序
     *
     * @param  string $field   排序的字段名称
     * @param  string $pattern 排序的模式，支持（desc,asc）
     *
     * @return $this
     */
    public function sort($field, $pattern = 'desc')
    {
        $this->option['sort'][] = [$field => ['order' => $pattern]];
        return $this;
    }

    /**
     * 指定返回的字段
     * 
     * @param array $field 选择的字段
     * 例如：
     * 获取指定的字段：['incluce' => ['doc_id','title']]
     * 排除指定的字段：['exclude' => ['doc_id','title']]
     *
     * @return $this
     */
    public function field($field)
    {
        $option = [];
        foreach ($field as $pattern => $item) {
            switch ($pattern) {
                case 'include' :
                    $option['include'] = $item;
                    break;
                case 'exclude' :
                    $option['exclude'] = $item;
                    break;
            }
            break;
        }
        if ($option) {
            $this->option['_source'] = $option;
        }

        return $this;
    }

    /**
     * term形式搜索,搜索值只能是简单数据类型
     * 例如：['test1' => 'abc']
     *
     * @param  array  $where 过滤条件
     *
     * @return $this
     */
    public function term($where)
    {
        $this->childOption[] = ['term' => $where];
        return $this;
    }

    /**
     * terms形式搜索，搜索的值为数组格式
     * 例如：['test1' => [1,2,3]]
     *
     * @param  array  $where 过滤条件
     *
     * @return $this
     */
    public function terms($where)
    {
        $this->childOption[] = ['terms' => $where];
        return $this;
    }

    /**
     * range形式范围搜索
     * 范围操作包含：
     *     gt :: 大于
     *     gte :: 大于等于
     *     lt :: 小于
     *     lte :: 小于等于
     * 例如：['publish_time' => ['gte' => '234', 'lte' =>'1233']]
     *
     * @param  array  $where 过滤条件
     *
     * @return $this
     */
    public function range($where)
    {
        $this->childOption[] = ['range' => $where];
        return $this;
    }

    /**
     * exists形式搜索，主要用于判断某个字段是否存在
     *
     * @param  string  $field 字段名称
     *
     * @return $this
     */
    public function exists($field)
    {
        $this->childOption[] = ['exists' => ['field' => $field]];
        return $this;
    }

    /**
     * 自己拼接数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-05
     * @param    [type]       $data [description]
     * @return   [type]             [description]
     */
    public function subjoin($data)
    {
        $this->childOption[] = $data;
        return $this;
    }

    /**
     * 清除配置
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-04
     * @param    boolean      $flag [是否清除标识]
     * @return   [type]             [description]
     */
    public function clearOption($flag = true)
    {
        if($flag){
            $this->childOption = [];
        }
        return $this;
    }

    /**
     * 构建参数
     *
     * @param  string  $bool 用户来合并过滤条件查询结果
     * 值可选:must,filter,should
     *
     * @return array  返回查询参数 
     */
    public function build($bool = 'filter')
    {
        $this->option['query']['bool'][$bool] = $this->childOption;
        $option = $this->option;
        return $option;
    }
}