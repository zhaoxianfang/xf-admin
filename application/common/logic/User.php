<?php
// +---------------------------------------------------------------------+
// | 爬虫管理    | [ 昆明信息港 ]  [用户逻辑层]                             |
// +---------------------------------------------------------------------+
// | Licensed   | http://www.apache.org/licenses/LICENSE-2.0 )           |
// +---------------------------------------------------------------------+
// | Author     | ZhaoXianFang <1748331509@qq.com>                       |
// +---------------------------------------------------------------------+
// | 版权       | http://www.kunming.cn                                  |
// +---------------------------------------------------------------------+

namespace app\common\logic;

use app\common\model\Log;
use util\Random;

/**
 * 系统通用逻辑模型
 */
class User extends Base
{
    // 姓别转义 各个平台 返回的值不一样
    protected $genderArr = array(
        //微信
        1=>1,
        0=>0,
        //
        '男' => 1,
        '女' => 0,
        //
        'm' => 1,
        'w' => 0
    );
    
    //记录各个登录模块存储 session 使用的名称
    protected $loginModel = array(
        'index' => 'home', //前台登录
        'admin' => 'admin', //后台登录
    );

    /**
     * 设置登录人信息
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-13
     * @param    [type]       $where [description]
     * @param    array        $data  [description]
     */
    public function setMemberValue($where, $data = [])
    {
        if (!isset($where['id'])) {
            $where['id'] = session();
        }
        $data['last_login_time'] = time();
        $data['last_login_ip']   = request()->ip();
        return $this->updateInfo($where, $data);
    }

    /**
     * 获取用户列表
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-13
     * @return   [type]       [description]
     */
    public function getListData()
    {
        $join = [['auth_group_access aga', 'aga.uid = u.id'], ['auth_group ag', 'ag.id = aga.group_id']];

        // 每页显示10条数据
        $list = $this->modelUser->pageList(10, [], '', 'u.*,ag.name as group_name', 'u', $join);

        return $list;
    }

    /**
     * 获取用户信息 [关联查询 标识符为 u]
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $where [description]
     * @return   [type]              [description]
     */
    public function getUserInfo($where = [])
    {
        $join = [['auth_group_access aga', 'aga.uid = u.id'], ['auth_group ag', 'ag.id = aga.group_id']];
        return $this->getRow($where, 'u.*,ag.name as group_name,ag.id as group_id', 'u', $join);
    }

    public function updataUser($data=[])
    {
        $groupId = '';
        if(isset($data["group_id"])) {
            $groupId = $data["group_id"];
            unset($data["group_id"]);
        }
        if( ( isset($data["password"]) && empty($data["password"]) ) || !$data["password"]) {
            unset($data["password"]);
        }else{
            //生成密码
            $data['salt']     = Random::nozero(6);
            $data['password'] = setPwd($data['password'], $data['salt']);
        }

        try{
            //更新用户信息
            $this->modelUser->updateRow($data);
            //更新用户组信息
            if($groupId){
                $this->logicAuthGroupAccess->updateRow(['uid' => $data["id"], 'group_id' => $groupId]);
            }
            return [10,'更新成功'];
        } catch (\Exception $e) {
            $errInfo = $e->getMessage();
            if($errInfo == 'miss update condition'){
                return [10,'更新成功'];
            }
            return [11,$errInfo];
        }
    }

    /**
     * 删除用户信息
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-14
     * @param    array        $where [description]
     * @return   [type]              [description]
     */
    public function delUserInfo($where=[])
    {
        try{
            $this->modelUser->delRow($where);
            if(isset($where['id'])){
                $this->logicAuthGroupAccess->delRow(['uid'=>$where['id'] ]);
            }
            return [0,'删除成功'];
        } catch (\Exception $e) {
            return [1,$e->getMessage()];
        }
    }


    /**
     * 快速登录（第三方）
     * @Author   ZhaoXianFang
     * @DateTime 2018-05-31
     * @param    string       $openId       [第三方回调openId]
     * @param    array        $data         [回调的用户信息]
     * @param    string       $type         [回调类型 qq,sina,wechat]
     * @param    string       $loginModel   [登录作用的模块、不同模块session名称不同]
     * @return   [type]                     [description]
     */
    public function fastLogin($openId = '', $data = array(), $type = 'qq', $loginModel = 'index')
    {

        switch ($type) {
            case 'qq':
                $userInfo = $this->qqLogin($openId, $data);
                break;
            case 'sina':
                $userInfo = $this->sinaLogin($openId, $data);
                break;
            case 'weixin':
                $userInfo = $this->weixinLogin($openId, $data);
                break;

            default:
                return $this->error("未知登录类型");

                break;
        }
        //最后登录登录时间
        $userInfo['last_login_time'] = time();

        //查找用户组
        $group_name = $this->logicAuthGroupAccess->getRow(['aga.uid' => $userInfo['id']], 'ag.name,ag.id', 'aga', ['auth_group ag', 'aga.group_id = ag.id']);
        //所属分组
        $userInfo['group_name'] = $group_name['name'];
        $userInfo['group_id']   = $group_name['id'];

        // $auth = ['member_id' => $userInfo['id']];
        
        //设置 登录信息
        $this->setLogin($userInfo, $loginModel);
        return $userInfo;
    }

