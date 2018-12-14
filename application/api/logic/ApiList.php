<?php
// +----------------------------------------------------------------------
// | Api 管理
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\logic;

/**
 * Api管理
 */
class ApiList extends ApiLogic
{
    //接口前缀
    protected $api_prefix = '/api/';
    //数据类型
    protected $dataType = [
        0 => '字符',
        1 => '数字',
        2 => '数组',
        3 => '文件',
    ];

    //接口状态
    protected $apiStatus = [
        0 => '已停用',
        1 => '已完成',
        2 => '测试中',
    ];

    //是否必填
    protected $isRequire = [
        0 => '否',
        1 => '是',
    ];

    /**
     * [getInfo 获取某个接口数据]
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-16
     * @param    array        $where  [查询条件]
     * @param    boolean      $toZhCn [是否转换为可读文字]
     * @return   [type]               [description]
     */
    public function getInfo($where = [],$toZhCn = true)
    {
        $info    = $this->modelApiList->where($where)->find();
        $apiInfo = [];
        if ($info) {
            $apiInfo = $info->getdata();
            if ($apiInfo['request_data']) {
                $apiInfo['request_data'] = json_decode($apiInfo['request_data'], true);
                if($toZhCn){
                    foreach ($apiInfo['request_data'] as $key => $value) {
                        $apiInfo['request_data'][$key]['data_type']  = $this->dataType[$value['data_type']];
                        $apiInfo['request_data'][$key]['is_require'] = $this->isRequire[$value['is_require']];
                    }
                }
            }
            $apiInfo['request_url']     = $this->api_prefix . $apiInfo['api_url'];
            $apiInfo['api_status_text'] = $this->apiStatus[$apiInfo['status']];
        }
        return $apiInfo;

    }
    

}
