<?php
// +---------------------------------------------------------------------
// | 后台管理员
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\logic;

use util\Random;

/**
 * 系统通用逻辑模型
 */
class User extends AdminLogic
{
    // 姓别转义 各个平台 返回的值不一样
    protected $genderArr = array(
        '男' => 1,
        '女' => 0,
        'm' => 1,
        'w' => 0,
    );

    //超级管理员id
    protected $super_admin_id = SYS_ADMINISTRATOR_ID;

    /**
     * 设置登录人信息
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-13
     * @param    [type]       $where [description]
     * @param    array        $data  [description]
     */
    public function setMemberValue($where, $data = [])
    {
        if (!isset($where['id'])) {
            $where['id'] = session($this->logicAuth->getSessionPrefix().'.id');
        }
        $data['last_login_time'] = time();
        $data['last_login_ip']   = request()->ip();
        return $this->updateInfo($where, $data);
    }

    /**
     * 获取用户列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-13
     * @return   [type]       [description]
     */
    public function getListData($where = [], $sort = 'u.create_time', $order = 'desc', $offset = 0, $limit = 15)
    {

        $join = [['auth_group_access aga', 'aga.uid = u.id'], ['auth_group ag', 'ag.id = aga.group_id']];
        
        $order = [$sort => $order];
        $limit = $offset . ',' . $limit;
        $alias = 'u';
        return $this->modelUser->getList($where, $order, 'u.*,ag.name as group_name,ag.id as group_id', $limit, $alias, $join);
    }

    public function countListData($where = [])
    {
        $join = [['auth_group_access aga', 'aga.uid = u.id'], ['auth_group ag', 'ag.id = aga.group_id']];
        $alias = 'u';
        return $this->modelUser->getCount($where, $alias, $join);
    }

    /**
     * 获取用户信息 [关联查询 标识符为 u]
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $where [description]
     * @return   [type]              [description]
     */
    public function getUserInfo($where = [])
    {
        $join = [['auth_group_access aga', 'aga.uid = u.id'], ['auth_group ag', 'ag.id = aga.group_id']];
        return $this->getRow($where, 'u.*,ag.name as group_name,ag.id as group_id', 'u', $join);
    }

    public function updataUser($data = [])
    {
        //检测是否可操作
        $checkResult = $this->checkUserRule($data["id"]);
        if ($checkResult !== true) {
            return $checkResult;
        }

        $groupId = '';
        if (isset($data["group_id"])) {
            $groupId = $data["group_id"];
            unset($data["group_id"]);
        }
        if ((isset($data["password"]) && empty($data["password"])) || !$data["password"]) {
            unset($data["password"]);
        } else {
            //生成密码
            $data['salt']     = Random::nozero(6);
            $data['password'] = setPwd($data['password'], $data['salt']);
        }

        try {
            //更新用户信息
            $this->modelUser->updateRow($data);
            //更新用户组信息
            if ($groupId) {
                $this->logicAuthGroupAccess->save(['group_id' => $groupId],['uid'=>$data["id"]]);
            }
            return [10, '更新成功'];
        } catch (\Exception $e) {
            $errInfo = $e->getMessage();
            if ($errInfo == 'miss update condition') {
                return [10, '更新成功'];
            }
            return [11, $errInfo];
        }
    }

    /**
     * 删除用户信息
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $where [description]
     * @return   [type]              [description]
     */
    public function delUserInfo($where = [])
    {
        
        if(isset($where['id']) && $where['id'] == $this->super_admin_id){
            return [1, '严重警告：不可删除超管账号'];
        }
        //检测是否可操作
        $checkResult = $this->checkUserRule($where["id"]);
        if ($checkResult !== true) {
            return $checkResult;
        }
        try {
            $this->modelUser->delRow($where);
            if (isset($where['id'])) {
                $this->logicAuthGroupAccess->delRow(['uid' => $where['id']]);
            }
            return [0, '删除成功'];
        } catch (\Exception $e) {
            return [1, $e->getMessage()];
        }
    }

    /**
     * [checkUserRule 用户组用户禁止比自己等级高的用户  上级 用户]
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-29
     * @param    [type]       $modifiedId [被编辑用户id ]
     * @return   [type]                   [description]
     */
    protected function checkUserRule($modifiedId = 0)
    {
        $userInfo = session('admin');
        $userId   = $userInfo['id'];

        if ($modifiedId == $userId || $userId == $this->super_admin_id) {
            //自己修改自己的信息 或者是超管来操作数据
            return true;
        }
        //查出 当前操作人的信息和被 操作人的信息
        $usersInfo = $this->getListData(['u.id' => [$userId, $modifiedId]], true);

        if (count($usersInfo) < 2) {
            return [1, '该操作无效'];
        }

        $myGroupId = $modifiedGroupId = 0;
        foreach ($usersInfo as $key => $user) {
            if ($user['id'] == $userId) {
                $myGroupId = $user['group_id'];
            } else {
                //被操作者分组id
                $modifiedGroupId = $user['group_id'];
            }
        }
        if (!$myGroupId || !$modifiedGroupId) {
            return [1, '该操作无效'];
        }
        if ($myGroupId == $modifiedGroupId) {
            return [1, '不可操作同组人员信息'];
        }
        //判断分组
        // $auth_group_list = create_closure($this->logicAuthGroup, 'all',['status'=>1]);
        $auth_group_list = $this->findGroupId();

        $myParentGroupIds = $modifiedParentGroupIds = [];
        $idOne = $myGroupId;
        $idTwo = $modifiedGroupId;
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
        $array_intersect = array_intersect($myParentGroupIds,$modifiedParentGroupIds);
        //如果交集等于自己 则为自己的下级或者下下级等
        $array_diff = array_diff($myParentGroupIds,$array_intersect);
        if(empty($array_diff)){
            return true;
        }
        return [1, '你无权对该用户信息进行操作'];
    }

    protected function findGroupId()
    {
        $tempArr = cache('all_auth_group_list');
    
        if (empty($tempArr)) {
            
            $tempArr = [];
            $list    = $this->logicAuthGroup->getList(['status'=>1]);
            foreach ($list as $key => $value) {
                $tempArr[$value['id']] = $value;
            }
            !empty($tempArr) && cache('all_auth_group_list', $tempArr, 3600);
        }
        return $tempArr;
    }

    /**
     * [getSelectUserInfo 下拉选择用户信息获取]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-11
     * @return   [type]       [description]
     */
    public function getSelectUserInfo()
    {
        $where = ['status'=>1];
        return $this->modelUser->getList($where, $order = '', 'id,name',false, []);
    }

}
