<?php
// +---------------------------------------------------------------------
// | 菜单
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace util;

class Menu
{
    protected static $instance;
    //默认配置
    protected $config = [];
    public $options   = [];
    //是否返回 $this
    protected $returnClass = false;
    //触发的菜单
    protected $activeMenu = '';
    //url地址前缀
    protected $urlPrefix = '';

    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    protected $arr           = [];
    protected $pk            = 'id';
    protected $pid           = 'pid';
    protected $weigh         = 'weigh'; //权重
    protected $title         = 'title'; //Tree 展示的作用字段
    protected $showchildicon = false; //子级菜单显示icon小图标
    protected $showNavIcon   = false; //前台nav 一级导航是否显示icon小图标

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    protected $icon = array(' │', ' ├', ' └');

    public function __construct($options = [])
    {
        $this->options = array_merge($this->config, $options);
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Tree
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**

     * 初始化方法

     * @param array 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','pid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','pid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','pid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','pid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','pid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','pid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','pid'=>3,'name'=>'三级栏目二')
     * )
     */
    public function init($arr = [], $pk = 'id', $pid = 'pid', $initTree = true)
    {
        $this->arr        = $arr;
        $pk ? $this->pk   = $pk : '';
        $pid ? $this->pid = $pid : '';

        $this->setReturn(true);

        if ($initTree) {
            //生成树
            $this->getTreeTwo();
        }
        return $this;
    }

    /**
     * 是否返回 $this 配合 getTreeTwo 用
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-16
     * @param    string       $flag [description]
     */
    public function setReturn($flag = false)
    {
        $this->returnClass = $flag ? true : false;
        return $this;
    }

    /**
     * 设置后台菜单是否包含icon 小图标
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-16
     * @param    boolean      $flag [description]
     */
    public function setAdminMenuIcon($flag = false)
    {
        $this->showchildicon = $flag ? true : false;
        return $this;
    }

    /**
     * 设置权重名
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-12
     * @param    boolean      $str [description]
     */
    public function setWeigh($str = '')
    {
        $this->weigh = $str;
        return $this;
    }

    /**
     * 设置tree 作用的字段
     * @Author   ZhaoXianFang
     * @DateTime 2018-12-12
     * @param    string       $name [description]
     */
    public function setTitle($str='title')
    {
        $this->title = $str;
        return $this;
    }

    /**
     * 获取数 TREE
     * @Author   ZhaoXianFang
     * @DateTime 2018-03-14
     * @param    [type]       $arrData    [待处理的数组]
     * @param    [type]       $pid        [父id]
     * @param    [type]       $pk         [自增主键id]
     */
    public function getTreeTwo()
    {
        $arrData = $this->arr;
        if (!$arrData) {
            return array();
        }
        $tree = array();
        //第一步，将分类id作为数组key,并创建children单元
        foreach ($arrData as $arr) {
            $tree[$arr[$this->pk]]              = $arr;
            $tree[$arr[$this->pk]]['childlist'] = array();
        }
        //第二步，利用引用，将每个分类添加到父类children数组中，这样一次遍历即可形成树形结构。
        foreach ($tree as $key => $item) {
            if ($item[$this->pid] != 0) {
                $tree[$item[$this->pid]]['childlist'][] = &$tree[$key]; //注意：此处必须传引用否则结果不对
                if ($tree[$key]['childlist'] == null) {
                    unset($tree[$key]['childlist']); //如果children为空，则删除该children元素（可选）
                }
            }
        }
        $tempArr = $tree; //临时存储 数据，如果 该树 没有根节点，那么就返回 没有根的数据，就不删除了
        //第三步，删除无用的非根节点数据
        foreach ($tree as $key => $t) {
            if (!isset($t[$this->pid])) {
                unset($tree[$key]);
                continue;
            }

            if ($t[$this->pid] != 0) {
                unset($tree[$key]);
            }
        }
        //如果本来有数据，但是又没有 根节点的数据，那就返回无根的数据
        if ($tempArr && !$tree) {
            //清除空数据
            foreach ($tempArr as $key => $t) {
                if (!isset($t[$this->pid]) || empty($t)) {
                    unset($tempArr[$key]);
                    continue;
                }
            }
            $tree = $tempArr;
        }
        if ($this->returnClass) {
            $this->arr = $tree;
            return $this;
        }
        return $tree;
    }

