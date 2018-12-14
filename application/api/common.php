<?php
// +----------------------------------------------------------------------
// | API 基础方法
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

/**
 * 应用公共（函数）文件
 */

use think\exception\HttpResponseException;
use think\Response;

if (!function_exists('sr')) {
/**
 * 字符串替换
 */
    function sr($str = '', $target = '', $content = '')
    {

        return str_replace($target, $content, $str);
    }
}
if (!function_exists('str_prefix')) {
/**
 * 字符串前缀验证
 */
    function str_prefix($str, $prefix)
    {
        return strpos($str, $prefix) === 0 ? true : false;
    }
}
if (!function_exists('throw_response_exception')) {
/**
 * 抛出响应异常
 */
    function throw_response_exception($data = [], $type = 'json')
    {
        $response = Response::create($data, $type);
        throw new HttpResponseException($response);
    }
}

if (!function_exists('get_access_token')) {
/**
 * 获取访问token
 */
    function get_access_token()
    {

        return md5('wenming' . date("Ymd") . API_KEY);
    }
}
