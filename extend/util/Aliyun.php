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

use OSS\OssClient;
use think\Config;
use \aliyun\sdk\core;
use \aliyun\sdk\vod\Request\V20170321 as vod;

/**
 * 阿里云
 */
class Aliyun
{
    /**
     * @var object 对象实例
     */
    protected static $instance;

    protected $client; //阿里sdk 连接信息

    protected $useSSL = true;

    public function __construct()
    {
        // 捕获异常
        try {
            $aliyunBasePath = ROOT_PATH . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'aliyun' . DIRECTORY_SEPARATOR . 'sdk' . DIRECTORY_SEPARATOR;

            $aliyunConfigPath = $aliyunBasePath . 'core' . DIRECTORY_SEPARATOR . 'Config.php';
            $aliyunOssPath    = $aliyunBasePath . 'oss-2.2.4' . DIRECTORY_SEPARATOR . 'autoload.php';

            include_once realpath($aliyunConfigPath);
            include_once realpath($aliyunOssPath);

            // require_once './aliyun-php-sdk/aliyun-oss-php-sdk-2.2.4/autoload.php';
            //配置参数
            // $accessKeyId     = config('aliyun.dianbo.accessKeyId');
            // $accessKeySecret = config('aliyun.dianbo.accessKeySecret');
            $accessKeyId     = config('aliyun.dianbo')['accessKeyId'];
            $accessKeySecret = config('aliyun.dianbo')['accessKeySecret'];

            $this->init_vod_client($accessKeyId, $accessKeySecret);

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
        return $this->client;
    }

    /**
     * [初始化客户端]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $accessKeyId     [description]
     * @param    [type]       $accessKeySecret [description]
     * @return   [type]                        [description]
     */
    public function init_vod_client($accessKeyId, $accessKeySecret)
    {
        $regionId     = 'cn-shanghai'; // 点播服务所在的Region，国内请填cn-shanghai，不要填写别的区域
        $profile      = core\Profile\DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret);
        $this->client = new core\DefaultAcsClient($profile);
    }

