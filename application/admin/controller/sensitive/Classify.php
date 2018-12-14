<?php
// +---------------------------------------------------------------------
// | 敏感词分类管理
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller\sensitive;

use app\admin\controller\AdminBase;

class Classify extends AdminBase
{
    protected $noNeedRight       = ['getclassifylist', 'getlevellist'];
    protected $noNeedLogin       = [''];
    protected $showContentHeader = false;

    /**
     * 基础控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-12
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();
        $this->title = '敏感词分类管理';

        //获取节点树
        if (ACTION_NAME == 'index') {
            $list = $this->logicSensitiveClassify->getListData(['sc.status' => 1]);
        } else {
            $list = $this->logicSensitiveClassify->getListDataTree(['sc.status' => 1]);
        }
        $this->view->assign('nodelist', $list);
        $this->view->assign('levellist', $this->logicSensitiveClassify->getLevelList());
    }

    public function index()
    {
        if ($this->request->isAjax() && !$this->request->isPjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->getTableSearchParam('sc.name');

            $list      = $this->logicSensitiveClassify->getListData($where, $sort, $order, $offset, $limit);
            $countList = $this->logicSensitiveClassify->countListData($where);

            return json(['rows' => $list, 'total' => $countList]);
        }
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $this->jump($this->logicSensitiveClassify->addData($row));
        }

        return $this->fetch();
    }

    public function edit()
    {
        $id = $this->request->param('ids', 0, 'intval');
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $this->jump($this->logicSensitiveClassify->updateRow($row));
        }
        $info = $this->logicSensitiveClassify->getRow(['id' => $id]);
        $this->view->assign('info', $info);
        return $this->fetch();
    }

    public function del()
    {
        $id = $this->request->param('ids', 0, 'intval');
        if (!$id || !$this->request->isPost()) {
            $this->jump([1, '非法请求']);
        }
        $this->jump($this->logicSensitiveClassify->delRow(['id' => $id]));
    }

    public function getClassifyList()
    {
        $list = $this->logicSensitiveClassify->getListDataTree(['sc.status' => 1]);
        return json($list);
    }

    /**
     * 获取敏感词级别
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-12
     * @return   [type]       [description]
     */
    public function getLevelList()
    {
        return json($this->logicSensitiveClassify->getLevelList());
    }
}
