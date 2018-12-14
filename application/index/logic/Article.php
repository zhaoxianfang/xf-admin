<?php
// +---------------------------------------------------------------------+
// | 爬虫管理    | [ 昆明信息港 ] [文章]                                   |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+

namespace app\index\logic;

/**
 * 文章 基础逻辑
 */
class Article extends IndexLogic
{

    /**
     * 获取列表
     */
    public function getListData($where = [],$showList = false)
    {

        if($showList){
            $list = $this->modelArticle->getList($where, 'a.id DESC', 'a.*', 'a',[]);
        }else{
            $join = ['user u', 'a.user_id = u.id'];
            // 每页显示10条数据
            $list = $this->modelArticle->pageList(10, $where, 'a.id DESC', 'a.*,u.nickname as user_name,u.id as user_id,u.avatar,u.email,u.gender', 'a', $join);
        }
        

        return $list;
    }

    public function getpage($where = [])
    {
        $join = ['user u', 'a.user_id = u.id'];
        return $this->modelArticle->getRow($where, 'a.*,u.nickname as user_name,u.id as user_id,u.avatar,u.email,u.gender', 'a', $join);
    }
    
}
