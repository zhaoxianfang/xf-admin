<?php
// +---------------------------------------------------------------------
// | 权限
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\logic;

use think\Collection;
use util\Menu;

/**
 * 权限列表逻辑
 */
class AuthRule extends AdminLogic
{
    /**
     * 获取列表
     */
    public function getListData($where = [])
    {
        $ruleList = $this->modelAuthRule->getList($where);
        $arrList  = Collection::make($ruleList)->toArray();
        // 菜单转换为视图
        $menulist = Menu::instance()->init($arrList)->setUrlPrefix('admin/')->getTree();

        return $menulist;
    }

}
