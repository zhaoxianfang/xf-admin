<?php
// +----------------------------------------------------------------------
// | 两微考核逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-24
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\api\library\Auth;
use think\Db;

/**
 * 两微考核
 */
class Wei extends ApiLogic
{

    /**
     * 获取考核列表
     * @DateTime 2018-08-24
     * @param    [int]    $user_id  用户ID
     * @param    [int]    $limit    [限制条数]
     * @param    [int]    $page     [分页页码]
     * @return   [array]  $list     查询结果集
     */
    public function getList($user_id = 0, $limit = 10, $page = 1)
    {
        $map['at.status']  = 1;
        $map['ar.status']  = 1;
        $map['ar.user_id'] = $user_id;

        $list = $this->modelAssessResult
            ->alias('ar')
            ->field('ar.*, at.template_title')
            ->where($map)
            ->join("assess_template at", 'at.template_id = ar.template_id')
            ->page($page, $limit)
            ->select()
        ;

        // 补全数据
        foreach ($list as $key => &$value) {
            $value['childcount'] = $this->modelAssessResultData->where('result_id', $value->result_id)->count();

            // 时间处理
            $list[$key]['result_start_time'] = date('Y-m-d H:i', $value['result_start_time']);
            $list[$key]['result_end_time']   = date('Y-m-d H:i', $value['result_end_time']);
        }

        return $list;
    }

    /**
     * 获取考核列表详情文章
     * @DateTime 2018-08-24
     * @param    [int]    $result_id  数据ID
     * @return   [array]  $list       查询结果集
     */
    public function detail($result_id = 0, $limit = 10, $page = 1)
    {
        $map['result_id'] = $result_id;
        $map['status']    = 1;

        // 查询考核结果所需字段
        $info_result = $this->modelAssessResult
            ->where($map)
            ->field('result_reid, template_id')
            ->find()->toArray()
        ;

        $info_field = $info_result['result_reid'];
        $info_temp  = $info_result['template_id'];
        
        $temp_info = db('assess_result_data')
            ->where($map)
            ->field('result_id, acount ,score, '.$info_field)
            ->page($page, $limit)
            ->select()
        ;

        $info_field = explode(',', $info_field);
        
        $info = [];

        // 组装数据
        foreach ($temp_info as $key => $value) {

            foreach ($value as $k => $v) {
                // 处理需要获取数据
                if (in_array($k, $info_field)) {
                    
                    $where['template_id'] = $info_temp;
                    $where['value']       = $k;
                    $where['status']      = 1;

                    $temp_info[$key][] = [
                        'field' => $k,
                        'name'  => $this->modelAssessTemplateStandard
                            ->where($where)
                            ->value('title'),
                        'num'   => $v,
                    ];

                    unset($temp_info[$key][$k]);
                }
            }

            // 更改结构
            $info[$key]['result_id'] = $value['result_id'];
            unset($temp_info[$key]['result_id']);
            $info[$key]['acount']    = $value['acount'];
            unset($temp_info[$key]['acount']);
            $info[$key]['score']     = $value['score'];
            unset($temp_info[$key]['score']);

            $info[$key]['column'] = $temp_info[$key];
        }
        return $info;
    }

    /**
     * 删除考核信息
     * @DateTime 2018-08-24
     * @param    [int]    $result_id   删除数据ID
     * @param    [int]    $user_id        用户ID
     * @return   [bool]   $result         数据结果
     */
    public function delete($result_id = 0, $user_id = 0)
    {
        $map['user_id']      = $user_id;
        $map['result_id']    = $result_id;

        // 是否有权限删除
        $info = $this->modelAssessResult->where($map)->find();

        if (empty($info) == true) {
            return $this->apiError(['code' => 10001, 'msg' => '没有数据']);
        }

        // 启动事务
        Db::startTrans();
        try{

            $result = $this->modelAssessResult
                ->where($map)
                ->update(['status' => 0])
            ;
            unset($map['user_id']);
            $result_data = $this->modelAssessResultData
                ->where($map)
                ->update(['status' => 0])
            ;

            // 提交事务
            Db::commit();    
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $this->apiError(['code' => 10004, 'msg' => '操作失败']);
        }

        return $result;
    }
}