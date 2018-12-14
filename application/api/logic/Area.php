<?php
// +----------------------------------------------------------------------
// | 文章地区逻辑
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
 * 文章地区
 */
class Area extends ApiLogic
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
        $ids = $this->auth->getAreaIds($user_id);

        foreach ($ids as $key => $value) {
            if ($value == 1) {
                continue;
            }
            $map['area_id'] = $value;
            $list[] = $this->modelArea->where($map)->field($field)->find(); 
        }

        return $list;
    }

    public function getFieldByAreaIds($area_ids = [], $field = '*')
    {
        return $this->modelArea->where('area_id','in',$area_ids)->column($field); 
    }
}