<?php
// +---------------------------------------------------------------------
// | 系统公共模型
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\model;

use think\Model;

/**
 * 模型基类
 */
class Base extends Model
{

    /**
     * 重写获取器 兼容 模型|逻辑|验证|服务 层实例获取
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @param    [type]       $name [description]
     * @return   [type]             [description]
     */
    public function __get($name)
    {

        $layer = $this->getLayerPrefix($name);

        if (false === $layer) {
            try {
                return $this->$name;
            } catch (\Exception $e) {
                return new \think\exception\ThrowableError($e);
            }
        }

        $model = sr($name, $layer);

        return "validate" == $layer ? validate($model) : model($model, $layer);
    }

    /**
     * 获取层前缀
     */
    public function getLayerPrefix($name)
    {

        $layer = false;

        $layer_array = ['model', 'logic', "validate", "service"];

        foreach ($layer_array as $v) {
            if ($name != $v && str_prefix($name, $v)) {

                $layer = $v;

                break;
            }
        }

        return $layer;
    }

    /**
     * 更新数据
     */
    final protected function updateInfo($where = [], $data = [])
    {

        $data['update_time'] = time();

        return $this->allowField(true)->save($data, $where);
    }

    /**
     * 添加数据
     */
    public function addRow($data = [])
    {
        if (!$data) {
            return ['0', '无参数传递'];
        }
        // 过滤post数组中的非数据表字段数据
        return $this->allowField(true)->insertGetId($data);
    }

    /**
     * 查询单条
     */
    public function getRow($where = [], $field = true, $alias = false, $join = [])
    {
        if ($alias) {
            $query = $this->alias($alias);
        } else {
            $query = $this;
        }

        $query = $this->setJoin($query, $join);
        $query = $this->setWhere($query, $where);
        if (!$where) {
            return false;
        }
        // 过滤post数组中的非数据表字段数据
        return $query->field($field)->find();
    }

    /**
     * 更新数据
     */
    public function updateRow($data = [])
    {
        // 显式指定更新数据操作
        return $this->isUpdate(true)->save($data);
    }

    /**
     * 删除数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-12
     * @param    array        $where [description]
     * @return   [type]              [description]
     */
    public function delRow($where = [])
    {
        if (!$where) {
            return false;
        }
        if (is_array($where)) {
            return $this->where($where)->delete();
        }
        if (is_numeric($where)) {
            return $this->delete($where);
        }
        return false;

    }

    /**
     * 设置关联属性
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     */
    private function setJoin($query = '', $joinData = [])
    {

        if (!empty($joinData)) {
            if (is_array($joinData)) {
                $join_count = count($joinData);
                if ($join_count > 0) {
                    if (isset($joinData['0']['0']) && is_array($joinData['0'])) {
                        for ($i = 0; $i < $join_count; $i++) {

                            $join_one = $joinData[$i]['0'];
                            $join_two = '';

                            if (isset($joinData[$i]['1'])) {
                                $join_two = $joinData[$i]['1'];
                            }
                            $query = $query->join($join_one, $join_two);
                        }
                    } else {
                        $join_one = $joinData['0'];
                        $join_two = '';
                        if (isset($joinData['1'])) {
                            $join_two = $joinData['1'];
                        }
                        $query = $query->join($join_one, $join_two);
                    }
                }
            } else {
                //不是数组则认为是字符串
                $query = $query->join($joinData);
            }
        }

        return $query;
    }

    /**
     * 设置where 查询条件
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-15
     * @param    string       $query [description]
     * @param    array        $where [description]
     */
    private function setWhere($query = '', $where = [])
    {
        if (!empty($where)) {
            if (is_array($where) && $where && isset($where[0]) && !is_array($where[0])) {
                $key1                     = $key2                     = $key3                     = '';
                list($key1, $key2, $key3) = $where;
                $query                    = $query->where($key1, $key2, $key3);
            } else {
                if (isset($where[0]) && is_array($where[0])) {
                    $where_count = count($where);
                    for ($i = 0; $i < $where_count; $i++) {
                        if (count($where[$i]) > 1) {
                            $key1                     = $key2                     = $key3                     = '';
                            list($key1, $key2, $key3) = $where[$i];
                            $query                    = $query->where($key1, $key2, $key3);
                        } else {
                            $query = $query->where($where[$i]);
                        }
                    }
                } else {
                    $query = $query->where($where);
                }
            }
        }
        return $query;
    }

    /**
     * 分页查询
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-13
     * @param    integer      $pageNum [每页显示条数]
     * @param    array        $where   [查询条件]
     * @param    string       $order   [排序]
     * @param    boolean      $field   [查询字段]
     * @return   [type]                [description]
     */
    public function pageList($pageNum = 10, $where = [], $order = '', $field = true, $alias = false, $join = [])
    {

        $query = $this;

        if (gettype($where) == 'array') {
            $query = $this->setWhere($query, $where);
        } else {
            $query = $this->where($where);
        }
        $query = $this->setJoin($query, $join);

        return $query->alias($alias)->field($field)->order($order)->paginate($pageNum, false, [
            'type'      => 'bootstrap',
            'var_page'  => 'page',
            'list_rows' => 5,
        ]);
    }

    /**
     * 获取列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-15
     * @param    array        $where [description]
     * @return   [type]              [description]
     */
    final public function getList($where = [], $order = '', $field = true, $limit = 50, $alias = false, $join = [])
    {

        $query = $this;
        if (gettype($where) == 'array') {
            $query = $this->setWhere($query, $where);
        } else {
            $query = $this->where($where);
        }
        $query = $this->setJoin($query, $join);
        return $query->alias($alias)->field($field)->order($order)->limit($limit)->select();
    }

    /**
     * 统计查询总条件
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-11
     * @param    array        $where [description]
     * @param    boolean      $alias [description]
     * @param    array        $join  [description]
     * @return   [type]              [description]
     */
    final public function getCount($where = [], $alias = false, $join = [])
    {
        $query = $this;

        if (gettype($where) == 'array') {
            $query = $this->setWhere($query, $where);
        } else {
            $query = $this->where($where);
        }
        $query = $this->setJoin($query, $join);
        return $query->alias($alias)->count();
    }

    /**
     * 查询自定字段
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-11
     * @param    array        $where [description]
     * @param    boolean      $field [description]
     * @return   [type]              [description]
     */
    final public function getColumn($where = [], $field = true)
    {
        $query = $this->setWhere($this, $where);
        return $query->column($field);
    }

}
