<?php
// +---------------------------------------------------------------------
// | 敏感词汇
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\logic;

class SensitiveWord extends AdminLogic
{
    public function getListData($where = [], $sort = 'sw.create_time', $order = 'desc', $offset = 0, $limit = 15)
    {
        // $join  = ['user u', 'sw.uid = u.id'];
        $join = [['user u', 'sw.uid = u.id'], ['sensitive_classify sc', 'sc.id = sw.classify_id']];
        $order = [$sort => $order];
        $limit = $offset . ',' . $limit;
        $alias = 'sw';

        return $this->modelSensitiveWord->getList($where, $order, 'sw.*,u.name as user_name,u.id as user_id,sc.name as classify_name,sc.level', $limit, $alias, $join);
    }

    public function countListData($where = [])
    {
         $join = [['user u', 'sw.uid = u.id'], ['sensitive_classify sc', 'sc.id = sw.classify_id']];
        $alias = 'sw';

        return $this->modelSensitiveWord->getCount($where, $alias, $join);
    }

    public function addData($row)
    {
        $row['create_time'] = time();
        $row['uid']         = session('admin.id');
        return $this->modelSensitiveWord->addRow($row);
    }
}
