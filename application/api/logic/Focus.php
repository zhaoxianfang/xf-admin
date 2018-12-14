<?php
// +----------------------------------------------------------------------
// | 重点关注逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-20
// +----------------------------------------------------------------------

namespace app\api\logic;

/**
 * 重点关注
 */
class Focus extends ApiLogic
{

    /**
     * 获取重点关注项及数据
     * @DateTime 2018-08-20
     * @param    [int]    $user_id  用户ID
     * @param    [int]    $limit    限制条数
     * @param    [int]    $field    限制字段
     * @param    [int]    $order    数据排序
     * @return   [array]    $list   查询结果集
     */
    public function getList($user_id = 0, $limit = 10, $field = '*', $order = "attention_id DESC")
    {
        $where['status']        = 1;
        $where['user_id']       = $user_id;

        $list = $this->modelDocAttention
            ->where($where)
            ->limit($limit)
            ->field($field)
            ->order($order)
            ->select()
        ;

        // 请求携带数据
        foreach ($list as $key => $value) {
            // 获取具体文章列表
            $list[$key]['data'] = $this->getDocList($value['attention_id'], $user_id, $limit);

            $list[$key]['attention_start_time'] = date('Y-m-d H:i', $value['attention_start_time']);
            $list[$key]['attention_end_time']   = date('Y-m-d H:i', $value['attention_end_time']);
        }

        // 头条
        $head = $this->logicArticle->getList(['headline' => 1], $user_id, $limit);
        $headline = [
            'attention_id'          => -1,
            'type'                  => 0,
            'type_id'               => 0,
            'type_name'             => '',
            'type_value'            => '头条',
            "attention_start_time"  => "",
            "attention_end_time"    => "",
            'data'                  => $head,
        ];

        //合并数据
        array_unshift($list, $headline);

        return $list;
    }

    /**
     * 获取重点关注项
     * @DateTime 2018-08-20
     * @param    [int]      $user_id 查询条件
     * @param    [int]      $field   限制字段
     * @param    [int]      $order   数据排序
     * @return   [array]    $list    查询结果集
     */
    public function getMainList($user_id = 0, $field = '*', $order = "attention_id DESC")
    {
        $where['status']        = 1;
        $where['user_id']       = $user_id;

        $data = $this->modelDocAttention
            ->where($where)
            ->field($field)
            ->order($order)
            ->select()
        ;

        // 限制重点关注条数
        $count = $this->modelDocAttention->where($where)->count();

        // 字段处理
        foreach ($data as $key => $value) {
            // 时间处理
            $data[$key]['attention_start_time'] = date('Y-m-d H:i', $value['attention_start_time']);
            $data[$key]['attention_end_time']   = date('Y-m-d H:i', $value['attention_end_time']);
        }

        $list = ['list' => $data];
        $count >= 5 ? $list['is_limit'] = 1 : $list['is_limit'] = 0;

        return $list;
    }

    /**
     * 获取重点关注项下文章列表
     * @DateTime 2018-08-20
     * @param    [int]      $attention_id   重点关注ID
     * @param    [int]      $user_id        查询条件
     * @param    [int]      $limit          限制条数
     * @param    [int]      $option         上拉和下滑（1：刷新，2：翻页）
     * @param    [int]      $doc_id         分割ID
     * @return   [array]    $list           查询结果集
     */
    public function getDocList($attention_id = 0, $user_id = 0, $limit = 10, $page = 1)
    {
        // 头条数据处理
        if ($attention_id == -1) {
            $data['headline'] = 1;

            $list = $this->logicArticle->getList($data, $user_id, $limit, $page);
        } else {
            // 查询关注信息
            $where['attention_id'] = $attention_id;
            $info = $this->modelDocAttention->where($where)->find();

            if (empty($info)) {
                $this->apiError(['code' => 30003, 'msg' => '关注数据不存在']);
            }

            $data['start_time'] = date('Y-m-d H:i', $info['attention_start_time']);
            $data['end_time']   = date('Y-m-d H:i', $info['attention_end_time']);
            
            // 分类处理
            switch ($info['type']) {
                // 标签
                case 1:
                    $map['tag_id'] = $info['type_id'];
                    $doc_ids = $this->modelDocTag->where($map)->column('doc_id');
                    $data['doc_id'] = $doc_ids;
                    break;
                // 区域
                case 2:
                    $map['area_id'] = $info['type_id'];
                    $doc_ids = $this->modelDocArea->where($map)->column('doc_id');
                    $data['doc_id'] = $doc_ids;
                    break;
                // 数据源
                case 3:
                    $data['source_id'] = $info['type_id'];
                    break;
                default:
                    $this->apiError(['code' => 30001, 'msg' => '关注类型错误']);
            }

            $list = $this->logicArticle->getList($data, $user_id, $limit, $page);
        }

        return $list;
    }

