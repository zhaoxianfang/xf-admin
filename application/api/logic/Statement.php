<?php
// +----------------------------------------------------------------------
// | 报道统计逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-23
// +----------------------------------------------------------------------

namespace app\api\logic;

use think\Config;
use app\api\library\Auth;
use app\common\elastic\service\Doc as SearchDoc;
use app\common\redis\Statement as RedisStatement;
use app\common\elastic\service\Statement as SearchStatement;

/**
 * 报道统计
 */
class Statement extends ApiLogic
{
    /**
     * 获取报道统计
     * @DateTime 2018-08-23
     * @param    [int]    $user_id  用户ID
     * @param    [int]    $limit   [限制条数]
     * @param    [int]    $page    [分页页码]
     * @return   [array]  $list     查询结果集
     */
    public function getList($user_id = 0, $limit = 10, $page = 1)
    {
        
        $map['status']      = 1;
        $map['user_id']     = $user_id;

        // // ES数据
        // $map['sync_status'] = 1;
        // $sort = ['create_time' => 'DESC'];
        // // $field['include'] = ['statement_id', 'title'];
        // // 实例化报道统计类查询结果
        // $statement = new SearchStatement();
        // $list = $statement->getList($map, $sort, 0, $limit);

        $field = 'statement_id, title, stat_template_id, start_time, end_time';

        $list = $this->modelStatement
            ->where($map)
            ->field($field)
            ->page($page, $limit)
            ->select()
        ;

        // 字段处理
        foreach ($list as $key => $value) {
            $list[$key]['start_time'] = date('Y-m-d H:i', $value['start_time']);
            $list[$key]['end_time']   = date('Y-m-d H:i', $value['end_time']);
        }

        return $list;
    }

    /**
     * 获取报道统计模板
     * @DateTime 2018-08-23
     * @param    [int]    $user_id  用户ID
     * @param    [int]    $limit   [限制条数]
     * @param    [int]    $page    [分页页码]
     * @return   [array]  $list     查询结果集
     */
    public function getTemplateList($user_id = 0, $limit = 10, $page = 1)
    {
        $map['status']  = 1;
        $map['user_id'] = $user_id;

        $list = $this->modelStatTemplate
            ->field('template_id, title, create_time')
            ->where($map)
            ->page($page, $limit)
            ->select()
        ;

        return $list;
    }

    /**
     * 修改报道统计
     * @DateTime 2018-08-24
     * @param    [array]  $title    修改标题
     * @param    [int]    $user_id  用户ID
     * @param    [int]    $statement_id  修改数据ID
     * @return   [array]  $list     数据结果
     */
    public function edit($title = '', $user_id = 0, $statement_id = 0)
    {

        $map['status']          = 1;
        $map['user_id']         = $user_id;
        $map['statement_id']    = $statement_id;

        $info = $this->modelStatement->where($map)->find();
        if (empty($info) == true) {
            return $this->apiError(['code' => 10001, 'msg' => '没有数据']);
        }

        $result = $this->modelStatement->where($map)->update(['title' => $title]);

        return $result;
    }

