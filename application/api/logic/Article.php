<?php
// +----------------------------------------------------------------------
// | 文章逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-20
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\common\elastic\service\Doc as SearchDoc;
use app\api\library\Auth;

/**
 * 文章
 */
class Article extends ApiLogic
{       
    /**
     * 获取文章列表
     * @Author   wanghuaili
     * @DateTime 2018-08-20
     * @param    [array]    $data           查询条件
     * @param    [int]      $limit          限制条数
     * @param    [int]      $user_id        用户ID
     * @param    [int]      $page           分页页码
     * @return   [array]    $list           查询结果集
     */
    public function getList($data = [], $user_id = 0, $limit = 10, $page = 1)
    {

        // 分页数据处理
        $offset = $limit * ($page - 1);

        $esDocList = $this->getEsData($data, $user_id, $limit, $offset);

        $total       = $esDocList['hits']['total']; //得到的总条数
        $articleList = $esDocList['hits']['hits']; //得到当前页的数据

        $list   = [];

        //文章列表
        foreach ($articleList as $key => $value) {

            // 来源
            $list[$key]                = $value['_source'];
            $list[$key]['source_name'] = $value['_source']['source']['name'];
            unset($list[$key]['source']);
            // 情感处理
            $list[$key]['emotion']['positive'] = $value['_source']['emotion']['0']['positive'] * 100;
            $list[$key]['emotion']['negative'] = $value['_source']['emotion']['0']['negative'] * 100;
            $list[$key]['emotion']['emotion_status'] = $value['_source']['emotion']['0']['emotion_status'];
            unset($list[$key]['emotion']['0']);

            // 文章处理
            if (isset($value['highlight'])) {
                
                if (isset($value['highlight']['title'])) {
                    $list[$key]['title'] = $value['highlight']['title']['0'];
                }
                
                if (isset($value['highlight']['content'])) {
                    $list[$key]['title'] .= '<br /><div style="color:#999">' . $value['highlight']['content']['0'] . '</div>';
                }
                
                $list[$key]['title'] = '<p>' . $list[$key]['title'] . '</p>';
            }

            // 摘要处理
            // if (empty($list[$key]['summary'])) {
            //     // 处理标签及截取
            //     $list[$key]['summary'] = truncate($list[$key]['content'],200);
            // }
            
            // 标题处理
            if(empty($list[$key]['title'])){
                // $list[$key]['title'] = '微博文章';
                $list[$key]['title'] = truncate($list[$key]['content'],200);
            }

            // 剔除文章信息
            unset($list[$key]['content']);
            // 时间处理
            // $list[$key]['publish_time'] = date('Y年m月d日', $list[$key]['publish_time']);
            $list[$key]['publish_time'] = friend_date($list[$key]['publish_time']);
        }

        return $list;
    }

