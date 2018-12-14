<?php
// +---------------------------------------------------------------------
// | 敏感词管理
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller\sensitive;

use app\admin\controller\AdminBase;
use think\Db;
use think\Exception;
use util\Excel;

class Word extends AdminBase
{
    protected $noNeedRight       = [''];
    protected $noNeedLogin       = [''];
    protected $showContentHeader = false;
    //导入数据的表头
    protected $importColumn = [
        'keywords'    => '关键词',
        'event_class' => '词语类别',
        'level'       => '级别',
        'classify'    => '敏感类型',
        'incident'    => '说明',
    ];

    /**
     * 基础控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-12
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();
        $this->title = '敏感词管理';

        //获取节点树
        if (ACTION_NAME == 'index') {
            $list = $this->logicSensitiveClassify->getListData(['sc.status' => 1]);
        } else {
            $list = $this->logicSensitiveClassify->getListDataTree(['sc.status' => 1]);
        }
        $this->view->assign('classifylist', $list);
    }

    public function index()
    {
        if ($this->request->isAjax() && !$this->request->isPjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->getTableSearchParam('sc.name');

            $list      = $this->logicSensitiveWord->getListData($where, $sort, $order, $offset, $limit);
            $countList = $this->logicSensitiveWord->countListData($where);

            return json(['rows' => $list, 'total' => $countList]);
        }
        return $this->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $this->jump($this->logicSensitiveWord->addData($row));
        }

        return $this->fetch();
    }

    public function edit()
    {
        $id = $this->request->param('ids', 0, 'intval');
        if ($this->request->isPost()) {
            $row = $this->request->param('row/a');
            $this->jump($this->logicSensitiveWord->updateRow($row));
        }
        $info = $this->logicSensitiveWord->getRow(['id' => $id]);
        $this->view->assign('info', $info);
        return $this->fetch();
    }

    public function del()
    {
        $id = $this->request->param('ids', 0, 'intval');
        if (!$id || !$this->request->isPost()) {
            $this->jump([1, '非法请求']);
        }
        $this->jump($this->logicSensitiveWord->delRow(['id' => $id]));
    }

    public function upload()
    {
        try {
            //只上传excle 文件
            $uploadInfo = Excel::init(['mimetype' => 'xls,xlsx,csv'])->uploadFile();
            //excel 内容数组
            $arrExcel = Excel::init()->getExcelToArrayByFilePath($uploadInfo['url']);

            $retult = Excel::init()->arrToStandardArr($arrExcel, $this->importColumn);

            // 启动事务
            Db::startTrans();
            try {
                $datalist  = $retult['data'];
                $classify  = $this->logicSensitiveClassify;
                $word      = $this->logicSensitiveWord;
                $nowTime   = time();
                $userId    = session('admin.id');
                $levelList = $classify->getLevelList();
                foreach ($datalist as $key => $row) {
                    $classifyRowInfo = $classify->where(['name' => $row['classify']])->find();
                    if (!$classifyRowInfo) {
                        $insertId = $classify->addRow([
                            'pid'         => 0,
                            'create_time' => $nowTime,
                            'uid'         => $userId,
                            'name'        => $row['classify'],
                            'level'       => $row['level'],
                            // 'level'       => $levelList[$row['level']],
                        ]);
                        $classifyRowInfo = $classify->where(['id' => $insertId])->find();
                    }
                    $word->addRow([
                        'create_time' => $nowTime,
                        'uid'         => $userId,
                        'keywords'    => $row['keywords'],
                        'incident'    => $row['incident'],
                        'event_class' => $row['event_class'],
                        'classify_id' => $classifyRowInfo['id'],
                    ]);
                }
                // 提交事务
                Db::commit();
                $this->jump(0, '导入成功');
            } catch (Exception $e) {
                // 回滚事务
                Db::rollback();
                $this->jump(1, '导入失败' . $e->getMessage());
            }

        } catch (Exception $e) {
            // 上传失败获取错误信息
            $this->jump(1, '文件上传失败' . $e->getMessage());
        }
    }

}
