<?php
// +----------------------------------------------------------------------
// | 文章
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ZhaoXianFang <1748331509@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-11-07
// +----------------------------------------------------------------------

namespace app\api\controller\sync;

use app\api\controller\ApiBase;
use app\common\queue\Redis;
use util\CloseHtml;

/**
 *  文章
 */
class Article extends ApiBase
{

    protected $model;

    public function initialize()
    {
        parent::initialize();

        $this->model = model('api/Article', 'logic');
    }

    /**
     * 分发文章到本系统
     * @Author   ZhaoXianFang
     * @DateTime 2018-11-07
     */
    public function add()
    {
        $data = request()->param();
        
        $data['content'] = CloseHtml::instance()->input($data['content'])->delHtmlTag(['img'])->output();
        //发布时间
        if (empty($data['publish_time'])) {
            if (isset($data['publish_time1']) && $data['publish_time1']) {
                $data['publish_time'] = $data['publish_time1'];
            } else {
                $data['publish_time'] = time();
            }
        }
        //过滤title
        $filterTitleBySourceId = config('sys.get_title1_by_source_id');
        if(!empty($filterTitleBySourceId) && isset($data['source_id']) && isset($data['title1']) && $data['title1']){
            $sourceIdArr = explode('|', trim($filterTitleBySourceId,'|') );
            if($sourceIdArr){
                if(in_array($data['source_id'], $sourceIdArr)){
                    $data['title'] = trim($data['title1']);
                }
            }
        }

        unset($data['publish_time1']);
        unset($data['title1']);

        // Log::record("提交的数据========================================" . json_encode($data));
        $insertId = Redis::init()->push($data);
        
        if ($insertId) {
            return $this->apiReturn(['code' => 0, 'msg' => '成功']);
        } else {
            return $this->apiReturn(['code' => 1, 'msg' => '失败']);
        }
    }
}
