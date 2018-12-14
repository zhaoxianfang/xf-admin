<?php
namespace app\index\controller;

use app\common\controller\Base;
use think\Collection;
use util\Menu;

class IndexBase extends Base
{
    public function initialize()
    {
    	parent::initialize();
    	//系统标题
        $this->view->config = config('sys.');

        $this->getNav();
    }

    public function getNav()
    {
        // 菜单
        $list = $this->logicArticleClassify->getList(['status'=>1,'show_home'=>1],'','*,name as title,CONCAT("/index/article/index/vol/",id) as name');
        $arrList = Collection::make($list)->toArray();
        $urlLink = $this->request->controller() . '/' . $this->request->action();
        $navStr = Menu::instance()->init($arrList)->setActiveMenu($urlLink)->setWeigh(false)->createMenu(0,'home');
        $this->view->assign('homenav', $navStr);
    }

}