    /**
     * 获取播放地址
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client  [description]
     * @param    [type]       $videoId [description]
     * @return   [type]                [description]
     */
    public function get_play_info($client, $videoId)
    {
        $request = new vod\GetPlayInfoRequest();
        $request->setVideoId($videoId);
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * [获取播放凭证]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client  [description]
     * @param    [type]       $videoId [description]
     * @return   [type]                [description]
     */
    public function get_play_auth($client, $videoId)
    {
        $request = new vod\GetVideoPlayAuthRequest();
        $request->setVideoId($videoId);
        $request->setAuthInfoTimeout(3600); // 播放凭证过期时间，默认为100秒，取值范围100~3600；注意：播放凭证用来传给播放器自动换取播放地址，凭证过期时间不是播放地址的过期时间
        $request->setAcceptFormat('JSON');
        $response = $client->getAcsResponse($request);
        return $response;
    }

    /**
     * [create_upload_video 获取视频上传地址和凭证]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client [description]
     * @return   [type]               [description]
     */
    /**
     * [获取视频上传地址和凭证]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-20
     * @param    [type]       $client      [description]
     * @param    string       $title       [视频标题(必填参数)]
     * @param    string       $fileName    [视频源文件名称，必须包含扩展名(必填参数)]
     * @param    string       $description [视频源文件描述(可选)]
     * @param    string       $coverURL    [自定义视频封面(可选)]
     * @param    string       $tags        [视频标签，多个用逗号分隔(可选)]
     * @return   [type]                    [description]
     */
    public function create_upload_video($client, $title = '', $fileName = '', $description = '', $coverURL = "", $tags = '')
    {
        if (!$client || !$title || !$fileName) {
            throw new \Exception("请检查参数是否为空");
        }
        $request = new vod\CreateUploadVideoRequest();
        $request->setTitle($title); // 视频标题(必填参数)
        $request->setFileName($fileName); // 视频源文件名称，必须包含扩展名(必填参数)
        $request->setDescription($description); // 视频源文件描述(可选)
        $request->setCoverURL($coverURL); // 自定义视频封面(可选)
        $request->setTags($tags); // 视频标签，多个用逗号分隔(可选)
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * 刷新视频上传凭证
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client  [description]
     * @param    [type]       $videoId [description]
     * @return   [type]                [description]
     */
    public function refresh_upload_video($client, $videoId)
    {
        $request = new vod\RefreshUploadVideoRequest();
        $request->setVideoId($videoId);
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * [create_upload_image 获取图片上传地址和凭证]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client    [description]
     * @param    [type]       $imageType [description]
     * @param    [type]       $imageExt  [description]
     * @return   [type]                  [description]
     */
    public function create_upload_image($client, $imageType, $imageExt)
    {
        $request = new vod\CreateUploadImageRequest();
        $request->setImageType($imageType);
        $request->setImageExt($imageExt);
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * [u修改视频信息]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client  [description]
     * @param    [type]       $videoId [description]
     * @return   [type]                [description]
     */
    public function update_video_info($client, $videoId)
    {
        $request = new vod\UpdateVideoInfoRequest();
        $request->setVideoId($videoId);
        $request->setTitle('New Title'); // 更改视频标题
        $request->setDescription('New Description'); // 更改视频描述
        $request->setCoverURL('http://img.alicdn.com/tps/TB1qnJ1PVXXXXXCXXXXXXXXXXXX-700-700.png'); // 更改视频封面
        $request->setTags('tag1,tag2'); // 更改视频标签，多个用逗号分隔
        $request->setCateId(0); // 更改视频分类(可在点播控制台·全局设置·分类管理里查看分类ID：https://vod.console.aliyun.com/#/vod/settings/category)
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);

    }

    /**
     * 删除视频
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client   [description]
     * @param    [type]       $videoIds [description]
     * @return   [type]                 [description]
     */
    public function delete_videos($client, $videoIds)
    {
        $request = new vod\DeleteVideoRequest();
        $request->setVideoIds($videoIds); // 支持批量删除视频；videoIds为传入的视频ID列表，多个用逗号分隔
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * 获取源文件信息（含原片下载地址）
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client  [description]
     * @param    [type]       $videoId [description]
     * @return   [type]                [description]
     */
    public function get_mezzanine_info($client, $videoId)
    {
        $request = new vod\GetMezzanineInfoRequest();
        $request->setVideoId($videoId);
        $request->setAuthTimeout(3600 * 5); // 原片下载地址过期时间，单位：秒，默认为3600秒
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * 获取视频列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client [description]
     * @return   [type]               [description]
     */
    public function get_video_list($client)
    {
        $request = new vod\GetVideoListRequest();
        // 示例：分别取一个月前、当前时间的UTC时间作为筛选视频列表的起止时间
        $localTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $utcNow      = gmdate('Y-m-d\TH:i:s\Z');
        $utcMonthAgo = gmdate('Y-m-d\TH:i:s\Z', time() - 30 * 86400);
        date_default_timezone_set($localTimeZone);
        $request->setStartTime($utcMonthAgo); // 视频创建的起始时间，为UTC格式
        $request->setEndTime($utcNow); // 视频创建的结束时间，为UTC格式
        #$request->setStatus('Uploading,Normal,Transcoding');  // 视频状态，默认获取所有状态的视频，多个用逗号分隔
        #$request->setCateId(0);               // 按分类进行筛选
        $request->setPageNo(1);
        $request->setPageSize(20);
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * 删除媒体流
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client  [description]
     * @param    [type]       $videoId [description]
     * @param    [type]       $jobIds  [description]
     * @return   [type]                [description]
     *
     * 可删除视频流或音频流信息及存储文件，并支持批量删除；删除后当CDN缓存过期，该路流会无法播放，请谨慎操作
     */
    public function delete_stream($client, $videoId, $jobIds)
    {
        $request = new vod\DeleteStreamRequest();
        $request->setVideoId($videoId);
        $request->setJobIds($jobIds); // 媒体流转码的作业ID列表，多个用逗号分隔；JobId可通过获取播放地址接口(GetPlayInfo)获取到。
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * [创建视频分类]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-19
     * @param    [type]       $client   [description]
     * @param    [type]       $cateName [description]
     * @param    integer      $parentId [description]
     * 创建视频分类，最大支持三级分类，每个分类最多支持创建100个子分类
     * 一级分类最大也是支持100个，若有更大需求请提工单联系我们
     */
    public function add_category($client, $cateName, $parentId = -1)
    {
        $request = new vod\AddCategoryRequest();
        $request->setCateName($cateName); // 分类名称，不能超过64个字节，UTF8编码
        $request->setParentId($parentId); // 父分类ID，若不填，则默认生成一级分类，根节点分类ID为-1
        $request->setAcceptFormat('JSON');
        return $client->getAcsResponse($request);
    }

    /**
     * 使用上传凭证和地址信息初始化OSS客户端（注意需要先Base64解码并Json Decode再传入）
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-20
     * @param    [type]       $uploadAuth    [description]
     * @param    [type]       $uploadAddress [description]
     * @return   [type]                      [description]
     */
    public function init_oss_client($uploadAuth, $uploadAddress)
    {
        $ossClient = new OssClient($uploadAuth['AccessKeyId'], $uploadAuth['AccessKeySecret'], $uploadAddress['Endpoint'],
            false, $uploadAuth['SecurityToken']);
        $ossClient->setUseSSL($this->useSSL);
        $ossClient->setTimeout(86400 * 7); // 设置请求超时时间，单位秒，默认是5184000秒, 建议不要设置太小，如果上传文件很大，消耗的时间会比较长
        $ossClient->setConnectTimeout(10); // 设置连接超时时间，单位秒，默认是10秒
        return $ossClient;
    }

    /**
     * [使用简单方式上传本地文件：适用于小文件上传；最大支持5GB的单个文件]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-20
     * @param    [type]       $ossClient     [description]
     * @param    [type]       $uploadAddress [description]
     * @param    [type]       $localFile     [description]
     * @return   [type]                      [description]
     */
    public function upload_local_file($ossClient, $uploadAddress, $localFile)
    {
        return $ossClient->uploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
    }

    /**
     * [大文件分片上传，支持断点续传；最大支持48.8TB]
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-20
     * @param    [type]       $ossClient     [description]
     * @param    [type]       $uploadAddress [description]
     * @param    [type]       $localFile     [description]
     * @return   [type]                      [description]
     */
    public function multipart_upload_file($ossClient, $uploadAddress, $localFile)
    {
        return $ossClient->multiuploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
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

}
