<?php
// +----------------------------------------------------------------------
// | 阿里云视频调拨 公共类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace util;

use OSS\Core\OssException;
use OSS\OssClient;
use think\Config;
use think\Exception;

/**
 * 阿里云
 */
class AliyunOss
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    protected $ossClient; //阿里oss sdk 连接信息

    protected $useSSL = true;
    protected $config; //配置

    public function __construct()
    {
        // 捕获异常
        try {
            //配置参数
            $this->config = config('aliyun.oss');

            $this->ossClient = new OssClient($this->config['accessKeyId'], $this->config['accessKeySecret'], $this->config['endpoint']);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 获取连接信息
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @return   [type]       [description]
     */
    public function getClient()
    {
        return $this->ossClient;
    }

    /**
     * 是否启用ssl
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-20
     * @param    [type]       $useSSL  boolean
     */
    public function setUseSSL($useSSL)
    {
        $this->useSSL = $useSSL;
    }

    /**
     * [列出用户所有的Bucket]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-27
     * @param    [type]       $ossClient [$ossClient OssClient实例]
     * @return   [type]                  [description]
     */
    public function listBuckets($ossClient)
    {
        $bucketList = null;
        try {
            $bucketListInfo = $ossClient->listBuckets();
        } catch (OssException $e) {
            $this->error($e->getMessage());
            return;
        }
        return $bucketListInfo->getBucketList();
        // $bucketList = $bucketListInfo->getBucketList();
        // foreach ($bucketList as $bucket) {
        //     print($bucket->getLocation() . "\t" . $bucket->getName() . "\t" . $bucket->getCreatedate() . "\n");
        // }
    }

    /**
     * [上传指定的本地文件内容]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-27
     * @param    [type]       $object   [键值 可以理解为路径]
     * @param    [type]       $filePath [文件]
     * @return   [type]                 [description]
     */
    public function uploadMyFile($object, $filePath, $aclType)
    {
        try {
            $info = $this->ossClient->uploadFile($this->config['bucket'], $object, $filePath);
            $this->putMyObjectAcl($object, $aclType);
            return $info;
        } catch (OssException $e) {
            throw new Exception("上传文件发生错误：" . $e->getMessage());
            // printf(__FUNCTION__ . ": FAILED\n");
            // printf($e->getMessage() . "\n");
            return;
            // $this->error($e->getMessage());
            // return;
        }
        return;
    }

    /**
     * 获取文件范围权限
     *
     * @param OssClient $ossClient OssClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public function putMyObjectAcl($object, $aclType = '0')
    {
        $acls = [
            '0' => 'default', //默认
            '1' => 'private', //私有读写
            '2' => 'public-read', //公共读私有写
            '3' => 'public-read-write', //公共读写
        ];
        try {
            return $this->ossClient->putObjectAcl($this->config['bucket'], $object, $acls[$aclType]);
        } catch (OssException $e) {
            throw new Exception("获取文件范围权限发生错误：" . $e->getMessage());
            // printf(__FUNCTION__ . ": FAILED\n");
            // printf($e->getMessage() . "\n");
            return;
        }
        // return true;
    }

    /**
     * 判断object是否存在
     *
     * @param OssClient $ossClient OSSClient实例
     * @param string $bucket bucket名字
     * @return null
     */
    public function doesMyObjectExist($object)
    {
        // $object = "oss-php-sdk-test/upload-test-object-name.txt";
        try {
            return $this->ossClient->doesObjectExist($this->config['bucket'], $object);
        } catch (OssException $e) {
            throw new Exception("判断文件是否存在时发生错误：" . $e->getMessage());
            // printf(__FUNCTION__ . ": FAILED\n");
            // printf($e->getMessage() . "\n");
            return;
        }
        return;
        // print(__FUNCTION__ . ": OK" . "\n");
        // var_dump($exist);
    }

    /**
     * 获取bucket的acl配置
     *
     * @param OssClient $ossClient OssClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public function getMyBucketAcl($bucket)
    {
        try {
            return $this->ossClient->getBucketAcl($bucket);
        } catch (OssException $e) {
            throw new Exception("获取acl配置发生错误：" . $e->getMessage());
            // printf(__FUNCTION__ . ": FAILED\n");
            // printf($e->getMessage() . "\n");
            return;
        }
        return;
        // print(__FUNCTION__ . ": OK" . "\n");
        // print('acl: ' . $res);
    }

    /**
     * 列出Bucket内所有目录和文件， 根据返回的nextMarker循环调用listObjects接口得到所有文件和目录
     *
     * @param dirname $dirname 需要查找的虚拟目录
     * @return null
     */
    public function listAllObjects($dirname = '')
    {
        // $prefix     = 'dir/';//前缀（虚拟目录）
        $prefix     = $dirname; //前缀（虚拟目录）
        $delimiter  = '/'; //定界符
        $nextMarker = ''; //标记
        $maxkeys    = 30;
        while (true) {
            $options = array(
                'delimiter' => $delimiter,
                'prefix'    => $prefix,
                'max-keys'  => $maxkeys,
                'marker'    => $nextMarker,
            );
            // var_dump($options);
            try {
                $listObjectInfo = $this->ossClient->listObjects($this->config['bucket'], $options);
            } catch (OssException $e) {
                throw new Exception("列出Bucker目录和文件发生错误：" . $e->getMessage());
                // printf(__FUNCTION__ . ": FAILED\n");
                // printf($e->getMessage() . "\n");
                return;
            }
            dump($listObjectInfo);
            // 得到nextMarker，从上一次listObjects读到的最后一个文件的下一个文件开始继续获取文件列表
            $nextMarker = $listObjectInfo->getNextMarker();
            $listObject = $listObjectInfo->getObjectList();
            $listPrefix = $listObjectInfo->getPrefixList();
            // $listPrefix = $listObjectInfo->PrefixList();
            var_dump(count($listObject));
            var_dump(count($listPrefix));
            if ($nextMarker === '') {
                break;
            }
        }
    }

    /**
     * 创建虚拟目录
     *
     * @param string $dirname 虚拟目录名称
     *
     * @param OssClient $ossClient OSSClient实例
     * @param string $bucket 存储空间名称
     * @return null
     */
    public function createMyObjectDir($dirname = '')
    {
        try {
            $this->ossClient->createObjectDir($this->config['bucket'], $dirname);
        } catch (OssException $e) {
            throw new Exception("创建虚拟目录错误：" . $e->getMessage());
            // $this->error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 下载文件 到本地
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-30
     * @param    [type]       $object    [可以理解为OSS上的文件路径]
     * @param    [type]       $localfile [需要保存到的本地文件路径]
     * @return   [type]                  [description]
     */
    public function getObjectToLocalFile($object, $localfile)
    {
        // $object    = "oss-php-sdk-test/upload-test-object-name.txt";
        // $localfile = "upload-test-object-name.txt";
        $options   = array(
            OssClient::OSS_FILE_DOWNLOAD => $localfile,
        );
        try {
            $this->ossClient->getObject($this->config['bucket'], $object, $options);
        } catch (OssException $e) {
            throw new Exception("下载文件出错：" . $e->getMessage());
            return;
        }
        if (file_exists($localfile)) {
            // unlink($localfile);
            return $localfile;
        }
        return false;
    }

    /**
     * 调用时候不需要填写第一个参数 bucket 参数
     * 对象的不存在的实例方法进行“调用”时，调用ossClient sdk中的方法
     * @Author   ZhaoXianFang
     * @DateTime 2017-08-16
     * @param    [type]       $method 方法
     * @param    [type]       $args       参数 数组
     * @return   [type]                   [description]
     */
    public function __call($method, $args)
    {
        // 捕获异常
        try {
            array_unshift($args, $this->config['bucket']);
            return call_user_func_array(array($this->ossClient, $method), $args);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * 调用时候不需要填写第一个参数 bucket 参数
     * 当对这个类的不存在的静态方法进行“调用”
     * @Author   ZhaoXianFang
     * @DateTime 2017-08-16
     * @param    [type]       $method 方法
     * @param    [type]       $args       [description]
     * @return   [type]                   [description]
     */
    public static function __callstatic($method, $args)
    {
        // 捕获异常
        try {
            array_unshift($args, $this->config['bucket']);
            return call_user_func_array(array($this->ossClient, $method), $args);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
