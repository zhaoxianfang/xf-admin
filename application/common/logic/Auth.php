<?php
// +---------------------------------------------------------------------
// | 权限
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\common\logic;

use think\Collection;
use think\Container;
use think\facade\Request;
use think\facade\Session;
use think\Exception;
use util\Menu;

class Auth extends \util\Auth
{

    /**
     * @var object 对象实例
     */
    protected static $instance;

    protected $_error     = '';
    protected $requestUri = '';
    //调用模块的门面模型
    protected $facadeModelClass = '';
    //调用模块session前缀名称
    protected $sessionPrefix = '';

    protected $logined = false; //登录状态

    public function __construct()
    {
        parent::__construct();
        try {
            $this->facadeModelClass = 'app\facade\\'.ucfirst(strtolower(request()->module()));
            $this->sessionPrefix = $this->facadeModelClass::getSessionPrefix();
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    public function __get($name)
    {
        return Session::get($this->sessionPrefix . '.' . $name);
    }

    //获取模型sisson名称
    public function getSessionPrefix()
    {
        return $this->sessionPrefix;
    }

    public function check($name = '', $uid = '', $relation = 'or', $mode = 'url')
    {
        $path = $name ? $name : MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
        $path = str_replace(".", "/", $path);
        return parent::check($path, $uid, $relation, $mode);
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     *
     * @param array $arr 需要验证权限的数组
     */
    public function match($arr = [])
    {
        $request = Request::instance();
        $arr     = is_array($arr) ? $arr : explode(',', $arr);
        if (!$arr) {
            return false;
        }

        $arr = array_map('strtolower', $arr);
        // 是否存在
        if (in_array(strtolower($request->action()), $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 检测是否登录
     *
     * @return boolean
     */
    public function isLogin()
    {
        if ($this->logined) {
            return true;
        }

        $moduleUser = Session::get($this->sessionPrefix);
        if (!$moduleUser) {
            return false;
        }

        $this->logined = true;
        return true;
    }

    public function getGroups($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getGroups($uid);
    }

    public function getRuleList($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleList($uid);
    }

    public function getUserInfo($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;

        return $uid != $this->id ? $this->facadeModelClass::get(intval($uid)) : Session::get($this->sessionPrefix);
    }

    public function getRuleIds($uid = null)
    {
        $uid = is_null($uid) ? $this->id : $uid;
        return parent::getRuleIds($uid);
    }

    public function isSuperAdmin()
    {
        $memberInfo             = session($this->sessionPrefix);
        $memberInfo['group_id'] = isset($memberInfo['group_id']) ? $memberInfo['group_id'] : 0;
        $memberGroup            = \app\admin\model\AuthGroup::where(['id' => $memberInfo['group_id']])->cache(true)->find();
        if ($memberGroup && $memberGroup['rules'] == '*') {
            return true;
        }
        return SYS_ADMINISTRATOR_ID == $this->id;
    }

    /**
     * 获取管理员所属于的分组ID
     * @param int $uid
     * @return array
     */
    public function getGroupIds($uid = null)
    {
        $groups   = $this->getGroups($uid);
        $groupIds = [];
        foreach ($groups as $K => $v) {
            $groupIds[] = (int) $v['group_id'];
        }
        return $groupIds;
    }

    /**
     * 取出当前管理员所拥有权限的分组
     * @param boolean $withself 是否包含当前所在的分组
     * @return array
     */
    public function getChildrenGroupIds($withself = false)
    {
        //取出当前管理员所有的分组
        $groups   = $this->getGroups();
        $groupIds = [];
        foreach ($groups as $k => $v) {
            $groupIds[] = $v['id'];
        }
        // 取出所有分组
        $groupList = \app\admin\model\AuthGroup::where(['status' => 1])->cache(true)->select();
        $objList   = [];
        foreach ($groups as $K => $v) {
            if ($v['rules'] === '*') {
                $objList = $groupList;
                break;
            }
            // 取出包含自己的所有子节点
            $childrenList = Tree::instance()->init($groupList)->getChildren($v['id'], true);
            $obj          = Tree::instance()->init($childrenList)->getTreeArray($v['pid']);
            $objList      = array_merge($objList, Tree::instance()->getTreeList($obj));
        }
        $childrenGroupIds = [];
        foreach ($objList as $k => $v) {
            $childrenGroupIds[] = $v['id'];
        }
        if (!$withself) {
            $childrenGroupIds = array_diff($childrenGroupIds, $groupIds);
        }
        return $childrenGroupIds;
    }

    /**
     * 取出当前管理员所拥有权限的管理员
     * @param boolean $withself 是否包含自身
     * @return array
     */
    public function getChildrenAdminIds($withself = false)
    {
        $childrenAdminIds = [];
        if (!$this->isSuperAdmin()) {
            $groupIds      = $this->getChildrenGroupIds(false);
            $authGroupList = \app\admin\model\AuthGroupAccess::
                field('uid,group_id')
                ->where('group_id', 'in', $groupIds)
                ->cache(true)
                ->select();

            foreach ($authGroupList as $k => $v) {
                $childrenAdminIds[] = $v['uid'];
            }
        } else {
            //超级管理员拥有所有人的权限
            $childrenAdminIds = $this->facadeModelClass::column('id');
        }
        if ($withself) {
            if (!in_array($this->id, $childrenAdminIds)) {
                $childrenAdminIds[] = $this->id;
            }
        } else {
            $childrenAdminIds = array_diff($childrenAdminIds, [$this->id]);
        }
        return $childrenAdminIds;
    }

    /**
     * 获得面包屑导航
     * @param string $path
     * @return array
     */
    public function getBreadCrumb($raturnhtml = false)
    {

        $ruleList = model('AuthRule')->cache(true)->select();
        $arrList  = Collection::make($ruleList)->toArray();

        $urlLink = $this->request->controller() . '/' . $this->request->action();
        // 面包屑导航
        $breadcrumb = Menu::instance()->init($arrList, '', '', false)->getBreadCrumb($urlLink, $raturnhtml);

        return $breadcrumb;
    }

    /**
     * 获取左侧菜单栏
     *
     * @param array $params URL对应的badge数据
     * @return string
     */
    public function getMenu($where = [])
    {
        if (!$where) {
            $where = ['status' => 1, 'ismenu' => 1];
        }

        if (!$this->isSuperAdmin()) {
            $ids         = $this->getRuleIds($this->id);
            $where['id'] = $ids;
        }

        $arrList = cache('_menu_list_' . $this->id . '_');

        if (empty($arrList)) {

            $ruleList = model('AuthRule')->getList($where);

            $arrList = Collection::make($ruleList)->toArray();
            cache('_menu_list_' . $this->id . '_', $arrList);
        }
        // 菜单
        $urlLink = $this->request->module() .'/'.$this->request->controller() . '/' . $this->request->action();
        $menuStr = Menu::instance()->init($arrList)->setActiveMenu($urlLink)->createMenu(0);
        return $menuStr;
    }

    /**
     * 删除目录缓存
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-11
     * @return   [type]       [description]
     */
    public function delMenuCache()
    {
        return cache('_menu_list_', null);
    }

    /**
     * 设置错误信息
     *
     * @param $error 错误信息
     */
    public function setError($error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->_error ? __($this->_error) : '';
    }

}
