<?php
// +---------------------------------------------------------------------
// | 系统日志管理
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller\system;

use app\admin\controller\AdminBase;

class Log extends AdminBase
{
    protected $noNeedRight = [];
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

            $logList   = $this->logicLog->getListData($where, $sort, $order, $offset, $limit);
            $countList = $this->logicLog->countListData($where);

            return json(['rows' => $logList, 'total' => $countList]);
        }
        return $this->fetch();
    }

}
