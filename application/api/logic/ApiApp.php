<?php
// +----------------------------------------------------------------------
// | Api 应用管理
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-15
// +----------------------------------------------------------------------

namespace app\api\logic;

/**
 * Api应用管理
 */
class ApiApp extends ApiLogic
{

    /**
     * 获取列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @return   [type]       [description]
     */
    public function getList($where = array())
    {
        return $this->modelApiApp->where(['app_status' => 1, 'show_api_doc' => 1])->where($where)->select();
    }

    /**
     * 获取app 文档地址
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-16
     * @param    string       $app_id [description]
     * @return   [type]               [description]
     */
    public function getUrl($app_id = '')
    {
        return $this->modelApiApp->where(['app_status' => 1])->where(['id' => $app_id])->value('url');
    }

}
