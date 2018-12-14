<?php
// +----------------------------------------------------------------------
// | 微信小程序 api 文档
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-11-07
// +----------------------------------------------------------------------

namespace app\api\controller\sync;

use app\api\controller\ApiBase;
use app\api\error\CodeBase;

/**
 * 微信小程序管理
 */
class Doc extends ApiBase
{
    //当前应用对应的ID
    protected $app_id = 1;

    protected $app_name = '数据同步';

    public function initialize()
    {
        parent::initialize();
        $this->view->assign('app_name', $this->app_name);

    }

    /**
     * 列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @return   [type]       [description]
     */
    public function index()
    {
        $code_list = CodeBase::$code;

        $this->assign('code_list', $code_list);
        $content = $this->fetch('doc/content_default');
        $this->assign('content', $content);

        $apiList = $this->logicApi->getApplistByAppid($this->app_id);
        $appUrl = $this->logicApiApp->getUrl($this->app_id);

        $this->view->assign('list', $apiList);
        $this->view->assign('app_url', $appUrl);
        
        return $this->fetch('doc/index');
    }

    /**
     * [details API详情]
     * @Author   ZhaoXianFang
     * @DateTime 2018-08-15
     * @return   [type]           [description]
     */
    public function details()
    {
    	if (!IS_AJAX) {
    		$this->redirect(url('index'),302);
    	}
    	$apiId = $this->param['id'];
    	
        $info = $this->logicApiList->getInfo(['id' => $apiId]);
        
        $this->assign('info', $info);
        
        // 测试期间使用token ， 测试完成请删除
        $this->assign('test_access_token', get_access_token());
        
        $content = $this->fetch('doc/content_template');
        
        return throw_response_exception(['content' => $content]);
        
    }

}
