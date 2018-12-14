<?php
// +----------------------------------------------------------------------
// | CurlMulti 多图片下载 处理类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

/**
$base_path = dirname(__FILE__).'/img';

$download_urls = array(
array('http://www.example.com/p1.jpg', $base_path.'/p1.jpg'),
array('http://www.example.com/p2.jpg', $base_path.'/p2.jpg'),
array('http://www.example.com/p3.jpg', $base_path.'/p3.jpg'),
array('http://www.example.com/p4.jpg', $base_path.'/p4.jpg'),
array('http://www.example.com/p5.jpg', $base_path.'/p5.jpg'),
);

$handle_num = (new CurlMulti($download_urls, 2, 10))->setUrl($download_urls_one)->download();

echo 'download num:'.$handle_num.PHP_EOL;
 */

namespace util;

class CurlMulti
{
    // 下载文件设置
    private $download_urls = array();

    // 最大开启进程数量
    private $max_process_num = 10;

    // 超时秒数
    private $timeout = 10;

    // 日志文件 记录日志文件
    private $logfile = null;

    /**
     * 初始化
     * @param  Array  $download_urls     下载的文件设置
     * @param  Int    $max_process_num   最大开启的进程数量
     * @param  Int    $timeout           超时秒数
     */
    public function __construct($download_urls = array(), $max_process_num = 10, $timeout = 10)
    {
        $this->download_urls   = $download_urls;
        $this->max_process_num = $max_process_num;
        $this->timeout         = $timeout;
    }

    /**
     * 设置下载的url地址
     * @Author   ZhaoXianFang
     * @DateTime 2018-04-26
     * @param    array        $urls [description]
     */
    public function setUrl($urls = array())
    {
        $this->download_urls = array_merge($this->download_urls, $urls);
        return $this;
    }

    /**
     * 设置日志文件记录路径
     * @Author   ZhaoXianFang
     * @DateTime 2018-04-26
     * @param    string       $filepath [description]
     */
    public function setLogFile($filepath = '')
    {
        // 日志文件
        if ($filepath) {
            $this->logfile = $filepath;
        } else {
            $this->logfile = dirname(__FILE__) . '/zxf_curl_multi_' . date('Ymd') . '.log';
        }
        return $this;
    }

    /**
     * 执行下载
     * @result Int
     */
    public function download()
    {

        // 已处理的数量
        $handle_num = 0;

        // 未处理完成
        while (count($this->download_urls) > 0) {

            // 需要处理的大于最大进程数
            if (count($this->download_urls) > $this->max_process_num) {
                $process_num = $this->max_process_num;
                // 需要处理的小于最大进程数
            } else {
                $process_num = count($this->download_urls);
            }

            // 抽取指定数量进行下载
            $tmp_download_urls = array_splice($this->download_urls, 0, $process_num);

            // 执行下载
            $result = $this->process($tmp_download_urls);

            if ($this->logfile) {
                // 写入日志
                $this->to_log($tmp_download_urls, $result);
            }

            // 记录已处理的数量
            $handle_num += count($result);

        }

        return $handle_num;

    }

    /**
     * 多进程下载文件
     * @param  Array $download_urls 本次下载的设置
     * @return Array
     */
    public function process($download_urls)
    {

        // 文件资源
        $fp = array();

        // curl会话
        $ch = array();

        // 执行结果
        $result = array();

        // 创建curl handle
        $mh = curl_multi_init();

        // 循环设定数量
        foreach ($download_urls as $k => $config) {
            $ch[$k] = curl_init();
            $fp[$k] = fopen($config[1], 'a');

            curl_setopt($ch[$k], CURLOPT_URL, $config[0]);
            curl_setopt($ch[$k], CURLOPT_FILE, $fp[$k]);
            curl_setopt($ch[$k], CURLOPT_HEADER, 0);
            curl_setopt($ch[$k], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$k], CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)');

            // 加入处理
            curl_multi_add_handle($mh, $ch[$k]);
        }

        $active = null;

        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($active);

        // 获取数据
        foreach ($fp as $k => $v) {
            fwrite($v, curl_multi_getcontent($ch[$k]));
        }

        // 关闭curl handle与文件资源
        foreach ($download_urls as $k => $config) {
            curl_multi_remove_handle($mh, $ch[$k]);
            fclose($fp[$k]);

            // 检查是否下载成功
            if (file_exists($config[1])) {
                $result[$k] = true;
            } else {
                $result[$k] = false;
            }
        }

        curl_multi_close($mh);

        return $result;

    }

    /**
     * 写入日志
     * @param Array $data 下载文件数据
     * @param Array $flag 下载文件状态数据
     */
    private function to_log($data, $flag)
    {
        // 临时日志数据
        $tmp_log = '';

        foreach ($data as $k => $v) {
            $tmp_log .= '[' . date('Y-m-d H:i:s') . '] url:' . $v[0] . ' file:' . $v[1] . ' status:' . $flag[$k] . PHP_EOL;
        }

        // 创建日志目录
        if (!is_dir(dirname($this->logfile))) {
            mkdir(dirname($this->logfile), 0777, true);
        }

        // 写入日志文件
        file_put_contents($this->logfile, $tmp_log, FILE_APPEND);
    }
}