    /**
     * 编辑或新增重点关注
     * @DateTime 2018-08-21
     * @param    [array]    $data   数据
     * @return   [bool]     $result 数据结果
     */
    public function edit($data = [], $attention_id = 0)
    {

        // 区分数据类型
        switch ($data['type']) {
            // 标签
            case 1:
                $map['tag_id'] = $data['type_id'];
                $tag_info = $this->modelTag->where($map)->find();
                if (empty($tag_info)) {
                    $this->apiError(['code' => 30002, 'msg' => '关注类型ID错误']);
                }
                $data['type_name']  = '标签';
                $data['type_value'] = $tag_info['keyword'];
                $data['doc_count']  = $tag_info['show_count'];
                break;
            // 地区
            case 2:
                $map['area_id'] = $data['type_id'];
                $area_info = $this->modelArea->where($map)->find();
                if (empty($area_info)) {
                    $this->apiError(['code' => 30002, 'msg' => '关注类型ID错误']);
                }
                $data['type_name']  = '地区';
                $data['type_value'] = $area_info['name'];
                $data['doc_count']  = 0;
                break;
            // 数据源
            case 3:
                $map['source_id'] = $data['type_id'];
                $source_info = $this->modelSource->where($map)->find();
                if (empty($source_info)) {
                    $this->apiError(['code' => 30002, 'msg' => '关注类型ID错误']);
                }
                $data['type_name']  = '数据来源';
                $data['type_value'] = $source_info['name'];
                $data['doc_count']  = 0;
                break;
            default:
                $this->apiError(['code' => 30001, 'msg' => '关注类型错误']);
        }

        $data['attention_start_time']   = strtotime($data['start_time']);
        $data['attention_end_time']     = strtotime($data['end_time']);
        unset($data['start_time']);
        unset($data['end_time']);

        // 数据是否存在
        $where['user_id']       = $data['user_id'];
        $where['type_name']     = $data['type_name'];
        $where['type_value']    = $data['type_value'];

        $exist = $this->modelDocAttention->where($where)->find();

        // 判断为新增或编辑
        if ($attention_id == 0) {
            if (!empty($exist)) {
                $this->apiError(['code' => 10006, 'msg' => '数据已存在']);
            }
            // 插入数据
            $result = $this->modelDocAttention->validate('DocAttention.add')->insert($data);

        } else {
            // 更新数据
            $result = $this->modelDocAttention
                ->validate('DocAttention.add')
                ->where('attention_id = '. $attention_id)
                ->update($data)
            ;
        }

        return $result;
    }

    /**
     * 删除重点关注
     * @DateTime 2018-08-21
     * @param    [int]    $attention_id   删除数据ID
     * @return   [bool]   $result         数据结果
     */
    public function delete($attention_id = 0, $user_id = 0)
    {
        $map['user_id']         = $user_id;
        $map['attention_id']    = $attention_id;

        $result = $this->modelDocAttention
            ->where($map)
            ->delete()
        ;
        
        return $result;
    }

    /**
     * 获取重点关注信息
     * @DateTime 2018-08-31
     * @param    [int]    $attention_id   数据ID
     * @return   [bool]   $info         数据结果
     */
    public function getInfo($attention_id = 0, $user_id = 0)
    {
        $map['user_id']      = $user_id;
        $map['attention_id'] = $attention_id;
        $map['status']       = 1;

        $info = $this->modelDocAttention
            ->where($map)
            ->field('attention_id, type, type_id, type_name, type_value, attention_end_time, attention_start_time')
            ->find()
        ;
        
        // $info['attention_end_time'] = date('Y-m-d H:i', $info['attention_end_time']);
        // $info['attention_start_time']   = date('Y-m-d H:i', $info['attention_start_time']);

        return $info;
    }
}