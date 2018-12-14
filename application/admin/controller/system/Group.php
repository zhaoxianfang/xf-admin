<?php
// +---------------------------------------------------------------------
// | 系统用户组管理
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller\system;

use app\admin\controller\AdminBase;

class Group extends AdminBase
{
    protected $noNeedRight = ['gettree','getgrouplist'];
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

            $logList   = $this->logicAuthGroup->getListData($where);
            $countList = $this->logicAuthGroup->countListData($where);

            return json(['rows' => $logList, 'total' => $countList]);
        }
        return $this->fetch();
    }

    public function getGroupList()
    {
        $where = ['status' => 1];
        $list  = $this->logicAuthGroup->getList($where, '', 'id,name', false, []);
        return json($list);
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            if (!isset($row['rules']) || empty($row['rules'])) {
                $this->jump([1, '未提交权限节点']);
            }
            if (!isset($row['rules']) || empty($row['name'])) {
                $this->jump([1, '未填写用户组名称']);
            }

            $this->jump($this->logicAuthGroup->addRow($row));
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
            $this->jump($this->logicAuthGroup->editGroup($row));
        }
        $id   = $this->request->param('ids', 0, 'intval');
        $info = $this->logicAuthGroup->getRow(['id' => $id]);
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
            $this->jump(['1', '非法请求']);
        }
        $info = $this->logicAuthGroup->delRow(['id' => $id]);
        if ($info === false) {
            $this->jump([1, '操作失败']);
        } else {
            $this->jump([0, '操作成功']);
        }
    }

    /**
     * 获取节点树
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-12
     * @return   [type]       [description]
     */
    public function gettree()
    {
        $groupid   = $this->request->param('id', 0, 'intval');
        $groupinfo = $this->logicAuthGroup->getRow(['id' => $groupid]);

        //获取权限列表
        $rulelist = $this->logicAuthRule->getListData(['status' => 1]);
        //已选节点
        $checkArr = explode(',', $groupinfo['rules']);

        $rule = [];
        foreach ($rulelist as $key => $value) {
            $rule[$key]['id']   = $value['id'];
            $rule[$key]['pId']  = $value['pid'];
            $rule[$key]['name'] = $value['title'];

            if (in_array($value['id'], $checkArr)) {
                $rule[$key]['checked'] = true;
            }
        }

        return json(['status' => 1, 'data' => $rule, 'info' => '获取成功']);
    }

}
