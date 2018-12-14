<?php
// +---------------------------------------------------------------------
// | 日志管理
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\logic;

class Log extends AdminLogic
{
    public function getListData($where = [], $sort = 'sl.create_time', $order = 'desc', $offset = 0, $limit = 15)
    {
        $join  = ['user u', 'sl.uid = u.id'];
        $order = [$sort => $order];
        $limit = $offset . ',' . $limit;
        $alias = 'sl';

        // getList($where, $order, $field ,$limit,$alias, $join)
        return $this->modelLog->getList($where, $order, 'sl.*,u.name as user_name,u.id as user_id', $limit, $alias, $join);
    }

    public function countListData($where = [])
    {
        $join  = ['user u', 'sl.uid = u.id'];
        $alias = 'sl';

        return $this->modelLog->getCount($where, $alias, $join);
    }
}
