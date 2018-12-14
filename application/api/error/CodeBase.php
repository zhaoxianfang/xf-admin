<?php
// +----------------------------------------------------------------------
// |  api 错误码 对应文档
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\error;

class CodeBase
{

    public static $success              = [API_CODE_NAME => 0,         API_MSG_NAME => '操作成功'];
    
    public static $accessTokenError     = [API_CODE_NAME => 1000001,   API_MSG_NAME => '访问Toekn错误'];
    
    public static $userTokenError       = [API_CODE_NAME => 1000002,   API_MSG_NAME => '用户Toekn错误'];
    
    public static $apiUrlError          = [API_CODE_NAME => 1000003,   API_MSG_NAME => '接口路径错误'];
    
    public static $dataSignError        = [API_CODE_NAME => 1000004,   API_MSG_NAME => '数据签名错误'];


    public static $code = [
        0   => '操作成功',
        1   => '未知错误',
        500 => '请求出错',

        // 公共模块
        10001   => '没有数据啦',
        10002   => '请求参数不完整',
        10003   => '请求类型错误',
        10004   => '操作失败',
        10005   => '数据量过大',
        10006   => '数据已存在',
        10007   => '结束时间需大于开始时间',
        10008   => '该用户不存在',
        10009   => '缺少参数',
        10010   => 'TOKEN 已过期',
        10011   => 'TOKEN 解析失败',
        10012   => 'TOKEN 已失效',
        10013   => '测试 TOKEN 已停用',


        // 用户模块
        20001   => '用户名不存在',
        20002   => '密码错误',
        20003   => '登陆失败',
        20004   => '获取TOKEN失败',
        20005   => '用户微信信息写入失败',
        20006   => '退出失败',

        // 重点关注
        30001   => '关注类型错误',
        30002   => '关注类型ID错误',
        30003   => '关注类型下无数据',

        // 报道统计
        40001   => '报表模板不存在',
        40002   => '选择时间范围超过了系统设定的时间差',
        40003   => '报表同步到Redis失败',
        40004   => '获取详情数据异常',

    ];

    /**
     * 获取错误码
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @return   [type]       [description]
     */
    public static function getCode($code_id = '')
    {
        if (!isset(self::$code[$code_id])) {
            $code_id = 2;
        }
        return self::$code[$code_id];
    }

}
