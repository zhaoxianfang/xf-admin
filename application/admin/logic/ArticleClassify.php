<?php
// +---------------------------------------------------------------------+
// | 爬虫管理    | [ 昆明信息港 ] [文章分类]                               |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+

namespace app\admin\logic;
use think\Collection;
use util\Menu;

/**
 * 应用 基础逻辑
 */
class ArticleClassify extends AdminLogic
{

    /**
     * 获取列表
     */
    public function getListData($where = [], $sort = 'ac.id', $order = 'desc', $offset = 0, $limit = 15)
    {
        $join = ['user u', 'ac.user_id = u.id'];
        $order = [$sort => $order];
        $limit = $offset . ',' . $limit;
        $alias = 'ac';

        return $this->modelArticleClassify->getList($where, $order, 'ac.*,u.nickname as user_name,u.id as user_id', $limit, $alias, $join);
    }

    public function countListData($where = [])
    {
        $join = ['user u', 'ac.user_id = u.id'];
        $alias = 'ac';
        return $this->modelArticleClassify->getCount($where, $alias, $join);
    }


    public function treeList($where = [])
    {
        $list = $this->modelArticleClassify->getList($where, 'ac.id DESC', 'ac.*,ac.name as title',50, 'ac',[]);
        $arrList = Collection::make($list)->toArray();
        // 菜单转换为视图
        $menulist = Menu::instance()->init($arrList)->setWeigh('')->getTree();
        
        return $menulist;
    }

    /**
     * 添加应用
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $data [description]
     */
    public function addClassify($data = [])
    {
        if ($data) {
            $data['user_id']     = session('admin.id');
            $data['create_time'] = time(); //创建时间
        }
        $insertId = $this->modelArticleClassify->addRow($data);
        if ($insertId === false) {
            return [1, '添加失败'];
        }
        return [10, '添加成功'];
    }

    /**
     * 设置 状态
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $data [description]
     */
    public function setStatus($data = [])
    {
        $setInfo = $this->modelArticleClassify->updateRow($data);
        $code    = $setInfo === false ? 0 : 1;
        $msg     = $setInfo === false ? '设置失败' : '设置成功';
        return [$code, $msg];
    }

    /**
     * 更新数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-15
     * @param    array        $rowData [description]
     * @return   [type]                [description]
     */
    public function updateData($rowData = [])
    {
        $info = $this->modelArticleClassify->updateRow($rowData);
        return ($info === false) ? [1, '更新失败'] : [10, '更新成功'];
    }

    public function getClassifyList()
    {
        $where = ['status'=>1];
        return $this->modelArticleClassify->getList($where, $order = '', 'id,name',false, []);
    }
}
