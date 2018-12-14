<?php
// +---------------------------------------------------------------------
// | 敏感词分类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\model;

class SensitiveClassify extends AdminModel
{
	public function getLevelAttr($value)
    {
        $levelList = $this->logicSensitiveClassify->getLevelList();
        return $levelList[$value];
    }
}
