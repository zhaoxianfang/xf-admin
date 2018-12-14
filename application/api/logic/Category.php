<?php
// +----------------------------------------------------------------------
// | 文章标签逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-23
// +----------------------------------------------------------------------

namespace app\api\logic;

/**
 * 文章标签
 */
class Category extends ApiLogic
{

    /**
     * 获取标签
     * @DateTime 2018-08-23
     * @param    [int]    $field    限制字段
     * @return   [array]  $list     查询结果集
     */
    public function getList($field = '*')
    {
        $where['status']        = 1;
        $where['sync_status']   = 1;

        $list = $this->modelCategory
            ->where($where)
            ->field($field)
            ->select()
        ;

        return $list;
    }
}