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

namespace app\common\elastic\service;

use app\common\elastic\exception\ModelException;
use app\common\elastic\lib\Query;
use app\common\elastic\model\Doc as ES;
use app\facade\Keywords;
use Think\Config;

/**
 * 文档信息服务层
 */
class Doc extends Service
{

    /**
     * 初始化
     */
    public function __construct()
    {
        $this->es = new ES;
    }

    public function getList($map = [], $sort = [], $offset = 0, $length = 15, $fields = '')
    {
        // 获取查询参数
        $option = $this->getQueryMatch($map, $sort, $offset, $length, $fields);

        // 全文搜索高亮显示文本
        if (!empty($map['keyword'])) {
            $keyword   = trim($map['keyword']);
            $highlight = $this->getHighlightQuery();
            $option    = array_merge($option, $highlight);

            //高亮词汇
            $option['query']['bool']['must'][] = $this->getHighlightMatch($keyword);
        }
        // echo '<pre>';
        // print_r($option);
        // print_r($map);
        // die;

        // $query  = Query::getInstance('aggs');
        // //来源聚合
        // $query->terms('source_id', 'source_id');
        // // dump($query->build());die;
        // $option = array_merge($option, $query->build());
        // print_r(json_encode($option));die;

        $list = $this->es->searchDoc($option);
        // return $list;

        $list = $this->subsHighlightData($list, $offset, $length);
        // $list   = $this->disposeHits($list, $offset, $length);
        return $list;
    }

    /**
     * 获取高亮显示部分
     */
    private function getHighlightQuery()
    {
        $style     = config('es.highlight_style');
        $pre_tags  = '<em>';
        $post_tags = '</em>';
        $highlight = [
            'highlight' => [
                'number_of_fragments' => 5,
                'fragment_size'       => 100,
                'fields'              => [
                    '_all'    => ['pre_tags' => [$pre_tags], 'post_tags' => [$post_tags]],
                    'title'   => ['number_of_fragments' => 0],
                    'author'  => ['number_of_fragments' => 0],
                    'summary' => ['number_of_fragments' => 0],
                    'content' => [
                        'number_of_fragments' => 5,
                        'order'               => 'score',
                        'fragmenter'          => 'span',
                        'type'                => 'unified',
                        'pre_tags'            => [$pre_tags],
                        'post_tags'           => [$post_tags],
                    ],
                ],
            ],
        ];
        // halt($highlight);
        return $highlight;
    }

    /**
     * 获取查询参数
     *
     * @param  array   $map    查询条件
     * @param  array   $sort   排序规则
     * @param  integer $offset 偏移量
     * @param  integer $length 获取得数据长度
     * @param  array   $field 搜索指定的字段
     *
     * @return array      ES查询参数
     */
    private function getQueryMatch($map = [], $sort = [], $offset = 0, $length = 15, $fields = '')
    {

        $query = Query::getInstance('query');

        // 按ID搜索
        if (isset($map['id'])) {
            $doc_id['id'] = $map['id'];
            $query->terms($doc_id);
        }

        // 发布时间搜索
        if (isset($map['start_publish_time']) && isset($map['end_publish_time'])) {
            $publish_time['publish_time']['gte'] = $map['start_publish_time'];
            $publish_time['publish_time']['lte'] = $map['end_publish_time'];
            $query->range($publish_time);
        } else if (isset($map['start_publish_time'])) {
            $publish_time['publish_time']['gte'] = $map['start_publish_time'];
            $query->range($publish_time);
        } else if (isset($map['end_publish_time'])) {
            $publish_time['publish_time']['lte'] = $map['end_publish_time'];
            $query->range($publish_time);
        }

        // 按数据来源搜索
        if (isset($map['source_id'])) {
            $source['source_id'] = $map['source_id'];
            $query->terms($source);
        }

        // 数据排序
        if ($sort) {
            foreach ($sort as $field => $parttern) {
                $query->sort($field, $parttern);
            }
        }

        // 获取指定字段
        if ($fields) {
            $query->field($fields);
        }

        // 分页搜索
        $query->limit($offset, $length);

        $option = $query->build('must');
        
        //如果设置排斥ID
        if (isset($map['exclude_id'])) {
            // 排斥 exclude
            $query->clearOption(true);
            $query->terms([$this->es->getPk() => $map['exclude_id']]);
            $option_exclude = $query->build('must_not');

            $option['query']['bool'] = array_merge($option['query']['bool'], $option_exclude['query']['bool']);
        }

        //如果设置附加ID
        if (isset($map['include_id'])) {
            // 增补；附加 subjoin

            $option_subjoin['query']['bool']['must'][] = ['ids' => ['values' => $map['include_id']]];
            $optionTemp['query']['bool']['should'][]   = [$option['query'], $option_subjoin['query']];

            $option = $optionTemp;
        }
        return $option;
    }

