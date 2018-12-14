<?php
// +---------------------------------------------------------------------+
// | 爬虫管理    | [ 昆明信息港 ]    [文章]                                |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+

namespace app\admin\controller\article;
use app\admin\controller\AdminBase;
use think\Collection;

class Article extends AdminBase
{
    protected $noNeedRight = [];
    protected $noNeedLogin = [];

    /**
     * 基础控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {
        $this->title = '文章管理';
        parent::initialize();

    }
    /**
     * 文章管理
     */
    public function index()
    {
        if ($this->request->isAjax() && !$this->request->isPjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->getTableSearchParam('a.title');

            $list      = $this->logicArticle->getListData($where, $sort, $order, $offset, $limit);
            $countList = $this->logicArticle->countListData($where);

            return json(['rows' => $list, 'total' => $countList]);
        }
        return $this->fetch();
    }

    /**
     * 发布文章
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $this->jump($this->logicArticle->addArticle($row));
        }
        return $this->fetch();
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $id = $this->request->param('ids', 0, 'intval');
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $this->jump($this->logicArticle->updateRow($row));
        }
        $info = $this->logicArticle->getRow(['id' => $id]);
        if (!$info) {
            $this->jump([11, '没有找到结果']);
        }
        $this->view->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 删除
     */
    public function del()
    {
        $id = $this->request->param('ids', 0, 'intval');
        if (!$id || !$this->request->isPost()) {
            $this->jump([1, '非法请求']);
        }
        $this->jump($this->logicArticle->delRow(['id' => $id]));
    }

    /**
     * 设置应用状态
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @return   [type]       [description]
     */
    public function setstatus()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $status    = $this->request->param('status', 0, 'intval');
        $setStatus = $this->logicArticle->setStatus(['id' => $id, 'status' => $status]);

        $this->jump($setStatus);
    }

}
