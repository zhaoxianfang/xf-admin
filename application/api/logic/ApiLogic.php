<?php
// +----------------------------------------------------------------------
// | Api基础逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\logic;

use app\api\error\CodeBase;
use app\api\model\ApiModel;
use Firebase\JWT\JWT;
use think\facade\Config;
use think\facade\Request;

/**
 * Api基础逻辑
 */
class ApiLogic extends ApiModel
{

    /**
     * API返回数据
     */
    public function apiReturn($code_data = [], $return_data = [], $return_type = 'json')
    {
        if (is_array($code_data) && array_key_exists('code', $code_data)) {

            if (!array_key_exists('msg', $code_data)) {
                $code_data['msg'] = CodeBase::getCode($code_data['code']);
            }
            !empty($return_data) && $code_data['data'] = $return_data;
            $result                                    = $code_data;

        } else {
            if (is_numeric($code_data)) {
                $result['code']                         = $code_data;
                $result['msg']                          = CodeBase::getCode($code_data);
                !empty($return_data) && $result['data'] = $return_data;
            } else {
                $result                               = CodeBase::$success;
                !empty($code_data) && $result['data'] = $code_data;
            }
        }

        $return_result = $this->checkDataSign($result);

        $return_result['exe_time'] = debug('api_begin', 'api_end');

        return $return_type == 'json' ? json($return_result) : $return_result;
    }

    /**
     * 检查是否需要响应数据签名
     */
    public function checkDataSign($data)
    {

        //检查是否需要响应数据签名

        return $data;
    }

    /**
     * API错误终止程序
     */
    public function apiError($code_data = [])
    {

        return throw_response_exception($code_data);
    }

    /**
     * API提交附加参数检查
     */
    public function checkParam($param = [])
    {
        //通过路由获取api 信息
        $apiInfo = $this->getApiInfoByRoot();
        if (!$apiInfo) {
            return false;
        }
        //验证 接收到的参数格式 是否合格
        $this->checkRequestData($apiInfo);
        //出错时返回 $this->apiError(['code'=>xxx,'msg'=>'xxxx']);

    }

    /**
     * 通过路由获取api 信息
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-16
     * @return   [type]       [description]
     */
    public function getApiInfoByRoot()
    {
        //获取接口 配置信息
        $controller = strtr($this->humpToLine(request()->controller()), '.', '/');
        $action     = $this->humpToLine(strtolower(request()->action()));//驼峰转换 驼峰转下划线
        $apiUrl     = $controller . '/' . $action;
        $apiInfo    = $this->logicApiList->getInfo(['api_url' => $apiUrl], false);
        return $apiInfo ?: false;
    }

    /**
     * [checkRequestData 验证 接收到的参数格式 是否合格]
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-16
     * @param    array        $data [api 数据]
     * @return   [type]             [description]
     * @example request_data 填入的格式
     * $arr = [
     *      [
     *          "field_name"=>"appid",
     *          "data_type"=>0,
     *          "is_require"=>0,
     *          "field_describe"=>"应用ID"
     *      ],[
     *          "field_name"=>"data[publish_time]",
     *          "data_type"=>0,
     *          "is_require"=>1,
     *          "field_describe"=>"发布时间"
     *      ]
     *  ]
     *  json_encode($arr);
     */
    public function checkRequestData($data = [])
    {

        //请求的参数
        $param = request()->param();

        //验证请求方式
        if (strtolower(request()->method()) != strtolower($data['method'] ?: 'post')) {
            return $this->apiError(['code' => 10003, 'msg' => '请求类型错误']);
        }

        //判断是否验证token
        if (isset($data['check_token']) && 1 == $data['check_token']) {
            request()->input(['token_user_id' => 0]);

            //接口需要检测token值
            $this->checkUserToken();
        }
        //判断是否有请求格式
        if (!isset($data['request_data']) || empty($data['request_data'])) {
            return true;
        }

        //请求数据验证格式
        $checkDataFormat = [];
        foreach ($data['request_data'] as $key => $format) {
            $field_name           = $this->bracketToArr($format['field_name'], true);
            $paramArr             = $this->bracketToArr($format['field_name']);
            $format['field_name'] = $field_name['0'];
            $checkDataFormat      = $this->refactorParameter($checkDataFormat, $paramArr, $format);
        }

        foreach ($checkDataFormat as $key => $value) {
            if (isset($value['field_name'])) {
                //一维参数
                if ($value['is_require'] && empty($param[$value['field_name']])) {
                    //未填写必填参数
                    $this->apiError(['code' => 10009, 'msg' => '缺少参数:' . $value['field_describe'] . '[' . $value['field_name'] . ']']);
                }
                continue;
            }

            //二维
            foreach ($value as $k => $val) {
                if (isset($val['field_name'])) {
                    //二维参数
                    if ($val['is_require'] && empty($param[$key][$val['field_name']])) {
                        //未填写必填参数
                        $this->apiError(['code' => 10009, 'msg' => '缺少参数:' . $val['field_describe'] . '[' . $val['field_name'] . ']']);
                    }
                    continue;
                }

                //三维
                foreach ($val as $k_k => $v) {
                    if (isset($v['field_name'])) {
                        //三维参数
                        if ($v['is_require'] && empty($param[$key][$k][$v['field_name']])) {
                            //未填写必填参数
                            $this->apiError(['code' => 10009, 'msg' => '缺少参数:' . $v['field_describe'] . '[' . $v['field_name'] . ']']);
                        }
                    }
                    continue;
                }
            }
        }

        // 如果需要验证其他字段，在下面追加验证
        return true;
    }