    /**
     * QQ 登录 第一次为注册
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-01
     * @param    string       $openId [description]
     * @param    array        $qqData [description]
     * @return   [type]               [description]
     */
    public function qqLogin($openId = '', $qqData = array())
    {
        if (!$openId) {
            return false;
        }
        Log::setPara('1', $qqData['nickname']);
        $checkQqMember = $this->modelUser::getByUuid($openId);
        $gender        = isset($this->genderArr[$qqData['gender']]) ? $this->genderArr[$qqData['gender']] : 2;
        if ($checkQqMember) {
            //更新登录
            Log::setTitle('QQ 登录');
            Log::setContent('QQ 登录');

            $checkQqMember->last_login_time = time();
            $checkQqMember->last_login_ip   = request()->ip();
            $checkQqMember->save();
        } else {
            //注册
            Log::setTitle('QQ 注册');
            Log::setContent('QQ 注册');
            $data = array(
                'uuid'          => $openId,
                'nickname'      => $qqData['nickname'],
                'avatar'        => $qqData['figureurl_qq_2'],
                'gender'        => $gender,
                'province'      => $qqData['province'],
                'city'          => $qqData['city'],
                'create_time'   => time(),
                'last_login_ip' => request()->ip(),
                'register_type' => 'qq',
            );
            $data['id']    = $this->modelUser->insertGetId($data);
            $checkQqMember = $data;
        }
        //检测分组
        $this->checkAuthGroup($checkQqMember['id']);
        return $checkQqMember;

    }

    /**
     * 微博登录
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $openId   [微博uid]
     * @param    array        $sinaData [返回数据]
     * @return   [type]                 [description]
     */
    public function sinaLogin($openId = '', $sinaData = array())
    {
        if (!$openId) {
            return false;
        }
        Log::setPara('1', $sinaData['name']);
        $checkSinaMember = $this->modelUser::getByUuid($openId);
        //性别
        $gender = isset($this->genderArr[$sinaData['gender']]) ? $this->genderArr[$sinaData['gender']] : 2;
        //地区
        $province = $city = '';
        if ($sinaData['location']) {
            $location = explode(' ', $sinaData['location']);
            $province = $location['0'];
            $city     = $location['1'];
        }
        if ($checkSinaMember) {
            //更新登录
            Log::setTitle('微博 登录');
            Log::setContent('微博 登录');

            $checkSinaMember->last_login_time = time();
            $checkSinaMember->last_login_ip   = request()->ip();
            $checkSinaMember->save();
        } else {
            //注册
            Log::setTitle('微博 注册');
            Log::setContent('微博 注册');
            $data = array(
                'uuid'          => $openId,
                'nickname'      => $sinaData['name'],
                'avatar'        => $sinaData['avatar_large'],
                'gender'        => $gender,
                'province'      => $province,
                'city'          => $city,
                'create_time'   => time(),
                'last_login_ip' => request()->ip(),
                'register_type' => 'sina',
            );
            $data['id']      = $this->modelUser->insertGetId($data);
            $checkSinaMember = $data;
        }
        //检测分组
        $this->checkAuthGroup($checkSinaMember['id']);
        return $checkSinaMember;

    }

    //微信登录
    public function weixinLogin($openId = '', $weixinData = array())
    {
        if (!$openId) {
            return false;
        }
        
        Log::setPara('1', $weixinData['nickname']);
        $checkWechatMember = $this->modelUser::getByUuid($openId);
        $gender        = isset($this->genderArr[$weixinData['sex']]) ? $this->genderArr[$weixinData['sex']] : 2;
        if ($checkWechatMember) {
            //更新登录
            Log::setTitle('微信 登录');
            Log::setContent('微信 登录');

            $checkWechatMember->last_login_time = time();
            $checkWechatMember->last_login_ip   = request()->ip();
            $checkWechatMember->save();
        } else {
            //注册
            Log::setTitle('微信 注册');
            Log::setContent('微信 注册');
            $data = array(
                'uuid'          => $openId,
                'nickname'      => $weixinData['nickname'],
                'avatar'        => $weixinData['headimgurl'],
                'gender'        => $gender,
                'province'      => $weixinData['province'],
                'city'          => $weixinData['city'],
                'create_time'   => time(),
                'last_login_ip' => request()->ip(),
                'register_type' => 'weixin',
            );
            $data['id']    = $this->modelUser->insertGetId($data);
            $checkWechatMember = $data;
        }
        //检测分组
        $this->checkAuthGroup($checkWechatMember['id']);
        return $checkWechatMember;
    }

    //检测用户 是否分组
    public function checkAuthGroup($user_id = '')
    {
        if(!$user_id){
            return false;
        }
        
        $checkResult = $this->logicAuthGroupAccess->getRow(['uid'=>$user_id]);

        if(!$checkResult){
            //没有分组的分配到默认分组
            $default_group_id = config('sys.default_user_group');
            $this->logicAuthGroupAccess->addRow(['uid'=>$user_id,'group_id'=>$default_group_id]);
        }
    }

    /**
     * 设置登录状态
     * @Author   ZhaoXianFang
     * @DateTime 2018-06-06
     * @param    string       $userInfo   [用户信息]
     * @param    string       $loginModel [设置登录的模板 home,admin,……]
     */
    public function setLogin($userInfo = '', $loginModel = 'index')
    {

        //存储用户登录信息
        session($this->loginModel[$loginModel], $userInfo);
        #TODO
    }

}
