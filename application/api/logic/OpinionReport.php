<?php
// +----------------------------------------------------------------------
// | 重点关注逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-21
// +----------------------------------------------------------------------

namespace app\api\logic;

/**
 * 重点关注
 */
class OpinionReport extends ApiLogic
{
    /**
     * 获取舆情数据
     * @DateTime 2018-08-22
     * @param    [array]    $data    [查询条件]
     * @param    [int]      $user_id [用户ID]
     * @param    [int]      $limit   [限制条数]
     * @param    [int]      $field   [限制字段]
     * @return   [array]    [list]   [返回数据集]
     */
    public function getDataList($data = [], $user_id = 0, $limit = 10, $page = 1)
    {

        return $this->logicArticle->getList($data, $user_id, $limit, $page);

    }

    /**
     * 获取echart图表数据
     * @DateTime 2018-08-22
     * @param    [array]    $data    [查询条件]
     * @param    [int]      $user_id [用户ID]
     * @return   [array]    [list]   [返回数据集]
     */
    public function getEchartData($data = [], $user_id = 0)
    {
        $esDocList = $this->logicArticle->getEsData($data, $user_id);

        //数据采集情况
        $sourceTimeMap = [];
        foreach ($esDocList['aggregations']['time_range']['buckets'] as $key => $value) {
            // $sourceTimeMap['xAxis'][] = $key;
            $sourceTimeMap['xAxis'][] = friend_date(strtotime($key), true);
            $sourceTimeMap['data'][]  = $value['doc_count'];
        }

        $sourceTimeMap['total'] = $esDocList['hits']['total'];

        //文章情感分析统计
        $emotionMap = [];

        foreach ($esDocList['aggregations']['emotion']['buckets'] as $key => $value) {
            $name                  = ($value['key'] == 1) ? '正面' : '预警';
            $emotionMap['xAxis'][] = $name;
            $emotionMap['data'][]  = ['name' => $name . '(' . $value['doc_count'] . ')', 'value' => $value['doc_count']];
            $emotionMap['ids'][]   = $value['key'];
        }

        //地区数据统计
        $areaMap = [];

        foreach ($esDocList['aggregations']['area']['area']['buckets'] as $key => $value) {
            $areaMap['xAxis'][] = $value['key'];
            $areaMap['data'][]  = $value['doc_count'];
        }

        //数据源抓取情况数据统计
        $sourceMap = [];

        foreach ($esDocList['aggregations']['active_source']['buckets'] as $key => $value) {
            $sourceMap['xAxis'][] = $value['key'];
            $sourceMap['data'][]  = $value['doc_count'];
        }

        if (isset($sourceMap['xAxis']) && $sourceMap['xAxis']) {
            $source = model('source')->where('source_id', 'in', $sourceMap['xAxis'])->column('name', 'source_id');
            foreach ($sourceMap['xAxis'] as $key => $value) {
                $sourceMap['xAxis'][$key] = $source[$value];
                $sourceMap['ids'][$key]   = $value;
            }
        }

        return [
            'source_time' => $sourceTimeMap,
            'source'      => $sourceMap,
            'area'        => $areaMap,
            'emotion'     => $emotionMap,
        ];
    }

    /**
     * 获取舆情报告列表
     * @DateTime 2018-08-30
     * @param    [array]    $data    [查询条件]
     * @param    [int]      $user_id [用户ID]
     * @param    [int]      $limit   [限制条数]
     * @param    [int]      $field   [限制字段]
     * @return   [array]    [list]   [返回数据集]
     */
    public function getList($user_id = 0, $limit = 10, $page = 1, $start_time = null, $end_time = null)
    {
        $map['status']  = 1;
        $map['user_id'] = $user_id;

        $query = $this->modelOpinionReport
            ->where($map)
            ->page($page, $limit)
            ->field('id, title, create_time')
        ;
        if ($start_time && $end_time) {
            $query = $query->whereTime('create_time', 'between', [$start_time, $end_time]);
        } else {
            if ($start_time) {
                $query = $query->whereTime('create_time', '>=', $start_time);
            }
            if ($end_time) {
                $query = $query->whereTime('create_time', '<=', $end_time);
            }
        }
        return $query->select();
    }

    /**
     * 获取舆情报告详情
     * @DateTime 2018-08-30
     * @param    [int]      $id      [舆情报告ID]
     * @param    [int]      $user_id [用户ID]
     * @return   [array]    [list]   [返回数据集]
     */
    public function detail($id = 0, $user_id = 0)
    {
        $map['status']  = 1;
        $map['id']      = $id;
        $map['user_id'] = $user_id;

        $info = $this->modelOpinionReport
            ->where($map)
            ->field('id, title, content, create_time')
            ->find()
        ;

        return $info;
    }

    /**
     * 删除考核信息
     * @DateTime 2018-08-24
     * @param    [int]    $id           删除数据ID
     * @param    [int]    $user_id      用户ID
     * @return   [bool]   $result       数据结果
     */
    public function delete($id = 0, $user_id = 0)
    {
        $map['user_id'] = $user_id;
        $map['id']      = $id;

        // 是否有权限删除
        $info = $this->modelOpinionReport->where($map)->find();

        if (empty($info) == true) {
            return $this->apiError(['code' => 10001, 'msg' => '没有数据']);
        }

        $result = $this->modelOpinionReport->where($map)->delete();

        return $result;
    }
}
