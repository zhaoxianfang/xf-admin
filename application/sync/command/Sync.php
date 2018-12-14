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

namespace app\sync\command;

use app\common\elastic\service\SyncDoc;
use app\common\queue\Redis;
use app\facade\Doc;
use app\facade\DocDetails;
use app\facade\DocErr;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\facade\Log;

/**
 * 同步mysql数据到es服务器中
 */
class Sync extends Command
{
    /**
     * 服务对象
     *
     * @var object
     */
    protected $objs;

    protected $writeLen = 1000;

    /**
     * 配置
     */
    protected function configure()
    {
        $this->setName('sync')->setDescription('Here is the synchronization data for mysql to elasticsearch! ');
    }

    /**
     * 执行命令
     */
    protected function execute(Input $input, Output $output)
    {
        $this->getObj();
        $this->loopSync();
        $this->redisToDb();
    }

    /**
     * 实例化同步数据对象
     */
    protected function getObj()
    {
        $this->objs['doc'] = new SyncDoc;
    }

    /**
     * 循环同步数据
     */
    protected function loopSync()
    {
        foreach ($this->objs as $k => $obj) {
            $obj->sync();
        }
    }

    /**
     * redisToDb redis数据转移到DB
     * @Author   ZhaoXianFang
     * @DateTime 2018-11-21
     * @return   [type]       [description]
     */
    public function redisToDb()
    {
        try {
            $redis = Redis::init();
        } catch (\Exception $e) {
            Log::record("redis 连接失败!" . $e->getMessage(), 'error');
            return true;
        }

        //队列长度
        $listLen = $redis->lLen($redis->getKey());
        if ($listLen > $this->writeLen) {
            $listLen = $this->writeLen;
        }
        // 启动事务
        Db::startTrans();

        for ($i = 0; $i < $listLen; $i++) {
            $redisRow = $redis->lPop($redis->getKey());
            if (empty($redisRow)) {
                continue;
            }
            $data = json_decode($redisRow, true);

            $arr = [
                "title"        => isset($data['title']) ? $data['title'] : '',
                "column"       => isset($data['column']) ? $data['column'] : '',
                "source"       => isset($data['source']) ? $data['source'] : '',
                "url"          => isset($data['url']) ? $data['url'] : '',
                "source_id"    => isset($data['source_id']) ? $data['source_id'] : '',
                "publish_time" => isset($data['publish_time']) ? strtotime($data['publish_time']) : '',
                "author"       => isset($data['author']) ? $data['author'] : '',
                "creare_time"  => time(),
                "status"       => 1,
                "sync_status"  => 0,
            ];
            $content = $data['content'];

            try {
                $add = Doc::allowField(true)->insertGetId($arr);
                DocDetails::allowField(true)->insert(['doc_id' => $add, 'content' => $content]);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();

                // $resInsert = $redis->push($data);
                // $type = $resInsert ? 12 : 11;
                Log::record('写入失败[' . $arr['url'] . ']=>' . json_encode($e->getMessage()), 'error');
                DocErr::insert([
                    'create_time' => time(),
                    'type'        => 21,
                    'err_info'    => json_encode($e->getMessage()),
                    'err_url'     => $arr['url'],
                    'content'     => json_encode($arr),
                ]);
            }
        }
    }
}
