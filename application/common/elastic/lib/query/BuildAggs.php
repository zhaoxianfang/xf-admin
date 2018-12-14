<?php
// +---------------------------------------------------------------------
// | ES 聚合查询
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
 * 聚合构造器
 */
class BuildAggs extends Query
{
    /**
     * 子集参数
     *
     * @var array
     */
    protected $childOption;

    /**
     * 添加terms桶
     *
     * @param  string $name  聚合指定的名称
     * @param  string $field 要分组的字段
     * @param  integer $size 分组的个数
     * @param  string $order 排序，支持按文档数量排序，asc,desc
     *
     * @return $this
     */
    public function terms($name, $field, $size = 10, $order = 'desc')
    {
        $this->childOption[$name]['terms']['field'] = $field;
        $this->childOption[$name]['terms']['size']  = $size;
        $this->childOption[$name]['terms']['order']['_count'] = $order;
        return $this;
    }

    /**
     * 添加histogram桶
     *
     * @param  string $name     为聚合指定名称
     * @param  string $field    要分组的字段
     * @param  integer $interval 桶的范围（间隔）
     *
     * @return $this
     */
    public function histogram($name, $field, $interval)
    {
        $this->childOption[$name]['histogram']['field'] = $field;
        $this->childOption[$name]['histogram']['interval'] = $interval;
        $this->childOption[$name]['histogram']['min_doc_count'] = 0; // 强制返回空桶
        return $this;
    }

    /**
     * 添加date_histogram桶
     *
     * @param  string $name     为聚合指定名称
     * @param  string $field    要分组的字段
     * @param  string $interval 桶的范围（支持：year, quarter, month, week, day, hour, minute, second）
     * @param  string $format   时间格式
     * @param  string $start_time 开始时间（用format指定的格式）
     * @param  string $end_time   结束时间（用format指定的格式）
     *
     * @return $this
     */
    public function dateHistogram($name, $field, $interval, $format, $start_time, $end_time)
    {
        $this->childOption[$name]['date_histogram']['field'] = $field;
        $this->childOption[$name]['date_histogram']['interval'] = $interval;
        $this->childOption[$name]['date_histogram']['format'] = $format;
        $this->childOption[$name]['date_histogram']['min_doc_count'] = 0; // 强制返回空桶
        // 强制返回指定时间戳
        $this->childOption[$name]['date_histogram']['extended_bounds'] = [
            'min'   => $start_time,
            'max'   => $end_time
        ]; 
        return $this;
    }

    /**
     * 添加 范围聚合
     *
     * @param  string $name  桶名称
     * @param  string $field 要分组的字段
     * @param  string $key   范围名称
     * @param  integer $from  from值
     * @param  integer $to    to值
     *
     * @return $this
     */
    public function range($name, $field, $key, $from, $to)
    {
        $this->childOption[$name]['range']['field'] = $field;
        $this->childOption[$name]['range']['keyed'] = true;
        if ($from && $to) {
            $this->childOption[$name]['range']['ranges'][] = [
                'key'   => $key,
                'from'  => $from,
                'to'    => $to,
            ];
        } else if ($from) {
            $this->childOption[$name]['range']['ranges'][] = [
                'key'   => $key,
                'from'  => $from,

            ];
        } else if ($to) {
            $this->childOption[$name]['range']['ranges'][] = [
                'key'   => $key,
                'to'    => $to,

            ];
        }
        return $this;
    }

    /**
     * 添加度量指标
     *
     * @param  string $parent_name 指定分组的名字(要在哪个分组下添加度量)
     * @param  string $name  为度量指定名字
     * @param  string $model 度量计算模式（avg:平均值，sum：总合，标准差：extended_stats，……）
     * @param  string $field 要度量的字段
     *
     * @return $this
     */
    public function measure($parent_name = '', $name = '', $model = '', $field = '')
    {
        if ($parent_name) {
            $this->childOption[$parent_name]['aggs'][$name][$model]['field'] = $field;
        } else {
            $this->childOption[$name][$model]['field'] = $field;
        }
        return $this;
    }

    /**
     * 添加nested聚合
     *
     * @param  string $name      nested聚合“进入”嵌套的字段
     * @param  string $path      nested的path路径字段名称
     * @param  string $child_key 要添加到nested聚合的key值
     *
     * @return $this
     */
    public function nested($name, $path, $child_key)
    {
        $childOption[$name]['nested']['path'] = $path;
        $childOption[$name]['aggs'][$child_key] = $this->childOption[$child_key];

        unset($this->childOption[$child_key]);

        $this->childOption = array_merge($this->childOption, $childOption);
        return $this;
    }

    /**
     * 构建参数
     */
    public function build()
    {
        $this->option['aggs'] = $this->childOption;
        return $this->option;
    }
}