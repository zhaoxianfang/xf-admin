<?php
// +----------------------------------------------------------------------
// | 后台权限管理
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-22
// +----------------------------------------------------------------------

namespace app\api\library;

use think\Db;
use think\Cache;
use util\Tree;
use app\api\model\Area;

class Auth extends \util\Auth
{
    protected $topAreaId    = 2824; //云南省
    
    public function getAreaIds($uid = 0)
    {
        $areaids = parent::getAreaIds($uid);
        // 不是超级管理员直接返回
        if (!in_array('*', $areaids)) {
            // 移除父级的id
            // dump($this->getGroups($uid));die;
            return $areaids;
        }
        // 是超级管理员获取全部区域ID
        $areaids = Db::name('area')
            ->where('status', 1)
            ->where('area_id',['>=',$this->topAreaId],['=',1],'or')
            ->column('area_id')
        ;
        return $areaids;
    }

    public function getSourceIds($uid = 0)
    {
        $sourceIds = parent::getSourceId($uid);
        // 不是超级管理员直接返回
        if (!in_array('*', $sourceIds)) {
            return $sourceIds;
        }
        // 是超级管理员获取全部区域ID
        $sourceIds = Db::name('area')
            ->where('status', 1)
            ->column('area_id')
        ;
        return $sourceIds;
    }

    public function getRuleIds($uid = null)
    {
        return is_null($uid) ? '':parent::getRuleIds($uid);
    }

    public function isSuperAdmin($uid = null)
    {
        return in_array('*', $this->getRuleIds($uid)) ? true : false;
    }

    /**
     * [getJoinAreaInfo 获取关系地区列表信息]
     * @Author   ZhaoXianFang
     * @DateTime 2018-04-28
     * @param    boolean      $return       [是否返回地区]
     * @param    array        $findChildOpt [如果需要查询地区树的某个节点的所有子节点，才传入该配置信息]
     *                                      demo:$findChildOpt=['ids'=>['1','2','3'],'type'=>'childs' ]
     * @return   [type]                     [description]
     */
    public function getJoinAreaInfo($return = true, $findChildOpt = [], $user_id = 0)
    {
        if (!Cache::has('JoinAreaInfo_' . $user_id) || !empty($findChildOpt)) {
            
            static $areadata = [];
            $AreaList        = collection(
                Area::where('area_id', 'in', $this->getAreaIds($user_id))
                    ->where(['status'=> 1])
                    ->where('area_id','lt',3931)
                    ->where('area_id','not in',[3927,3928,3929])
                    ->field('*,area_id AS rule_id,fid AS pid')
                    ->select()
            )->toArray();

            foreach ($AreaList as $key => &$value) {
                $value['rule_id'] = $value['area_id'];
                $value['pid']     = $value['fid'];
            }

            $treeClass = Tree::instance();
            $allArea   = $treeClass->init($AreaList)->getTreeTwo();

            // 如果需要返回子节点(包含自己)
            if ($findChildOpt) {

                $findArr = [];
                switch ($findChildOpt['type']) {
                    case 'childs':
                        $fun = 'getChildrenIds';
                        break;
                    case 'parent':
                        $fun = 'getParentsIds';
                        break;
                    default:
                        $fun = 'getChildrenIds';
                        break;
                }

                foreach ($findChildOpt['ids'] as $key => $fid) {
                    $findArr[] = $treeClass->$fun($fid, true);
                }

                return $findArr;
            }
            $tempArea = $treeClass->findArea($allArea, $this->areaTopId);
            // 1 表示 忽略省级 2表示忽略市级
            for ($i = 0; $i < 2; $i++) {
                if (count($tempArea['childlist']) <= 1) {
                    $tempArea = $tempArea['childlist']['0'];
                }
            }

            $areadata = $treeClass->areaArr($tempArea);

            Cache::set('JoinAreaInfo_' . $user_id, $areadata, 86400);
        }

        $areadata = Cache::get('JoinAreaInfo_' . $user_id);
        
        if ($return) {
            return $areadata;
        }
    }
}