    /**
     * 获取高亮搜索条件
     *
     * @param  string $keyword 关键词
     *
     * @return array
     */
    private function getHighlightMatch($keyword)
    {
        $query = Query::getInstance('query');

        // 按关键词进行全文搜索
        if (isset($keyword)) {
            $search_map = [];

            //判断是否具有查询关键词
            $joinKeyword = Keywords::where('name', $keyword)->find();
            if (isset($joinKeyword['join_word']) && trim($joinKeyword['join_word'], '|')) {
                $keywordsArr = explode('|', trim($joinKeyword['join_word'], '|'));
                // 按关键词进行全文搜索
                foreach ($keywordsArr as $key => $word) {
                    $search_word_map[]['match'] = ['title' => $word];
                    $search_word_map[]['match'] = ['content' => $word];
                }
                //在满足其他条件的情况下 必须满足 自定义关联词汇 伪表达式：（a && b && c ...  && ( 自定义词汇1 || 自定义词汇2)  ）
                $search_map['bool']['should'] = $search_word_map;
                // $query->subjoin($keywordAnalyzerData);
            }

            //说明，egg 1 不需要同时同时满足； egg 2只要满足一个条件即可
            //egg 1: $search_map[]['match'] = ['title' => $keyword];
            //egg 2: $search_map['bool']['should'][]['match'] = ['title' => $keyword];

            $search_map['bool']['should'][]['match'] = ['title' => $keyword];
            $search_map['bool']['should'][]['match'] = ['content' => $keyword];

            return $search_map;
        }
    }

    /**
     * 获取详情信息
     *
     * @param  integer $doc_id 文档ID
     *
     * @return array          文档信息
     */
    public function getInfo($doc_id = 0)
    {
        try {
            return $this->es->getDocById($doc_id);
        } catch (ModelException $e) {
            return false;
        }
    }

    /**
     * 创建索引
     */
    public function createIndex()
    {
        try {
            $this->es->deleteIndex();
        } catch (ModelException $e) {
        }
        // 创建索引
        $this->es->createIndex();

        // 创建mapping
        // 标题
        $properties['title'] = [
            'type'            => 'text',
            'analyzer'        => 'ik_max_word', //最细粒度
            'search_analyzer' => 'ik_max_word',
        ];
        //作者
        $properties['author'] = [
            'type'            => 'text',
            'analyzer'        => 'ik_max_word',
            'search_analyzer' => 'ik_max_word',
        ];
        //栏目
        $properties['column'] = [
            'type' => 'keyword',
        ];
        // 来源名称
        $properties['source']['type']       = 'object';
        $properties['source']['properties'] = [
            'name' => ['type' => 'keyword'],
        ];
        // 网页链接
        $properties['url'] = [
            'type'            => 'text',
            'analyzer'        => 'ik_smart', //最粗粒度
            'search_analyzer' => 'ik_smart',
        ];

        //内容
        $properties['content'] = [
            'type'            => 'text',
            'analyzer'        => 'ik_max_word',
            'search_analyzer' => 'ik_max_word',
        ];

        $type                         = $this->es->getType();
        $mapping[$type]['properties'] = $properties;
        $this->es->putMapping($mapping);
        return $this->es->getIndexMappting();
    }
}
