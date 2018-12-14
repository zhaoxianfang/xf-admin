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

namespace app\admin\logic;

/**
 * 文章 基础逻辑
 */
class Article extends AdminLogic
{

    /**
     * 获取列表
     */
    public function getListData($where = [], $sort = 'a.id', $order = 'desc', $offset = 0, $limit = 15)
    {
        $join  = [['user u', 'a.user_id = u.id'], ['article_classify ac', 'ac.id = a.classify_id']];
        $order = [$sort => $order];
        $limit = $offset . ',' . $limit;
        $alias = 'a';

        return $this->modelArticle->getList($where, $order, 'a.*,u.name as user_name,u.id as user_id,ac.name as classify_name', $limit, $alias, $join);
    }

    public function countListData($where = [])
    {
        $join  = [['user u', 'a.user_id = u.id'], ['article_classify ac', 'ac.id = a.classify_id']];
        $alias = 'a';

        return $this->modelArticle->getCount($where, $alias, $join);
    }

    /**
     * 添加应用
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $data [description]
     */
    public function addArticle($data = [])
    {
        if ($data) {
            $data['user_id']     = session('admin.id');
            $data['create_time'] = time(); //创建时间
            $data['status']      = 0; //默认需要审核
        }
        //获取摘要
        $summary         = !empty($data['summary']) ? $data['summary'] : $data['content'];
        $data['summary'] = truncate($summary);

        unset($data['push']);
        $insertId = $this->modelArticle->addRow($data);
        if ($insertId === false) {
            return [1, '添加失败'];
        }
        return [10, '添加成功'];
    }

    /**
     * 设置app 状态
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $data [description]
     */
    public function setStatus($data = [])
    {
        $setInfo = $this->modelArticle->updateRow($data);
        $code    = $setInfo === false ? 1 : 0;
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
    public function updateArticle($rowData = [])
    {
        //获取摘要
        $summary            = !empty($rowData['summary']) ? $rowData['summary'] : $rowData['content'];
        $rowData['summary'] = truncate($summary);

        $info = $this->modelArticle->updateRow($rowData);
        return ($info === false) ? [1, '更新失败'] : [10, '更新成功'];
    }
}