    /**
     * [getEsData 获取es数据]
     * @DateTime 2018-08-22
     * @param    array      $data       [查询条件]
     * @param    int        $user_id    [用户ID]
     * @param    array      $field      [限制字段]
     * @param    int        $limit      [限制条数]
     * @param    int        $offset     [偏移量]
     * @return   [array]    $esDocList  [结果集]
     */
    public function getEsData($data = [], $user_id = 0, $limit = 10, $offset = 0)
    {

        $doc  = new SearchDoc;
        $map = $list  = [];
        $sort = ['publish_time' => 'DESC'];

        $field['include'] = ['doc_id', 'title', 'content', 'source.name', 'publish_time','summary', 'emotion'];

        // 搜索的ID
        if (!empty($data['doc_id'])) {
            $map['doc_id'] = $data['doc_id'];
        }
        
        // 搜索的关键字
        if (!empty($data['keyword'])) {
            $map['keyword'] = $data['keyword'];
            $sort           = [];
        }

        // 按标签搜索
        if (!empty($data['tag_id'])) {
            $map['tag_id'] = explode(',', trim($data['tag_id'], ','));
        }

        // 按情感状态搜索
        if (!empty($data['emotion_status'])) {
            $map['emotion_status'] = [$data['emotion_status']];
        }

        // 按发布时间搜索(必须)
        $map['start_publish_time'] = strtotime("-3 day");
        $map['end_publish_time'] = time();

        if (!empty($data['start_time'])) {
            $map['start_publish_time'] = strtotime($data['start_time']); // 开始时间 默认前三天
        }

        if (!empty($data['end_time'])) {
            $map['end_publish_time'] = strtotime($data['end_time']); // 结束时间 默认此时
        }

        // 按数据源级别搜索
        if (!empty($data['source_level'])) {
            $map['source_level'] = [$data['source_level']];
        }
        // $map['source_level'] = $this->request->param('source_level/a', [0, 1, 2, 3, 4, 5]);

        // 按数据源类型搜索
        if (!empty($data['source_type'])) {
            $map['source_type'] = [$data['source_type']];
        }
        // $map['source_type'] = $this->request->param('source_type/a', [0, 1, 2, 3, 4, 5, 6]);

        // 按数据源
        $this->auth = new Auth();
        if (!empty($data['source_id'])) {
            $map['source_id'] = explode(',', $data['source_id']);
        } else {
            $map['source_id'] = $this->auth->getSourceIds($user_id);
        }

        // 按地区
        $my_area_ids = $this->auth->getAreaIds($user_id);
        if (!empty($data['area_id'])) {
            $map['area_id'] = [$data['area_id']];
        }

        // 按分类
        if (!empty($data['category_id'])) {
            $map['category_id'] = explode(',', $data['category_id']);
        }

        // 头条
        if (!empty($data['headline'])) {
            $map['headline'] = $data['headline'];
        }

        try {
            $esDocList = $doc->dataAnalyze($map, $sort, $offset, $limit, $field, 1);
        } catch (\Exception $e) {
            $this->apiError(['code' => 10005, 'msg' => '数据量过大']);
        }

        return $esDocList;
    }

    /**
     * 获取文章详情
     * @DateTime 2018-08-23
     * @param    array      $data       [查询条件]
     * @param    int        $user_id    [用户ID]
     * @param    array      $field      [限制字段]
     * @param    int        $limit      [限制条数]
     * @param    int        $offset     [偏移量]
     * @return   [array]    $esDocList  [结果集]
     */
    public function detail($doc_id = 0)
    {
        // 查询文章详情
        $ids       = $doc_id;
        $docSearch = new SearchDoc;
        $doc       = $docSearch->getInfo($ids);

        // // 分类
        // $category = [];
        // if (!empty($doc['_source']['category']) == true) {
        //     $category = $doc['_source']['category'];
        // }

        // // 标签
        // $tags = [];
        // if (!empty($doc['_source']['tag']) == true) {
        //     $tags = $doc['_source']['tag'];
        // }

        // // 地区
        // $area = [];
        // if (!empty($doc['_source']['area']) == true) {
        //     $area = $doc['_source']['area'];
        // }
        
        // // 来源
        // $source = [];
        // if (!empty($doc['_source']['source']['name']) == true) {
        //     $source = $doc['_source']['source']['name'];
        // }

        // // 情感
        // $emotion = []
        // if (!empty($doc['_source']['emotion']) == true) {
        //     $emotion = $doc['_source']['emotion'];
        // }

        // 截图地址
        if ($doc['_source']['urls']) {
            $url_arr = explode('thumbnail:', $doc['_source']['urls']);
            if (count($url_arr) > 1) {
                $doc['_source']['urls'] = $url_arr['1'];
            }
        }

        // 标题处理
        if(empty($doc['_source']['title'])){
            $doc['_source']['title'] = '微博文章';
        }

        $info = $doc['_source'];

        // 时间处理
        $info['publish_time'] = date('Y-m-d H:i:s', $info['publish_time']);

        // 情感度处理
        $info['emotion']['positive'] = $info['emotion'][0]['positive'] * 100;
        $info['emotion']['negative'] = $info['emotion'][0]['negative'] * 100;
        unset($info['emotion']['0']);
        
        unset($info['create_time']);
        unset($info['source_id']);
        
        return $info;
    }
}
