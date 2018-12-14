<?php
// +---------------------------------------------------------------------
// | ES 查询
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\elastic\lib\query;

use app\common\elastic\lib\Query;

/**
 * 索引构造器
 */
class BuildIndex extends Query
{
    /**
     * 设置索引名称
     */
    public function setIndex($name)
    {
        $this->option['index'] = $name;
        return $this;
    }

    /**
     * 设置索引type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * 设置分析器为IK分析器
     */
    public function setAnalysis()
    {
        $this->option['settings']['analysis']['analyzer']['ik']['tokenizer'] = 'ik_max_word';
        return $this;
    }

    /**
     * 设置映射字段
     *
     * @param  string $field   字段名称
     * @param  string $type       字段类型
     * @param  string $analyzer   分词器
     * @param  string $index      索引类型  
     *
     * @return $this
     */
    public function setMapping($field, $type, $analyzer = '', $index = 'not_analyzed')
    {
        $properties['properties'][$field]['type'] = $type;
        $properties['properties'][$field]['index'] = $index;

        if ($analyzer) {
            $properties['properties'][$field]['analyzer'] = $analyzer;
        }

        $index_type =  $this->type ? $this->type : '_default_';
        $this->option['body']['mappings'][$inde_type] = $properties;

        return $this;
    }

    /**
     * 构建参数
     */
    public function build()
    {
        return $this->option;
    }

}