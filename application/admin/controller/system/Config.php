<?php
// +---------------------------------------------------------------------
// | 系统配置
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\controller\system;

use app\admin\controller\AdminBase;
use app\facade\Config AS SysConfig;

class Config extends AdminBase
{
    protected $noNeedRight = [];
    protected $noNeedLogin = [];

    /**
     * 权限控制控制
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-05
     * @return   [type]       [description]
     */
    public function initialize()
    {
        parent::initialize();

    }

    public function index()
    {

        $siteList  = [];
        $groupList = SysConfig::getGroupList();
        foreach ($groupList as $k => $v) {
            $siteList[$k]['name']  = $k;
            $siteList[$k]['title'] = $v;
            $siteList[$k]['list']  = [];
        }

        foreach (SysConfig::all() as $k => $v) {
            if (!isset($siteList[$v['group']])) {
                continue;
            }
            $value          = $v->toArray();
            $value['title'] = $value['title'];
            if (in_array($value['type'], ['select', 'selects', 'checkbox', 'radio'])) {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array') {
                $value['value'] = (array) json_decode($value['value'], true);
            }
            $value['content']                = json_decode($value['content'], true);
            $siteList[$v['group']]['list'][] = $value;
        }
        $index = 0;
        foreach ($siteList as $k => &$v) {
            $v['active'] = !$index ? true : false;
            $index++;
        }
        $this->view->assign('siteList', $siteList);
        $this->view->assign('typeList', SysConfig::getTypeList());
        $this->view->assign('groupList', SysConfig::getGroupList());
        return $this->fetch();
    }

    public function edit()
    {

        if ($this->request->isPost()) {
            $row = $this->request->post("row/a");
            if ($row) {
                $configList = [];
                foreach (SysConfig::all() as $v) {
                    if (isset($row[$v['name']])) {
                        $value = $row[$v['name']];
                        if (is_array($value) && isset($value['field'])) {
                            $value = json_encode(SysConfig::getArrayData($value), JSON_UNESCAPED_UNICODE);
                        } else {
                            $value = is_array($value) ? implode(',', $value) : $value;
                        }
                        $v['value']   = $value;
                        $configList[] = $v->toArray();
                    }
                }
                SysConfig::allowField(true)->saveAll($configList);
                try
                {
                    $this->refreshFile();
                    $this->jump(0);
                } catch (Exception $e) {
                    $this->jump([1,$e->getMessage()]);
                }
            }
            $this->jump([1,'参数不能为空']);
        }
    }

    protected function refreshFile()
    {
        $config = [];
        foreach (SysConfig::all() as $k => $v)
        {

            $value = $v->toArray();
            if (in_array($value['type'], ['selects', 'checkbox', 'images', 'files']))
            {
                $value['value'] = explode(',', $value['value']);
            }
            if ($value['type'] == 'array')
            {
                $value['value'] = (array) json_decode($value['value'], TRUE);
            }
            $config[$value['name']] = $value['value'];
        }
        file_put_contents(ROOT_PATH . 'config' . DS . 'site.php', '<?php' . "\n\nreturn " . var_export($config, true) . ";");
    }

}
