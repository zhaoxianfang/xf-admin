<?php
// +---------------------------------------------------------------------
// | 用户组
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
 * 用户组
 */
class AuthGroup extends AdminLogic
{
    //超级管理员所属组 group_id
    protected $super_admin_group_id = 1;
    /**
     * 获取列表
     */
    public function getListData($where = [])
    {

        $groupList = $this->modelAuthGroup->getList($where, '', '*,name as title');
        $arrList = Collection::make($groupList)->toArray();
        // 菜单转换为视图
        $grouplistarr = Menu::instance()->init($arrList)->setWeigh()->getTree();

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
        return $this->modelAuthGroup->getCount($where);
    }

    /**
     * 编辑信息
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-12
     * @param    array        $row [提交的表单数据]
     * @return   [type]            [description]
     */
    public function editGroup($row = [])
    {
        if(!isset($row['id']) || !$row['id']){
            return [1, '无效的操作'];
        }
        //不能操作比自己级别高的组
        $userInfo    = session('admin');
        $userId      = $userInfo['id']; //操作人的id
        $userGroupId = $userInfo['group_id']; //操作人的分组id
        if ($row['id'] == $row['pid']) {
            if ($row['id'] == $this->super_admin_group_id && $userId == $this->logicUser->super_admin_id) {
                $row['pid'] = 0;
            }else{
                return [1, '1.无权操作 <br />2.所属分组和父级组不能相同'];
            }
        }
        //不是超管，判断使其不可超过级别比自己大的组
        if ($userId != $this->logicUser->super_admin_id) {
            if ($row['id'] == $userGroupId) {
                return [1, '你无权修改自己所在组的权限'];
            }
            $auth_group_list = $this->findAllGroupId();

            $myParentGroupIds = $modifiedParentGroupIds = [];
            $idOne            = $userGroupId;
            $idTwo            = $row['id'];
            while (isset($auth_group_list[$idOne]) || isset($auth_group_list[$idTwo])) {
                if (isset($auth_group_list[$idOne])) {
                    $myParentGroupIds[] = $auth_group_list[$idOne]['id'];
                    $idOne              = $auth_group_list[$idOne]['pid'];
                } else {
                    $modifiedParentGroupIds[] = $auth_group_list[$idTwo]['id'];
                    $idTwo                    = $auth_group_list[$idTwo]['pid'];
                }
            }
            //交集
            $array_intersect = array_intersect($myParentGroupIds, $modifiedParentGroupIds);
            //如果交集等于自己 则为自己的下级或者下下级等
            $array_diff = array_diff($myParentGroupIds, $array_intersect);
            if (!empty($array_diff)) {
                return [1, '你无权对该用户组进行操作'];
            }
        }
        //正式写入数据
        $updatainfo = $this->modelAuthGroup->updateRow($row);
        if ($updatainfo === false) {
            return [1, '编辑失败'];
        } else {
            return [10, '编辑成功'];
        }

    }

    protected function findAllGroupId()
    {
        $tempArr = cache('all_auth_group_list');

        if (empty($tempArr)) {

            $tempArr = [];
            $list    = $this->modelAuthGroup->getList(['status' => 1]);
            foreach ($list as $key => $value) {
                $tempArr[$value['id']] = $value;
            }
            !empty($tempArr) && cache('all_auth_group_list', $tempArr, 3600);
        }
        return $tempArr;
    }
}
