<?php
// +---------------------------------------------------------------------
// | 敏感词 分类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\logic;

use think\Collection;
use util\Menu;

class SensitiveClassify extends AdminLogic
{
    //敏感词划分的级别
    protected $levelList = [
        '1' => '一般',
        '2' => '警戒',
        '3' => '严重',
        '4' => '特级',
    ];

    /**
     * 获取列表
     */
    public function getListData($where = [], $sort = 'sc.create_time', $order = 'desc', $offset = 0, $limit = 15)
    {
        $join  = ['user u', 'sc.uid = u.id'];
        $order = [$sort => $order];
        $limit = $offset . ',' . $limit;
        $alias = 'sc';

        return $this->modelSensitiveClassify->getList($where, $order, 'sc.*,u.name as user_name,u.id as user_id', $limit, $alias, $join);
    }

    public function getListDataTree($where = [], $sort = 'sc.create_time', $order = 'desc', $offset = 0, $limit = 15)
    {
        $join  = ['user u', 'sc.uid = u.id'];
        $order = [$sort => $order];
        $limit = $offset . ',' . $limit;
        $alias = 'sc';

        $list = $this->modelSensitiveClassify->getList($where, $order, 'sc.*,u.name as user_name,u.id as user_id', $limit, $alias, $join);

        $arrList = Collection::make($list)->toArray();
        // 菜单转换为视图
        $grouplistarr = Menu::instance()->init($arrList)->setWeigh()->setTitle('name')->getTree();

        foreach ($grouplistarr as $key => $value) {
            $pidArr[] = $value['pid'];
        }
        foreach ($grouplistarr as $key => &$value) {
            $value['has_child'] = (in_array($value['id'], $pidArr)) ? 1 : 0;
        }
        unset($pidArr);
        return $grouplistarr;
    }

    public function countListData($where = [])
    {
        $join  = ['user u', 'sc.uid = u.id'];
        $alias = 'sc';

        return $this->modelSensitiveClassify->getCount($where, $alias, $join);
    }

    public function addData($row)
    {
        $row['create_time'] = time();
        $row['uid']         = session('admin.id');
        return $this->modelSensitiveClassify->addRow($row);
    }

    public function getLevelList()
    {
        return $this->levelList;
    }
}
