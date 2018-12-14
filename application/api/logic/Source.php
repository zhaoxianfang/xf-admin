<?php
// +----------------------------------------------------------------------
// | 文章数据源逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-23
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\api\library\Auth;

/**
 * 文章数据源
 */
class Source extends ApiLogic
{

    /**
     * 获取地区
     * @DateTime 2018-08-23
     * @param    [int]    $user_id  用户ID
     * @param    [int]    $field    限制字段
     * @return   [array]  $list     查询结果集
     */
    public function getList($user_id = 0, $field = '*')
    {
        // 以权限获取
        $this->auth = new Auth;
        $ids = $this->auth->getSourceIds($user_id);

        $temp = $this->modelSource->where('source_id','in',$ids)->field($field)->select();

        // 组装数据
        foreach ($temp as $key => $value) {
            switch ($value['level']) {
                case 1:
                    $list['1'][] = $value;
                    break;
                case 2:
                    $list['2'][] = $value;
                    break;
                case 3:
                    $list['3'][] = $value;
                    break;
                case 4:
                    $list['4'][] = $value;
                    break;
                case 5:
                    $list['5'][] = $value;
                    break;
            }
        }

        return $list;
    }

    public function getFieldBySourceIds($source_ids = [], $field = '*')
    {
        return $this->modelSource->where('source_id','in',$source_ids)->column($field); 
    }
}