<?php
namespace app\facade;

class SyncLog extends Base
{
	protected static function getFacadeClass()
    {
    	return 'app\common\model\SyncLog';
    }
}
