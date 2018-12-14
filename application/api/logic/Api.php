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
use think\Collection;
/**
 * Api管理
 */
class Api extends ApiLogic
{
    /**
     * 获取api 分组列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @param    array        $where [description]
     * @return   [type]              [description]
     */
    public function getApiGroupList($where = array())
    {
        return $this->modelApiGroup->where($where)->select();
    }

    /**
     * 通过appid 或者所有的api列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @param    string       $app_id [description]
     * @return   [type]               [description]
     */
    public function getApplistByAppid($app_id = '')
    {
        $groupList = $this->modelApiGroup->where(['app_id' => $app_id])->order('sort desc')->select();
        $groupArr  = [];
        $groupIds  = [];
        foreach ($groupList as $key => $group) {
            $groupArr[$group['id']] = $group;
            $groupIds[]             = $group['id'];
        }
        
        $apiGroupList = Collection::make($groupArr)->toArray();
        unset($groupArr);
        
        $allApiList = Collection::make($this->modelApiList->where('group_id', 'in', $groupIds)->select())->toArray();
        foreach ($allApiList as $key => $api) {
            $apiGroupList[$api['group_id']]['api_list'][] = $api;
        }
        return $apiGroupList;
    }

}
