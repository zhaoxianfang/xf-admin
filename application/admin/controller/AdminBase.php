<?php
// +---------------------------------------------------------------------
// | 后台基础类
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller;

use app\common\controller\Base;
use think\facade\Hook;

class AdminBase extends Base
{
    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 无需鉴权的方法,但需要登录
     * @var array
     */
    protected $noNeedRight = [];

    /**
     * 页面内容加载内容头
     * @var array
     */
    protected $showContentHeader = true;

    /**
     * 快速搜索时执行查找的字段
     */
    protected $searchFields = 'id';

    /**
     * 是否是关联查询
     */
    protected $relationSearch = false;

    /**
     * 是否开启数据限制
     * 支持auth/personal
     * 表示按权限判断/仅限个人
     * 默认为禁用,若启用请务必保证表中存在admin_id字段
     */
    protected $dataLimit = false;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 数据限制开启时自动填充限制字段值
     */
    protected $dataLimitFieldAutoFill = true;

    /**
     * 引入后台控制器的traits
     */
    // use \app\common\traits\Backend;

    /**
     * 基础控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {

        parent::initialize();

        // 初始化后台模块常量
        $this->logicAuth->isLogin() && $this->initAdminConst();

        // 初始化后台模块信息
        $this->initAdminInfo();

        // 后台控制器钩子
        Hook::listen('hook_controller_admin_base', $this->request);
    }

    /**
     * 初始化后台模块信息
     */
    final private function initAdminInfo()
    {

        //检测权限
        list($jump_type, $message) = $this->logicAdminLogic->authCheck($this->noNeedLogin, $this->noNeedRight);

        // 权限验证不通过则跳转提示
        1 == $jump_type ? $this->jump($jump_type, $message, url('login/index')) : '';

        // 初始化基础数据
        IS_AJAX && !IS_PJAX ?: $this->initBaseInfo();
        // !IS_PJAX && $this->initBaseInfo();

        // 若为PJAX则关闭布局
        IS_PJAX && $this->view->engine->layout(false);

        //设置禁用 布局的过滤器
        $this->filterAction();
    }

    /**
     * 初始化后台模块常量
     */
    final private function initAdminConst()
    {
        // 登录者ID
        defined('MEMBER_ID') or define('MEMBER_ID', $this->logicAuth->isLogin());

        // 是否为超级管理员
        defined('IS_ROOT') or define('IS_ROOT', MEMBER_ID == SYS_ADMINISTRATOR_ID);
    }

    /**
     * 初始化基础数据
     */
    final private function initBaseInfo()
    {

        $this->onLayout(true);

        //获取菜单列表
        $menu = $this->logicAuth->getMenu();

        // 菜单视图
        $this->view->assign('menulist', $menu);
        //获得面包屑导航
        $breadcrumb = $this->showContentHeader ? $this->logicAuth->getBreadCrumb(true) : '';

        // 面包屑导航视图
        $this->view->assign('breadcrumb', $breadcrumb);

        //渲染配置信息
        $this->view->assign('config', config('sys.'));

        //管理员信息
        $this->view->assign('admin', session('admin'));
    }

    /**
     * 获取内容头部视图
     */
    final protected function getContentHeader($describe = '')
    {

        //面包屑导航
        $breadcrumb = $this->showContentHeader ? $this->logicAuth->getBreadCrumb(true) : '';

        //标题头
        $title = empty($this->title) ? '' : $this->title;
        //隐藏头信息
        $hiddenHead = "<input type='hidden' name='xf_title_hidden' id='xf_title_hidden' value='" . $title . "'/>";
        //容器
        $section = '<section class="content container-fluid">';

        return $breadcrumb . $hiddenHead . $section;

    }

    /**
     * 重写fetch方法
     */
    final protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {

        $content                       = parent::fetch($template, $vars, $replace, $config);
        IS_AJAX && IS_PJAX && $content = $this->getContentHeader() . $content;

        echo $content;die;
        return $content;
    }

    /**
     * 获取table插件传递的参数
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-11
     * @param mixed $searchfields     快速查询的字段
     * @param boolean $relationSearch 是否关联查询
     * @return   [type]               [description]
     */
    protected function getTableSearchParam($searchfields = null, $relationSearch = null)
    {
        $searchfields   = is_null($searchfields) ? $this->searchfields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search         = $this->request->get("search", '');
        $filter         = $this->request->get("filter", '');
        $op             = $this->request->get("op", '', 'trim');
        $sort           = $this->request->get("sort", "id");
        $order          = $this->request->get("order", "DESC");
        $offset         = $this->request->get("offset", 0);
        $limit          = $this->request->get("limit", 0);
        $filter         = (array) json_decode($filter, true);
        $op             = (array) json_decode($op, true);
        $filter         = $filter ? $filter : [];
        $where          = [];
        $tableName      = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name      = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => &$item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v   = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                case '!=':
                    $where[] = [$k, $sym, (string) $v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue;
                    }

                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v   = str_replace(' ~ ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue;
                    }

                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } else if ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }

    /**
     * 获取数据限制的管理员ID
     * 禁用数据限制时返回的是null
     * @return mixed
     */
    protected function getDataLimitAdminIds()
    {
        if (!$this->dataLimit) {
            return null;
        }
        if ($this->auth->isSuperAdmin()) {
            return null;
        }
        $adminIds = [];
        if (in_array($this->dataLimit, ['auth', 'personal'])) {
            $adminIds = $this->dataLimit == 'auth' ? $this->auth->getChildrenAdminIds(true) : [$this->auth->id];
        }
        return $adminIds;
    }

    /**
     * 过滤关闭布局
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-12
     * @return   [type]       [description]
     */
    protected function filterAction()
    {
        if(in_array(ACTION_NAME, ['add','edit','del'])){
            $this->view->engine->layout(false);
        }
    }
}
