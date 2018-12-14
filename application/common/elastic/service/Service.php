<?php
// +---------------------------------------------------------------------
// | ES 服务基类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\elastic\service;

/**
 * 服务层基础类
 */
class Service
{
    /**
     * 处理es列表信息
     *
     * @param array $list es返回结果集
     * @param  integer $offset 偏移量
     * @param  integer $length 获取得数据长度
     *
     * @return array 处理后的资源列表信息
     */
    protected function disposeHits($list, $offset, $length)
    {
        $res = [];
        // 处理列表信息
        $res['total'] = $list['hits']['total'];
        $res['offset'] = $offset;
        $res['length'] = $length;
        $res['list'] = [];
        if (!$list['hits']['hits']) {
            return $res;
        }
        $res['list'] = [];
        foreach ($list['hits']['hits'] as $key => $item) {
            $res['list'][$key] = $item['_source'];
        }
        return $res;
    }

    /**
     * 替换高亮数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-11-09
     * @param    [type]       $list [description]
     * @return   [type]             [description]
     */
    protected function subsHighlightData($list, $offset, $length)
    {
        $res = [];
        if (!$list['hits']['hits']) {
            return $res;
        }

        if(!isset($list['hits']['hits']['0']['highlight'])){
            return $this->disposeHits($list, $offset, $length);
        }
        
        // 处理列表信息
        $res['total'] = $list['hits']['total'];
        $res['offset'] = $offset;
        $res['length'] = $length;
        $res['list'] = [];
        
        $res['list'] = [];
        foreach ($list['hits']['hits'] as $key => $item) {
            $doc = $item['_source'];
            foreach ($item['highlight'] as $k => $v_h) {
                if(isset($doc[$k])){
                    $doc[$k] = '';
                    foreach ($v_h as $k_i => $hightItem) {
                        $doc[$k] .= $hightItem;
                    }
                }
            }
            $res['list'][$key] = $doc;
        }
        return $res;
    }

}