    /**
     * 把带方括号字符串中的字符串提取出来并返回数组
     * @Author   ZhaoXianFang
     * @DateTime 2018-11-07
     * @param    [type]       $str          [description]
     * @param    [type]       $getEndParam  [最后一个参数]
     * @return   [type]                     [description]
     * @example  a[b][c]     =>             ['a','b',c]
     *           abc         =>             ['abc']
     *           [a][b][c]   =>             ['a','b',c]
     */
    private function bracketToArr($str, $getEndParam = false)
    {
        if (strpos($str, "[") === false) {
            return [$str];
        }
        //第一个参数
        $firstPara = substr($str, 0, strpos($str, "["));

        $strPattern = "/(?<=\[)([^\]]*?)(?=\])/";
        $result     = [];
        preg_match_all($strPattern, $str, $result);

        if ($result[1]) {
            if ($getEndParam) {
                return [end($result[1])];
            }
            return array_merge([$firstPara], $result[1]);
        }
        return [$firstPara];
    }

    /**
     * 参数重构
     * @Author   ZhaoXianFang
     * @DateTime 2018-11-07
     * @param    string       $param [description]
     * @return   [type]              [description]
     */
    private function refactorParameter($arr, $param, $value)
    {
        $result = null;
        // 弹出最后一个元素
        for ($i = count($param) - 1; $i >= 0; $i--) {
            if ($result === null) {
                $result[$param[$i]] = $value;
                // $result = end($param);
            } else {
                $result = array($param[$i] => $result);
            }
        }
        return array_merge_recursive($arr, $result);

    }

    /**
     * 检测用户token值是否有效
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-27
     * @return   [type]       [description]
     */
    public function checkUserToken()
    {
        $param = request()->param();
        if (isset($param['token']) && !empty($param['token'])) {
            if (get_access_token() == $param['token']) {
                $useToken = Config::get('site.api_use_test_token');
                if ($useToken != 1) {
                    return $this->apiError(['code' => 10013, 'msg' => '测试 TOKEN 已经停用']);
                }
                //测试token
                Request::instance()->post(['token_user_id' => 17]);
                return true;
            } else {
                // 解析token获取用户信息
                try {
                    // 获取jwtkey
                    $jwtkey = Config::get('wxapi.jwt_key') ?: 'kmxxg';

                    $decoded = JWT::decode($param['token'], $jwtkey, array('HS256'));
                } catch (\UnexpectedValueException $e) {
                    return $this->apiError(['code' => 10011, 'msg' => 'TOKEN 解析失败']);
                } catch (\DomainException $e) {
                    return $this->apiError(['code' => 10011, 'msg' => 'TOKEN 解析失败']);
                }

                // 用户信息部分
                $user_info['user_id']  = $decoded->data->user_id;
                $user_info['username'] = $decoded->data->username;

                // 对token进行验证，确保唯一可用
                $map['user_id'] = $user_info['user_id'];
                $py_token       = $this->modelWepy->where($map)->value('token');

                if ($param['token'] != $py_token) {
                    return $this->apiError(['code' => 10012, 'msg' => 'TOKEN 已失效']);
                }

                request()->input(['token_user_id' => $user_info['user_id']]);

                return true;
            }
        } else {
            return $this->apiError(['code' => 10010, 'msg' => 'TOKEN 已过期']);
        }
    }

    /**
     * 下划线转驼峰
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-29
     * @return   [type]       [description]
     */
    public function convertUnderline($str)
    {
        $str = preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str);
        return $str;
    }

    /**
     * 驼峰转下划线
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-29
     * @return   [type]       [description]
     */
    private function humpToLine($str)
    {
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);
        return trim($str, '_');
    }

    private function convertHump(array $data)
    {
        $result = [];
        foreach ($data as $key => $item) {
            if (is_array($item) || is_object($item)) {
                $result[$this->humpToLine($key)] = $this->convertHump((array) $item);
            } else {
                $result[$this->humpToLine($key)] = $item;
            }
        }
        return $result;
    }
}
