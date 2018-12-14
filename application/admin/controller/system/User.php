<?php
// +---------------------------------------------------------------------
// | 系统用户(或管理员)管理
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use util\Random;

class User extends AdminBase
{
    protected $noNeedRight = ['getuserlist'];
    protected $noNeedLogin = [];

    /**
     * 权限控制控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        if ($this->request->isAjax() && !$this->request->isPjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->getTableSearchParam('u.name');

            $logList   = $this->logicUser->getListData($where, $sort, $order, $offset, $limit);
            $countList = $this->logicUser->countListData($where);

            return json(['rows' => $logList, 'total' => $countList]);
        }
        return $this->fetch();
    }

    //所有用户
    public function getUserList()
    {
        $userlist = $this->logicUser->getSelectUserInfo();
        return json($userlist);
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');

            $groupId = $row['group_id']; //所属组
            unset($row['group_id']);

            //生成密码
            $row['salt']        = Random::nozero(6);
            $row['uuid']        = Random::uuid();
            $row['password']    = setPwd($row['password'], $row['salt']);
            $row['create_time'] = time();

            $userId = $this->logicUser->addRow($row);
            if (is_array($userId)) {
                $this->jump($userId);
            }
            //添加分组
            $addinfo = $this->logicAuthGroupAccess->addRow(['uid' => $userId, 'group_id' => $groupId]);
            if ($addinfo === false) {
                $this->jump([1, '操作失败']);
            } else {
                $this->jump([10, '操作成功']);
            }
        }
        //获取用户组节点树
        $groupList = $this->logicAuthGroup->getListData();
        $this->view->assign('grouplist', $groupList);

        return $this->fetch();
    }

    /**
     * 编辑
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $this->jump($this->logicUser->updataUser($row));
        }
        $id   = $this->request->param('ids', 0, 'intval');
        $info = $this->logicUser->getUserInfo(['u.id' => $id]);
        if (!$info) {
            $this->jump([11, '没有找到结果']);
        }

        //获取用户组节点树
        $groupList = $this->logicAuthGroup->getListData();

        $this->view->assign('grouplist', $groupList);
        $this->view->assign('info', $info);

        return $this->fetch();
    }

    /**
     * 删除权限
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-11
     * @return   [type]       [description]
     */
    public function del()
    {
        $id = $this->request->param('ids', 0, 'intval');
        if (!$id || !$this->request->isPost()) {
            $this->jump([1, '非法请求']);
        }
        $this->jump($this->logicUser->delUserInfo(['id' => $id]));
    }

}
