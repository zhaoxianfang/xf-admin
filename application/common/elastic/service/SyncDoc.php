<?php
// +----------------------------------------------------------------------
// | 网络舆情大数据平台系统
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: dorisnzy <dorisnzy@163.com>
// +----------------------------------------------------------------------
// | Date: 2018-03-05
// +----------------------------------------------------------------------

namespace app\common\elastic\service;

use app\common\elastic\exception\ModelException;
use app\common\elastic\model\Doc;
use app\facade\Doc as MDoc;
use app\facade\DocDetails;
use app\facade\SyncLog;
use think\Collection;

/**
 * 同步文档信息
 */
class SyncDoc
{
    /**
     * 获取数据长度
     *
     * @var integer
     */
    protected $limit = 500;

    /**
     * 文档ES模型
     *
     * @var object
     */
    protected $docES;

    /**
     * 日志模型类型
     *
     * @var integer
     */
    protected $logModelType = 1;

    /**
     * 日志对象
     *
     * @var object
     */
    protected $logModel;

    /**
     * 系统初始化
     */
    public function __construct()
    {
        $this->docModel        = MDoc::class;
        $this->docDetailsModel = DocDetails::class;

        $this->docES = new Doc;

    }

    /**
     * 同步文档信息
     */
    public function sync()
    {
        $doc_map['d.sync_status'] = 0;
        $list                     = MDoc::alias('d')
            ->field('d.*,dd.content')
            ->join('__DOC_DETAILS__ dd', 'dd.doc_id=d.id')
            ->where($doc_map)
            ->limit($this->limit)
            ->order('publish_time desc')
            ->select()
        ;

        if (!$list) {
            return;
        }

        $list = Collection::make($list)->toArray();

        // 组装数据
        foreach ($list as $k => $v) {

            $sourceArr = isset($v['source']) ? explode(',', $v['source']) : [];
            $sourceVal = [];
            if ($sourceArr) {
                foreach ($sourceArr as $key => $source) {
                    $sourceVal[] = ['name' => $source];
                }
            }
            $list[$k]['source'] = $sourceVal;

            //判断该内容是否已经存在
            try {
                try {
                    $hasDoc = $this->docES->getDocById($v['id']);
                } catch (ModelException $e) {
                    $hasDoc = json_decode($e->getMessage(), true);
                }

                if ($hasDoc['found'] === true) {
                    if ($v['status'] == 1) {
                        //更新
                        $upData = $this->docES->updateDoc($v['id'], $list[$k]);
                        if ($upData['result'] == 'updated' || $upData['result'] == 'noop') {
                            MDoc::update(['id' => $v['id'], 'sync_status' => 1]);

                            SyncLog::insert([
                                'title'       => '更新',
                                'content'     => json_encode($upData),
                                'create_time' => time(),
                                'type'        => 'success',
                            ]);
                        }
                    } else {
                        //删除
                        $delData = $this->docES->deleteDoc($v['id'], $list[$k]);
                        if ($delData['result'] == 'deleted') {
                            MDoc::update(['id' => $v['id'], 'sync_status' => 1]);

                            SyncLog::insert([
                                'title'       => '删除',
                                'content'     => json_encode($delData),
                                'create_time' => time(),
                                'type'        => 'success',
                            ]);
                        }
                    }
                } else {
                    if ($v['status'] == 1) {
                        //创建文章
                        $addData = $this->docES->createDoc($list[$k]);
                        if ($addData['result'] == 'created' || $addData['result'] == 'noop') {
                            MDoc::update(['id' => $v['id'], 'sync_status' => 1]);

                            SyncLog::insert([
                                'title'       => '创建',
                                'content'     => json_encode($addData),
                                'create_time' => time(),
                                'type'        => 'success',
                            ]);
                        }
                    } else {
                        MDoc::update(['id' => $v['id'], 'sync_status' => 1]);
                    }

                }
            } catch (ModelException $e) {
                $tryData = json_decode($e->getMessage(), true);
                //记录异常
                SyncLog::insert([
                    'title'       => '异常',
                    'content'     => json_encode($tryData),
                    'create_time' => time(),
                    'type'        => 'error',
                ]);
            }
        }
    }

}
