<?php
// +---------------------------------------------------------------------
// | 系统权限管理
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller\system;
use app\admin\controller\AdminBase;

class Authrule extends AdminBase
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
            list($where, $sort, $order, $offset, $limit) = $this->getTableSearchParam('title');

            $list   = $this->logicAuthRule->getListData($where);

            return json(['rows' => $list, 'total' => count($list)]);
        }
        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if($this->request->isPost()){
            $row = $this->request->param('row/a');
            $info = $this->logicAuthRule->addRow($row);
            if($info === false){
                $this->jump([1,'操作失败']);
            }else{
                $this->jump([10,'操作成功']);
            }
        }
        //获取权限菜单节点树
        $authRuleList = $this->logicAuthRule->getListData(['ismenu'=>1,'status'=>1]);
        $this->view->assign('nodelist',$authRuleList);
        
        return $this->fetch();
    }

    /**
     * 编辑
     */
    public function edit()
    {
        if($this->request->isPost()){
            $row = $this->request->param('row/a');
            $info = $this->logicAuthRule->updateRow($row);
            if($info === false){
                $this->jump([1,'操作失败']);
            }else{
                $this->jump([10,'操作成功']);
            }
        }
        $id = $this->request->param('ids',0,'intval');
        $ruleInfo = $this->logicAuthRule->where('id', $id)->find();
        if(!$ruleInfo){
            $this->jump([1,'没有找到结果']);
        }
        //获取权限菜单节点树
        $authRuleList = $this->logicAuthRule->getListData(['ismenu'=>1,'status'=>1]);
        $this->view->assign('nodelist',$authRuleList);
        $this->view->assign('ruleinfo',$ruleInfo);
       
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
        $id = $this->request->param('ids',0,'intval');
        if(!$id || !$this->request->isPost() ){
            $this->jump([1,'非法请求']);
        }
        $info = $this->logicAuthRule->delRow(['id'=>$id]);
        if($info === false){
            $this->jump([1,'操作失败']);
        }else{
            $this->jump([0,'操作成功']);
        }
    }


}
