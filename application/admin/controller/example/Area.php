<?php
namespace app\admin\controller\example;

use app\admin\controller\AdminBase;

class Area extends AdminBase
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
        //获取权限列表
        return $this->fetch();
    }

    /**
     * 搜索
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-11
     * @return   [type]       [description]
     */
    public function search()
    {
        $lv       = $this->request->param('lv', 1);
        $province = $this->request->param('province', 0, "intval");
        $city     = $this->request->param('city', 0, "intval");
        $counties = $this->request->param('counties', 0, "intval");
        $town     = $this->request->param('town', 0, "intval");
        $village  = $this->request->param('village', 0, "intval");

        $where = ['level' => $lv];
        if ($province) {
            $where['parent_code_id'] = $province;
        }
        if ($city) {
            $where['parent_code_id'] = $city;
        }
        if ($counties) {
            $where['parent_code_id'] = $counties;
        }
        if ($town) {
            $where['parent_code_id'] = $town;
        }
        if ($village) {
            $where['parent_code_id'] = $village;
        }

        $json = $this->logicPosition->getListData($where);
        // return  $this->success('', null, $json);
        return json($json);

    }

}
