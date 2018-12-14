<?php

namespace app\common\logic;
use think\Collection;

use app\common\model\Base as ModelBase;

/**
 * 获取地区逻辑模型
 */
class Position extends ModelBase
{
    public function getListData($where = [])
    {
        return $this->modelPosition->field('code_id as value,position_name as name')->where($where)->select();
        
    }
}