    /**
     * 新增报道统计
     * @DateTime 2018-08-24
     * @param    [array]  $data     新增数据
     * @param    [int]    $user_id  用户ID
     * @return   [array]  $list     数据结果
     */
    public function add($data = [], $user_id = 0)
    {       
        
        // 判断时间限制
        if ($data['start_time'] >= $data['end_time']) {
            return $this->apiError(['code' => 10007, 'msg' => '结束时间需大于开始时间']);
        }

        // 获取系统限制最大时间差
        $time_gap = Config::get("site.reports_time_set") ? Config::get("site.reports_time_set") : 0;
        if ($time_gap) {
            if($data['end_time'] - $data['start_time'] > $time_gap){
                return $this->apiError(['code' => 40002, 'msg' => '选择时间范围超过了系统设定的时间差']);
            }
        }

        // 限制模板权限
        $map['template_id'] = $data['template_id'];
        $map['user_id']     = $user_id;

        $template_info = $this->modelStatTemplate->where($map)->find();
        if(empty($template_info) == true){
            return $this->apiError(['code' => 40001, 'msg' => '报表模板不存在']);
        }

        $data = [
            'title'            => strip_tags($data['title']),
            'user_id'          => $user_id,
            'create_time'      => time(),
            'stat_template_id' => $data['template_id'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
        ];

        $result = $this->modelStatement->insert($data);
        
        if ($result === false) {
            return $this->apiError(['code' => 10004, 'msg' => '操作失败']);
        }

        return $result;
    }

    /**
     * 删除报告统计
     * @DateTime 2018-08-24
     * @param    [int]    $statement_id   删除数据ID
     * @param    [int]    $user_id        用户ID
     * @return   [bool]   $result         数据结果
     */
    public function delete($statement_id = 0, $user_id = 0)
    {
        $map['user_id']         = $user_id;
        $map['statement_id']    = $statement_id;

        // 是否有权限删除
        $info = $this->modelStatement->where($map)->find();

        if (empty($info) == true) {
            return $this->apiError(['code' => 10001, 'msg' => '没有数据']);
        }

        $result = $this->modelStatement
            ->where($map)
            ->update(['status' => '-1'])
        ;

        return $result;
    }

    /**
     * 查看报道数据列表
     * @DateTime 2018-08-29
     * @param    [int]    $statement_id   删除数据ID
     * @param    [int]    $user_id        用户ID
     * @param    [int]    $limit          限制条数
     * @param    [int]    $page           分页页码
     * @return   [type]   $list           [数据结果]
     */
    public function detail($statement_id = 0, $user_id = 0, $limit = 10, $page = 1)
    {
       
        list($map, $sort, $fields, $userFields) = $this->getDetailMap($statement_id);
        $doc = new SearchDoc;
        try {

            // 分页数据处理
            $offset = $limit * ($page - 1);
            $list   = $doc->getList($map, $sort, $offset, $limit, $fields);

            $dataList = [];
            $number   = 1;

            foreach ($list['list'] as $key => $item) {
                
                foreach ($fields['incluce'] as $k => $field) {
                   
                    switch ($field) {
                        case '':
                            $dataList[$key]['column'][]= [
                                'name'  =>'自定义',
                                'value' =>''
                            ];
                            break;
                        case 'area':
                            $dataList[$key]['column'][]= [
                                'name'  =>$field,
                                'value' =>$item['area'][0]["name"]
                            ];
                            break;
                        case 'area_id':
                            $dataList[$key]['column'][]= [
                                'name'  =>$field,
                                'value' =>$item['area_id'][0]
                            ];
                            break;
                        case 'department':
                            $dataList[$key]['column'][]= [
                                'name'  =>$field,
                                'value' =>isset($item['department'][0])?$item['department'][0]:''
                            ];
                            break;
                        case 'source':
                            $dataList[$key]['column'][]= [
                                'name'  =>$field,
                                'value' =>$item['source']["name"]
                            ];
                            break;
                        case 'emotion':
                            $dataList[$key]['column'][]= [
                                'name'  =>$field,
                                'value' =>$item['emotion'][0]["positive"]
                            ];
                            break;
                         case 'publish_time'://发布时间
                            $dataList[$key]['column'][]= [
                                'name'  =>$field,
                                'value' =>date('Y-m-d H:i',$item['publish_time'])
                            ];
                            break;
                        default:
                            $dataList[$key]['column'][]= [
                                'name'  =>$field,
                                'value' =>$item[$field]
                            ];
                            break;
                    }
                    
                }

                $dataList[$key]['doc_id']= $item['doc_id'];
                $dataList[$key]['url']= $item['url'];
                
                if (!empty($userFields)) {

                    foreach ($userFields as $key_temp => $value) {
                        
                        switch ($value) {
                            case 'number':
                                $dataList[$key]['column'][]= [
                                    'name'  =>$value,
                                    'value' =>$number
                                ];
                                $number = $number + 1;
                                break;
                            case 'user_id':
                                // 查询用户信息
                                $dataList[$key]['column'][]= [
                                    'name'  =>$value,
                                    'value' =>$this->modelUser->where(['user_id' => $user_id])->value('nickname')
                                ];
                                break;
                            case 'create_time':
                                $dataList[$key]['column'][]= [
                                    'name'  =>$value,
                                    'value' =>date('Y-m-d')
                                ];
                                break;
                            default:
                                # code...
                                break;
                        }
                    }
                }
            }

            return $dataList;
        
        } catch (\Exception $e) {
            
            return $this->apiError(['code' => 40004, 'msg' => '获取详情数据异常']);
        
        }
    }


    /**
     * [deldetail 删除报表中的某条数据]
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-06
     * @param    int      $statement_id [报道ID]
     * @param    int      $doc_id       [文章ID]
     * @return   [bool]                 [操作结果]
     */
    public function deldetail($statement_id = 0, $doc_id = 0)
    {
        // 包含和排除的字段
        $mStatSubjoin = $this->modelStatSubjoin;

        $statSubjoin = $mStatSubjoin->get(['statement_id' => $statement_id]);
        
        if ($statSubjoin) {
            $updataArr = [];
            if ($statSubjoin->include_ids) {
                // 包含
                $include_ids = explode(",", $statSubjoin->include_ids);
                if (in_array($article_ids, $include_ids)) {
                    // 删除元素
                    $include_ids = array_merge(array_diff($include_ids, array($article_ids)));
                    $updataArr['include_ids'] = implode(',', $include_ids);
                }
            }
            if ($statSubjoin->exclude_ids) {
                // 排除
                $exclude_id = explode(",", $statSubjoin->exclude_ids);
                if (!in_array($article_ids, $exclude_id)) {
                    // 不在排除范围
                    $exclude_id[] = $article_ids;
                    $updataArr['exclude_ids'] = implode(',', $exclude_id);
                }
            }
            $result = $mStatSubjoin->where('statement_id',$statement_id)->update($updataArr);

        } else {
            // 没有该报表数据
            $result = $mStatSubjoin->insert(['statement_id' => $statement_id, 'exclude_ids' => $article_ids]);
            
        }
        if ($result === false) {
            return $this->apiError(['code' => 10004, 'msg' => '操作失败']);
        }
        return $result;
    }

    protected function getDetailMap($ids = 0)
    {
        //报表中记录了起止时间
        $row = $this->modelStatement->where(['statement_id' => $ids])->find();
        //模板中记录地区、情感、分类、数据源
        $stat_template = $this->modelStatTemplate->where(['template_id' => $row['stat_template_id']])->find();
        //模板中记录的字段
        $stat_efield = $this->modelStatEfield->where(['template_id' => $stat_template['template_id']])->select();
        
        //查询条件
        $map = [];

        //起止时间
        $map['start_publish_time'] = $row['start_time'];
        $map['end_publish_time'] = $row['end_time'];
        
        //地区
        $areaData = explode(",", $stat_template['area_ids']);
        !empty($areaData[0]) && $map['area_id'] = $areaData;
        
        //情感
        ($stat_template['emotion'] > 0) && $map['emotion_status'] = [$stat_template['emotion']];

        //分类
        $categoryIdsData = explode(",", $stat_template['category_ids']);    
        !empty($categoryIdsData[0]) && $map['category_id'] = $categoryIdsData;

        //数据源
        $sourceIdsData = explode(",", $stat_template['source_ids']);
        !empty($sourceIdsData[0]) && $map['source_id'] = $sourceIdsData;

        $fieldsArr = collection($stat_efield)->toArray();
        //包含和排除的字段
        $statSubjoin = $this->modelStatSubjoin->where(['statement_id' => $ids])->find();

        if($statSubjoin){
            if($statSubjoin->include_ids){
                //包含
                $map['include_id'] = explode(",", $statSubjoin->include_ids);
            }
            if($statSubjoin->exclude_ids){
                //排除
                $map['exclude_id'] = explode(",", $statSubjoin->exclude_ids);
            }
        }

        $incluce_fields = [];

        //用户选择自动生成字段
        $userFields = [];

        //查询字段 fields
        foreach ($fieldsArr as $key => $field) {
            
            if(in_array($field['value'],['number','user_id','create_time'])){
                //用户选择自动生成字段
                $userFields[] = $field['value'];
            }else{
                $incluce_fields[] = $field['value'];
            }
        }
        //指定字段
        if (!empty($incluce_fields)) {
            $fields = ['incluce' => $incluce_fields];
        } else {
            $fields = '';
        }
        
        // 排序
        $sort = ['publish_time' => 'DESC'];
        
        return [$map, $sort, $fields, $userFields];
    }

    /**
     * 获取报道统计信息
     * @DateTime 2018-09-10
     * @param    [int]    $statement_id   数据ID
     * @return   [bool]   $info           数据结果
     */
    public function getInfo($statement_id = 0, $user_id = 0)
    {
        $map['user_id']      = $user_id;
        $map['statement_id'] = $statement_id;
        $map['status']       = 1;

        $info = $this->modelStatement
            ->where($map)
            ->field('statement_id, title, stat_template_id, create_time, start_time, end_time')
            ->find()
        ;
        
        if (!empty($info)) {
            
            $info['stat_title'] = $this->modelStatTemplate
                ->where(['template_id' => $info['stat_template_id']])
                ->value('title')
            ;
            $info['start_time'] = date('Y-m-d', $info['start_time']);
            $info['end_time']   = date('Y-m-d', $info['end_time']);

        }

        return $info;
    }
}