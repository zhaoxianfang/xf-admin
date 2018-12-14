<?php
// +---------------------------------------------------------------------+
// | 爬虫管理    | [ 昆明信息港 ]    [文章管理]                            |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+

namespace app\index\controller;


class Article extends IndexBase
{
    /**
	 * 基础控制
	 * @Author   ZhaoXianFang
	 * @DateTime 2018-06-05
	 * @return   [type]       [description]
	 */
    public function initialize()
    {
    	parent::initialize();
        
    }

    // 文章列表
    public function index()
    {
        //卷标（分类）
        $vol        = $this->request->param('vol',1);
        $where=['a.classify_id'=>$vol,'a.status'=>1];
        $list = $this->logicArticle->getListData($where);
        $this->view->assign('list', $list);
        $this->view->title = '文章列表';

        // 设置面包屑导航
        $this->view->breadcrumb = $this->logicArticleClassify->getBreadCrumb($vol);
        
        return $this->fetch();
    }

    public function page()
    {

        $id        = $this->request->param('id',0);
        $where=['a.id'=>$id,'a.status'=>1];
        $article = $this->logicArticle->getpage($where);
        $this->view->assign('info', $article);
        $this->view->title = $article['title'];
        // 设置面包屑导航
        $this->view->breadcrumb = $this->logicArticleClassify->getBreadCrumb($article['classify_id']);
        return $this->fetch();
    }

}