    /**
     * 生成排序后的菜单 每个子菜单紧跟在父菜单后面 权重值大的在前面
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-28
     * @return   [type]       [description]
     */
    public function getTree()
    {
        // dump($this->arr);die;
        if ($this->weigh && $this->arr) {
            $this->arr = $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        $arrList = $this->reduce($this->arr, 0);
        foreach ($arrList as $key => &$value) {

            if (isset($value['childlist'])) {
                unset($value['childlist']);
            }
        }
        return $arrList;
    }

    /**
     * 数组归纳 多维转二维
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-28
     * @param    string       $arrData [description]
     * @param    string       $lv      [级别 跟级为0]
     * @return   [type]                [description]
     */
    private function reduce($arrData = '', $lv = 0)
    {
        $listArr = array();
        if (!$arrData) {
            return array();
        }
        $countArr = count($arrData) - 1;
        foreach ($arrData as $key => $value) {

            if ($lv > 0) {
                if ($key == $countArr) {
                    $value[$this->title] = str_repeat($this->icon['0'], $lv - 1) . $this->icon['2'] . $value[$this->title];
                } else {
                    $value[$this->title] = str_repeat($this->icon['0'], $lv - 1) . $this->icon['1'] . $value[$this->title];
                }
            }
            $listArr[] = $value;
            if (isset($value['childlist']) && $value['childlist']) {
                if ($this->weigh) {
                    $value['childlist'] = $this->my_sort($value['childlist'], $this->weigh, SORT_DESC, SORT_NUMERIC);
                }

                $lv++;
                $childArr = $this->reduce($value['childlist'], $lv);

                // if (isset($value['childlist']['0']['childlist']) || !$this->weigh) {
                $listArr = array_merge($listArr, $childArr);
                // } else {
                //     $listArr = array_merge($listArr, $this->my_sort($childArr, $this->weigh, SORT_DESC));
                // }
                $lv--;

            }
        }
        return $listArr;

    }

    /**
     * 自定义 数组排序
     * @Author   ZhaoXianFang
     * @DateTime 2017-09-11
     * @param    [type]       $arrays     [被排序数组]
     * @param    [type]       $sort_key   [被排序字段]
     * @param    [type]       $sort_order [排序方式]
     * @param    [type]       $sort_type  [排序类型]
     * @return   [type]                   [description]
     */
    private function my_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
    {
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                if (is_array($array)) {
                    $key_arrays[] = $array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        if (empty($key_arrays)) {
            return $arrays;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    public function setActiveMenu($activeMenu = '')
    {
        $this->activeMenu = $activeMenu ? strtolower(str_replace(".", "/", $activeMenu)) : '';
        return $this;
    }

    public function setUrlPrefix($prefixStr = '')
    {
        $this->urlPrefix = $prefixStr;
        return $this;
    }

    /**
     * 创建目录
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @param    [type]       $pk   [description]
     * @param    array        $tArr [description]
     * @param    array        $level [层级]
     * @return   [type]             [description]
     */
    /**
     * [createMenu 创建目录]
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-16
     * @param    [type]       $pk    [顶级pid 一般为0]
     * @param    string       $scope [作用域，admin(后台)、home(前台)]
     * @return   [type]              [description]
     */
    public function createMenu($pk = 0, $scope = 'admin')
    {
        if ($scope == 'admin') {
            //后台菜单
            return $this->adminMenu($pk);
        } else {
            //前台顶部nav导航
            return $this->homeNavMenu($pk);
            // return $this->homeNavMenuTwo($pk);
        }
    }

    /**
     * 创建后台目录
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @param    [type]       $pk   [description]
     * @param    array        $tArr [description]
     * @param    array        $level [层级]
     * @return   [type]             [description]
     */
    public function adminMenu($pk, $tArr = array(), $level = 0)
    {
        $str = '';
        if (!$tArr && $pk < 1) {
            $this->tabLevel = 0;
            $str .= '<li class="header">菜单栏</li>';
            // $tArr = $this->arr;
            if ($this->weigh && $this->arr) {
                $tArr = $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC);
            } else {
                $tArr = $this->arr;
            }
        }
        if (!$tArr) {
            return $str;
        }
        $childs = $this->findChild($pk, $tArr);

        if (!$childs) {
            return $str;
        }
        if ($this->weigh && $childs) {
            $childs = $this->my_sort($childs, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        $level += 1;
        $nbsp = ''; //缩进
        if ($pk > 0) {
            $nbspStr = '&nbsp;&nbsp;';
            $nbsp    = str_repeat($nbspStr, $level);
        }
        foreach ($childs as $key => $value) {
            if (!isset($value[$this->pid])) {
                continue;
            }

            $nextArr = (isset($value['childlist']) && count($value['childlist']) > 0) ? $value['childlist'] : array();

            $href     = url($this->urlPrefix . $value['name']);
            $icon     = $value['icon'];
            $hasSub   = '';
            $hasChild = 0;
            if (isset($value['childlist']) && count($value['childlist']) > 0) {
                //有子菜单
                $href     = 'javascript:;';
                $hasSub   = 'treeview ';
                $hasChild = 1;
            }
            $iconStr = '';
            //是否显示子级icon 图标
            if ($level == 1 || $this->showchildicon) {
                $iconStr = '<i class="' . $icon . '"></i>';
            }
            
            $activeMenu = $this->checkactiveMenu($value['name'], $hasChild);
            $str .= '<li class="' . $hasSub . ' ' . $activeMenu . '"><a menu href="' . $href . '">' . $nbsp . $iconStr . '<span>' . $value[$this->title] . '</span>';
            if ($hasChild) {
                $str .= '<!-- has child begin --><span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span><!-- has child end -->';
            }
            $str .= '</a>';
            if ($hasChild) {
                $str .= '<!-- has child begin --><ul class="treeview-menu">';
                // <!-- has li -->;
                $str .= $this->adminMenu($value[$this->pk], $nextArr, $level);
                $str .= '</ul><!-- has child end -->';
            }
            $str .= '</li>';

        }

        return $str;
    }

    /**
     * 创建前台nav 导航目录 只支持二级导航，不支持三级
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-16
     * @param    [type]       $pk    [description]
     * @param    array        $tArr  [description]
     * @param    integer      $level [description]
     * @return   [type]              [description]
     */
    public function homeNavMenu($pk, $tArr = array(), $level = 0)
    {
        $str = '';
        if (!$tArr && $pk < 1) {
            $this->tabLevel = 0;
            if ($this->weigh && $this->arr) {
                $tArr = $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC);
            } else {
                $tArr = $this->arr;
            }
        }
        if (!$tArr) {
            return $str;
        }
        $childs = $this->findChild($pk, $tArr);

        if (!$childs) {
            return $str;
        }
        if ($this->weigh && $childs) {
            $childs = $this->my_sort($childs, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        $level += 1;
        $nbsp = ''; //缩进
        if ($pk > 0) {
            $nbspStr = '&nbsp;&nbsp;';
            $nbsp    = str_repeat($nbspStr, $level);
        }
        foreach ($childs as $key => $value) {
            if (!isset($value[$this->pid])) {
                continue;
            }

            $nextArr = (isset($value['childlist']) && count($value['childlist']) > 0) ? $value['childlist'] : array();

            $href     = url($this->urlPrefix . $value['name']);
            $icon     = isset($value['icon']) ? $value['icon'] : '';
            $hasSub   = '';
            $hasChild = 0;
            if (isset($value['childlist']) && count($value['childlist']) > 0) {
                //有子菜单
                $href     = 'javascript:;';
                $hasSub   = 'dropdown ';
                $hasChild = 1;
            }
            $iconStr = '';
            //是否显示子级icon 图标
            if (($level == 1 && $this->showNavIcon) || $this->showchildicon) {
                $iconStr = '<i class="' . $icon . '"></i>';
            }

            $activeMenu = $this->checkactiveMenu($value['name'], $hasChild);
            if ($hasChild) {
                $str .= '<li class="' . $hasSub . ' ' . $activeMenu . '"><a menu href="' . $href . '"  class="dropdown-toggle" data-toggle="dropdown">' . $iconStr . $value[$this->title] . '<span class="caret"></span></a>';

                $str .= '<!-- 下拉菜单 --><ul class="dropdown-menu" role="menu">';
                $str .= $this->homeNavMenu($value[$this->pk], $nextArr, $level);
                $str .= '</ul><!-- 下拉菜单 end --></li>';
            } else {
                $str .= '<li class="' . $activeMenu . '"><a menu href="' . $href . '">' . $iconStr . '<span>' . $value[$this->title] . '</span></a></li>';
            }
        }

        return $str;

    }

    /**
     * 自定义扩展
     * 创建前台nav 导航目录 只支持二级导航，不支持三级
     * @Author   ZhaoXianFang
     * @DateTime 2018-07-16
     * @param    [type]       $pk    [description]
     * @param    array        $tArr  [description]
     * @param    integer      $level [description]
     * @return   [type]              [description]
     */
    public function homeNavMenuTwo($pk, $tArr = array(), $level = 0)
    {
        $str = '';
        if (!$tArr && $pk < 1) {
            $this->tabLevel = 0;
            if ($this->weigh && $this->arr) {
                $tArr = $this->my_sort($this->arr, $this->weigh, SORT_DESC, SORT_NUMERIC);
            } else {
                $tArr = $this->arr;
            }
        }
        if (!$tArr) {
            return $str;
        }
        $childs = $this->findChild($pk, $tArr);

        if (!$childs) {
            return $str;
        }
        if ($this->weigh && $childs) {
            $childs = $this->my_sort($childs, $this->weigh, SORT_DESC, SORT_NUMERIC);
        }
        $level += 1;
        $nbsp = ''; //缩进
        if ($pk > 0) {
            $nbspStr = '&nbsp;&nbsp;';
            $nbsp    = str_repeat($nbspStr, $level);
        }
        foreach ($childs as $key => $value) {
            if (!isset($value[$this->pid])) {
                continue;
            }

            $nextArr = (isset($value['childlist']) && count($value['childlist']) > 0) ? $value['childlist'] : array();

            $href     = url($this->urlPrefix . $value['name']);
            $icon     = isset($value['icon']) ? $value['icon'] : '';
            $hasSub   = '';
            $hasChild = 0;
            if (isset($value['childlist']) && count($value['childlist']) > 0) {
                //有子菜单
                $href     = 'javascript:;';
                $hasSub   = ($pk > 0) ? 'dropdown-submenu' : 'dropdown';
                $hasChild = 1;
                $ulClass  = ($pk > 0) ? 'dropdown-menu menu-top' : 'dropdown-menu';
            }
            $iconStr = '';
            //是否显示子级icon 图标
            if (($level == 1 && $this->showNavIcon) || $this->showchildicon) {
                $iconStr = '<i class="' . $icon . '"></i>';
            }

            $activeMenu = $this->checkactiveMenu($value['name'], $hasChild);

            if ($hasChild) {
                $str .= '<li class="' . $hasSub . ' ' . $activeMenu . '"><a menu href="' . $href . '"  class="dropdown-toggle" data-toggle="dropdown">' . $iconStr . $value[$this->title] . '<span class="caret"></span></a>';
                $str .= '<ul class="' . $ulClass . '">';
                $str .= $this->homeNavMenuTwo($value[$this->pk], $nextArr, $level);
                $str .= '</ul>';
            } else {
                $str .= '<li class="' . $hasSub . ' ' . $activeMenu . '"><a menu href="' . $href . '" >' . $iconStr . $value[$this->title] . '</span></a>';
            }
            $str .= '</li>';
        }

        return $str;
    }

    /**
     * 检测是否 激活该菜单
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-30
     * @param    string       $link     [数据库记录的地址]
     * @param    string       $hasChild [有子菜单？]
     * @return   [type]                 [description]
     */
    private function checkactiveMenu($link = '', $hasChild = 0)
    {
        if (!$this->activeMenu || !$link) {
            return '';
        }
        $linkArr    = explode('/', strtolower(trim($link,'/'))); //数据库获取
        $setLinkArr = $this->activeMenu?explode('/', strtolower($this->activeMenu)):[]; //当前控制器与方法
        $activeStr  = '';

        if($linkArr['0'] != request()->module()){
            array_unshift($linkArr,request()->module());
        }

        $flsg = false;
        foreach ($linkArr as $key => $node) {
            if (isset($setLinkArr[$key]) && $node == $setLinkArr[$key]) {
                $flsg = true;
            } else {
                return '';
            }
        }
        if ($flsg || $hasChild) {
            return $hasChild ? 'menu-open active' : 'active';
        }
    }

    /**
     * 查找子数组
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @param    [type]       $pk       [description]
     * @param    array        $listData [description]
     * @return   [type]                 [description]
     */
    protected function findChild($pk, $listData = array())
    {
        $findArr = array();
        foreach ($listData as $value) {
            if ($value['pid'] == $pk) {
                $findArr[] = $value;
                continue;
            }
            if (isset($value['childlist']) && $value['childlist']) {
                $result = $this->findChild($value['rule_id'], $value['childlist']);
                if ($result) {
                    $findArr = array_merge($findArr, $result);
                }
            }
        }
        return $findArr;
    }

    /**
     * 清除数据
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-17
     * @return   [type]       [description]
     */
    public function clear()
    {
        $this->config      = [];
        $this->options     = [];
        $this->returnClass = '';
        $this->arr         = [];
        $this->pk          = 'id';
        $this->pid         = 'pid';
        return $this;
    }

    /**
     * 获取面包屑导航
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-07
     * @param    [type]       $link     [用户访问的链接]
     * @param    [type]       $html     [是否返回html导航]
     * @return   [type]                 [description]
     */
    public function getBreadCrumb($link, $html = false)
    {
        $path = strtolower(str_replace(".", "/", $link));

        $dataArr = $this->arr;

        $newAuthArr = [];
        //先设置数组的键值和名称到新数组
        foreach ($dataArr as $key => $auth) {

            $nameArr = explode('/', $auth['name']);
            if (count($nameArr) < 2) {
                $nameArr['1'] = 'index';
            }

            $auth['name']                 = strtolower(implode("/", $nameArr));
            $newAuthArr[$auth[$this->pk]] = $auth;

        }
        $breadCrumbTitle = $remark = '';
        $crumb           = []; //导航 数组
        foreach ($newAuthArr as $key => $value) {
            if ($value['name'] == $path) {
                $crumb[] = $value;
                $pid     = $value[$this->pid];

                $breadCrumbTitle = $value[$this->title];
                $remark          = $value['remark']; //备注

                while ($pid) {
                    $crumb[] = $newAuthArr[$pid];
                    $pid     = $newAuthArr[$pid][$this->pid];
                }
                break;
            }
        }

        $str = '';
        if ($html) {
            $str .= '<section class="content-header"><h1>';
            $str .= $breadCrumbTitle;
            $str .= '<small>' . $remark . '</small></h1><ol class="breadcrumb">';
        }

        $crumbCount = count($crumb) - 1;
        for ($i = $crumbCount; $i >= 0; $i--) {
            if ($html) {
                if ($i == 0) {
                    $str .= '<li class="active">' . $crumb[$i][$this->title] . '</li>';
                } else {
                    $str .= '<li><a href="' . url($crumb[$i]['name']) . '"><i class="' . $crumb[$i]['icon'] . '"></i> ' . $crumb[$i][$this->title] . '</a></li>';
                }
            } else {
                $str .= $i == $crumbCount ? $crumb[$i][$this->title] : ' > ' . $crumb[$i][$this->title];
            }
        }
        if ($html) {
            $str = !empty($crumb) ? $str . '</ol></section>' : '';
        } else {
            $str = $str ? $str : $path;
        }

        return $str;
    }

    // 获取父级节点
    public function getParentNode($id = '')
    {
        $str     = '';
        $tree    = array();
        $arrData = $this->arr;
        //第一步，将分类id作为数组key
        foreach ($arrData as $arr) {
            $tree[$arr[$this->pk]] = $arr;
        }

        $resultArr = [];
        while (isset($tree[$id])) {
            $resultArr[] = $tree[$id]['name'];
            $id          = $tree[$id][$this->pid];
        }

        $crumbCount = count($resultArr) - 1;
        for ($i = $crumbCount; $i >= 0; $i--) {
            switch ($i) {
                case $crumbCount:
                    $str .= '<li><a href="#"><i class="fa fa-dashboard"></i>' . $resultArr[$i] . '</a></li>';
                    break;
                case 0:
                    $str .= '<li class="active">' . $resultArr[$i] . '</li>';
                    break;
                default:
                    $str .= '<li><a href="#">' . $resultArr[$i] . '</a></li>';
                    break;
            }
        }
        return $str;

    }

}
