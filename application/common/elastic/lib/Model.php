<?php
// +---------------------------------------------------------------------
// | ES基础模型类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\elastic\lib;

use app\common\elastic\lib\ElasticsearchFactory;
use app\common\elastic\exception\ModelException;
use Elasticsearch\Common\Exceptions\ElasticsearchException;

/**
 * es模型基础类
 */
abstract class Model
{
    /**
     * es连接客户端
     * 
     * @var object 
     */
    private $client;

    /**
     * es索引名称
     *
     * @var string
     */
    protected $index;

    /**
     * es 类型名称
     *
     * @var string
     */
    protected $type;

    /**
     * 数据主键ID
     *
     * @var string
     */
    protected $pk = 'id';


    /**
     * 初始化
     */
    public function __construct()
    {       
        // 实例化es客户端
        $this->client = ElasticsearchFactory::getInstance();
        // 设置索引
        $this->setIndex();
        // 设置类型
        $this->setType();
        // 模型初始化
        $this->init();
    }

    /**
     * 设置es索引
     */
    abstract protected function setIndex();

    /**
     * 设置ES类型值
     */
    abstract protected function setType();

    /**
     * 设置ES文档字段
     */
    abstract protected function getField();

    /**
     * 模型初始化
     */
    protected function init()
    {

    }

    /**
     * 获取索引
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * 获取type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 创建索引
     *
     * @throws object 错误信息
     * @return object 创建索引成功信息
     */
    public function createIndex()
    {
        $param['index'] = $this->index;
        try {
            $response = $this->client->indices()->create($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());          
        }
    }

    /**
     * 删除索引
     *
     * @throws object 错误信息
     * @return object 删除索引成功信息
     */
    public function deleteIndex()
    {
        $param = ['index' => $this->index];
        try {
            $response = $this->client->indices()->delete($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 设置setting
     */
    public function putSettings($body = [])
    {
        $param['index'] = $this->index;
        $param['body'] = $body;

        try {
            $response = $this->client->indices()->putSettings($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 设置mapping
     */
    public function putMapping($body = [])
    {
        $param['index'] = $this->index;
        $param['type']  = $this->type;
        $param['body']  = $body;

        try {
            $response = $this->client->indices()->putMapping($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 获取索引配置信息
     *
     * @param  string $index 索引名称
     * @return array        索引配置信息
     */
    public function getIndexSettings()
    {
        $param = ['index' => $this->index];

        try {
            $response = $this->client->indices()->getSettings($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 获取mapping配置信息
     */
    public function getIndexMappting()
    {
        $param = ['index' => $this->index, 'type' => $this->type];

         try {
            $response = $this->client->indices()->getMapping($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 创建文档信息   
     *
     * @param array $data 创建的文档数据
     * @return array  创建后的文档信息
     */
    public function createDoc($data = [])
    {
        $param['index'] = $this->index;
        $param['type']  = $this->type;
        if (!empty($data[$this->pk])) {
            $param['id'] = $data[$this->pk];
        }
        $param['body']  = $data;

        try {
            $response = $this->client->index($param);
             return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 批量创建文档
     *
     * @param  array  $data 数据集
     * @return array  创建后的文档信息
     */
    public function createDocAll($data = [])
    {
        $param = [];
        $index_conf = [
            '_index'    => $this->index,
            '_type'     => $this->type,
        ];
        foreach ($data as $key => $item) {
            unset($index_conf['_id']);
            if (!empty($item[$this->pk])) {
                $index_conf['_id'] = $item[$this->pk];
            }
            $param['body'][] = [
                'index' => $index_conf
            ];
            $param['body'][] = $item;
        }

        try {
            $response = $this->client->bulk($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 根据ID获取文档信息
     *
     * @param  string $id id值
     *
     * @return 返回结果信息
     */
    public function getDocById($id = '')
    {
        $param['index'] = $this->index;
        $param['type']  = $this->type;
        $param['id']    = $id;

        try {
            $response = $this->client->get($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 更新文档信息
     *
     * @param  string $id id值
     * @param  array $data 更新的数据信息
     *
     * @return 返回结果信息
     */
    public function updateDoc($id, $data)
    {
        $param['index'] = $this->index;
        $param['type']  = $this->type;
        $param['id']    = $id;
        $param['body']  = ['doc'=>$data];

        try {
            $response = $this->client->update($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 根据ID删除文档信息
     *
     * @param  mixed $id 文档ID信息
     *
     * @return 返回结果集信息
     */
    public function deleteDoc($id)
    {
        $param['index'] = $this->index;
        $param['type']  = $this->type;
        $param['id']    = $id;

        try {
            $response = $this->client->delete($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 搜索文档信息
     *
     * @param  array $match 搜索条件
     *
     * @return array        结果集信息
     */
    public function searchDoc($match = [])
    {
        $param['index'] = $this->index;
        $param['type']  = $this->type;
        $param['body']  = $match;

        try {
            $response = $this->client->search($param);
            return $response;
        } catch (ElasticsearchException $e) {
            throw new ModelException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 新增批量数据
     */
    public function addAll($data)
    {
        $list = [];
        $fields = $this->getField();
        foreach ($data as $key => $item) {
            foreach ($fields as $field) {
                $list[$key][$field] = isset($item[$field]) ? $item[$field] : '';
            }
        }
        $res = $this->createDocAll($list);
        return $res;
    }
} 