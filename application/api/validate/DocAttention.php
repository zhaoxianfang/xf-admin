<?php
// +----------------------------------------------------------------------
// | Api 重点关注验证
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2017-12-17
// +----------------------------------------------------------------------

namespace app\api\validate;

use think\Validate;

class DocAttention extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'type'          => 'require|number',
        'type_id'       => 'require|number',
        'user_id'       => 'require|number',
        'start_time'    => 'require',
        'end_time'      => 'require',
          
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'type.require'          => '关注类型不为空',
        'type.number'           => '关注类型为整数',
        'type_id.require'       => '关注类型ID不为空',
        'type_id.number'        => '关注类型ID为整数',
        'start_time.require'    => '关注开始时间不为空',
        'start_time.require'    => '关注结束时间不为空',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'   => ['type', 'type_id', 'user_id', 'start_time', 'end_time'],
        'edit'  => ['type', 'type_id'],
    ];

}