<?php
// +---------------------------------------------------------------------
// | 系统初始化加载
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\model;

/**
 * 日志模型
 */
class Log extends Base
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = '';
    //自定义日志标题
    protected static $title = '';
    //自定义日志内容
    protected static $content = '';
    //自定义日志类型
    protected static $logtype = '0';
    //自定义日志操作账号
    protected static $account = '';

    static public function setContent($content)
    {
        self::$content = $content;
    }

    static public function setTitle($title)
    {
        self::$title = $title;
    }
    /**
     * 设置附加参数
     * @Author   ZhaoXianFang
     * @DateTime 2018-10-16
     * @param    [type]       $logtype [日志类型]
     * @param    [type]       $account [附加账号]
     */
    static public function setPara($logtype = 0, $account = '')
    {
        self::$logtype = (int) $logtype;
        self::$account = $account;
    }

    /**
     * 检测是否需要写日志
     * @Author   ZhaoXianFang
     * @DateTime 2018-10-16
     * @return   [type]       [description]
     */
    public function checkWrite()
    {
        return self::$title ? true : self::$content ? true : false;
    }

    public function record($title = '')
    {

        $username = '未知';
        $content  = self::$content;
        if (!$content) {
            $content = request()->param();
            foreach ($content as $k => $v) {
                if (is_string($v) && strlen($v) > 200 || stripos($k, 'password') !== false) {
                    unset($content[$k]);
                }
            }
        }

        $title = self::$title;
        if (!$title) {
            $title = $this->logicAuth->getBreadCrumb(false);
        }
        $sessionPrefix = $this->logicAuth->getSessionPrefix();

        $user_id = session($sessionPrefix . '.id') ? session($sessionPrefix . '.id') : '';

        if (!$user_id) {
            return false;
        }

        self::create([
            'title'       => $title,
            'content'     => !is_scalar($content) ? json_encode($content) : $content,
            'url'         => request()->url(),
            'uid'         => $user_id,
            'useragent'   => request()->server('HTTP_USER_AGENT'),
            'ip'          => request()->ip(),
            'create_time' => time(),
            'type'        => self::$logtype,
            'account'     => self::$account,
        ]);
    }

}
