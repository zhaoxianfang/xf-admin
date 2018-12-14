<?php
// +---------------------------------------------------------------------+
// | 爬虫管理    | [ 昆明信息港 ] [文章分类]                                   |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+

namespace app\index\logic;
use util\Menu;
use think\Collection;

/**
 * 文章 基础逻辑
 */
class ArticleClassify extends IndexLogic
{

    public function getBreadCrumb($classify_id='')
    {
    	if(!$classify_id){
    		return '';
    	}
    	// dump($this->modelArticleClassify->all());die;
    	// $list = $this->modelArticleClassify->all();
    	$list = auto_cache('article_classify_list', create_closure($this->modelArticleClassify, 'getList',['status'=>1]),6);
    	
        // 面包屑导航
        return Menu::instance()->init($list, '', '', false)->getParentNode($classify_id);
        
    	
    }
    
}
