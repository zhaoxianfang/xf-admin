<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use app\common\logic\Auth;
use think\facade\Request;

/**
 * 检测页面权限
 * @Author   ZhaoXianFang
 * @DateTime 2018-07-04
 * @param    string       $str      [description]
 * @param    string       $isPath   [是否为完整权限全称]
 * @return   [type]                 [description]
 */
function check_page_auth($str = '', $isPath = false)
{
    $auth = Auth::instance();
    if ($isPath) {
        $path = $str;
    } else {
        $request = Request::instance();
        // $module     = strtolower($request->module());
        $controllername = strtolower($request->controller());
        // $actionname     = strtolower($request->action());
        // $path = str_replace('.', '/', $controllername) . '/' . $actionname;
        $path = str_replace('.', '/', $controllername) . '/' . $str;
    }
    // 判断控制器和方法判断是否有对应权限
    if (!$auth->check($path)) {
        return false;
    }

    return true;
}
/**
 * 通过类创建逻辑闭包
 */
function create_closure($object = null, $method_name = '', $parameter = [])
{

    $func = function () use ($object, $method_name, $parameter) {

        return call_user_func_array([$object, $method_name], $parameter);
    };

    return $func;
}

/**
 * 通过闭包控制缓存 默认缓存 10 分钟 10*60
 */
function auto_cache($key = '', $func = null, $time = 600)
{

    $result = cache($key);

    if (empty($result)) {

        $result = $func();

        !empty($result) && cache($key, $result, $time);
    }

    return $result;
}

/**
 * 字符串替换
 */
function sr($str = '', $target = '', $content = '')
{

    return str_replace($target, $content, $str);
}

/**
 * 字符串前缀验证
 */
function str_prefix($str, $prefix)
{

    return strpos($str, $prefix) === 0 ? true : false;
}

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function data_md5($str, $key = 'kmxxgAdmin')
{

    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 使用上面的函数与系统加密KEY完成字符串加密
 * @param  string $str 要加密的字符串
 * @return string
 */
function data_md5_key($str, $key = '')
{

    if (is_array($str)) {

        ksort($str);

        $data = http_build_query($str);

    } else {

        $data = (string) $str;
    }

    return empty($key) ? data_md5($data, 'kmxxgAdmin') : data_md5($data, $key);
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data)
{

    // 数据类型检测
    if (!is_array($data)) {

        $data = (array) $data;
    }

    // 排序
    ksort($data);

    // url编码并生成query字符串
    $code = http_build_query($data);

    // 生成签名
    $sign = sha1($code);

    return $sign;
}

/**
 * 生成密码
 * @Author   ZhaoXianFang
 * @DateTime 2018-06-13
 * @param    string       $password [description]
 * @param    string       $sale     [description]
 */
function setPwd($password = '', $sale = '')
{
    return data_md5_key($password, $sale);
}

/**
 * 检测当前用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 */
function is_administrator($member_id = null)
{

    $return_id = is_null($member_id) ? is_login() : $member_id;

    return $return_id && (intval($return_id) === SYS_ADMINISTRATOR_ID);
}

/**
 * 判断用户头像 如果找不到头像则返回系统默认头像
 * @Author   ZhaoXianFang
 * @DateTime 2018-05-25
 * @param    string       $img [用户头像地址]
 * @return   boolean           [description]
 */
function headImg($img = '')
{
    return is_file($img) ? $img : request()->domain() . request()->root() . '/static/libs/adminlte/img/head.png';
}

/**
 * @Author:  赵先方
 * @功能:二维数组去重
 * @param $array 传入二维数组
 * @param $key 去重字段 //去重条件
 */
function array_unique_xf($array, $key = 'id')
{
    $tmp_arr = array(); //声明数组
    foreach ($array as $k => $v) {
        //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true

        if (in_array($v[$key], $tmp_arr)) {
            unset($array[$k]); //删除掉数组（$arr）里相同ID的数组
        } else {
            $tmp_arr[] = $v[$key]; //记录已有的id
        }
    }
    return $array;
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname 目录
     * @param bool $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }

}

/**
 * 下划线转驼峰
 * @Author   ZhaoXianFang
 * @DateTime 2018-08-29
 * @return   [type]       [description]
 */
function convert_underline($str)
{
    $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
        return strtoupper($matches[2]);
    }, $str);
    return $str;
}

if (!function_exists('truncate')) {
    /**
     * 文章去去除标签截取文字
     * @Author   ZhaoXianFang
     * @DateTime 2018-09-12
     * @param    [type]       $string [被截取字符串]
     * @param    integer      $length [长度]
     * @param    boolean      $append [是否加...]
     * @return   [type]               [description]
     */
    function truncate($string, $length = 150, $append = true)
    {
        $string    = html_entity_decode($string);
        $string    = trim(strip_tags($string,'<em>'));
        $strlength = strlen($string);
        if ($length == 0 || $length >= $strlength) {
            return $string;
        } elseif ($length < 0) {
            $length = $strlength + $length;
            if ($length < 0) {
                $length = $strlength;
            }
        }
        if (function_exists('mb_substr')) {
            $newstr = mb_substr($string, 0, $length, "UTF-8");
        } elseif (function_exists('iconv_substr')) {
            $newstr = iconv_substr($string, 0, $length, "UTF-8");
        } else {
            for ($i = 0; $i < $length; $i++) {
                $tempstring = substr($string, 0, 1);
                if (ord($tempstring) > 127) {
                    $i++;
                    if ($i < $length) {
                        $newstring[] = substr($string, 0, 3);
                        $string      = substr($string, 3);
                    }
                } else {
                    $newstring[] = substr($string, 0, 1);
                    $string      = substr($string, 1);
                }
            }
            $newstr = join($newstring);
        }
        if ($append && $string != $newstr) {
            $newstr .= '...';
        }
        return $newstr;
    }
}