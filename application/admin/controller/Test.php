<?php
// +---------------------------------------------------------------------
// | 测试控制器
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller;

use app\common\elastic\model\Doc as MDoc;
use app\common\elastic\service\Doc;
use app\common\queue\Redis;
use think\Db;

class Test extends AdminBase
{
    protected $noNeedRight       = ['*'];
    protected $noNeedLogin       = [''];
    protected $showContentHeader = false;

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

    public function index()
    {
        return '测试控制器';
    }

    public function link()
    {
        $doc = new Doc;
        dump($doc);
    }

    public function table()
    {
        if ($this->request->isAjax()) {
            $list = [
                'id'        => 1,
                'name'      => 'name',
                'user_name' => 'user_name',
            ];

            // $result = array("total" => count($list), "rows" => $list);
            $result = array("total" => 1, "rows" => [$list]);
            return json($result);
        }
        $this->title = '关键字管理';
        return $this->fetch();
    }

    public function createIndex()
    {
        $doc = new Doc;
        echo "<pre>";
        print_r($doc->createIndex());
    }

    public function delIndex()
    {
        $doc = new MDoc;
        dump($doc->deleteIndex());

    }

    public function addEsTestData()
    {
        $doc = new MDoc;

        // $data = Db::name('doc')->where('status',1)->find();
        // $data['source_id'] = [1,2,3,4,5,6];
        // $data['source'] = ['name'=>'测试来源'];
        // $data['content'] = '测试正文';

        // dump($doc->createDoc($data));

        $data = Db::name('doc')->where('status', 1)->select();

        foreach ($data as $key => &$value) {
            $value['source_id'] = [1, 2, 3, 4, 5, 6];
            $value['source']    = ['name' => '测试来源'];
            $value['content']   = '测试正文';
        }

        // dump($doc->createDoc($data));
        dump($doc->createDocAll($data));

    }

    public function searchEsTest()
    {
        // 按发布时间搜索(必须)
        $map['start_publish_time'] = strtotime(date("Y-m-d H:i:s", strtotime("-300 day"))); //开始时间 默认前三天
        $map['end_publish_time']   = 1689652965; //time(); //结束时间 默认现在
        $map['keyword']            = '测试';
        // $map['source_id'] = explode(',', $source_id);
        $doc = new Doc;
        dump($doc->getList($map, $sort = [], $offset = 0, $length = 15, $fields = ''));
    }

    /**
     * 获取Mapping信息
     * 接口:$client->indices()->getMapping
     * 这里获取的相关信息就是我们刚刚创建的索引es可以动态的修改以及添加相关的信息.
     */
    public function getMapping()
    {

        $doc = new MDoc;
        echo "<pre>";
        // print_r($doc->getIndexSettings());
        print_r($doc->getIndexMappting());
    }

    public function redis()
    {
        echo "<pre>";

        $redis = Redis::init();

        $testData = [
            'sdf'    => 'sfsfcongegs',
            'sfadsf' => 'sfsfcongegs',
            'hs1651' => 'sfsfcongegs',
        ];
        print_r($redis->push($testData));
        print_r($redis->info());
        print_r($redis->lLen($redis->getKey()));
        // print_r($redis->keys('*'));
        // print_r($redis->lPop($redis->getKey()));
        print_r($redis->lRange($redis->getKey(), 0, 100));
        // print_r($redis->hGetAll());
        // print_r($redis->ping());

        // $redis = new Redis();
        // $redis = RedisClass::getInstance(config('redis.'),config('redis.'));
        // echo "<pre>";
        // // print_r($doc->getIndexSettings());
        // print_r($redis->ping());

        // $redis = new \Redis();
        // $redis->connect(config('redis.host'), config('redis.port'));
        // $redis->auth(config('redis.auth'));
        // print_r($redis->ping());

    }

}
