<?php
// +---------------------------------------------------------------------
// | 后台登录
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

namespace app\admin\logic;

use app\common\model\Log;
use think\facade\Cookie;
use think\facade\Session;
use think\captcha\Captcha;
/**
 * 登录逻辑
 */
class Login extends AdminLogic
{

    /**
     * 登录处理
     */
    public function loginHandle($username = '', $password = '', $verify = '', $keepLogin = false)
    {

        // 检测验证码
        if( !(new Captcha())->check($verify))
        {
            return [1, '验证码输入错误'];
        }
        $validate_result = $this->validateLogin->scene('admin_login')->check(compact('username', 'password', 'verify'));

        if (!$validate_result) {
            return [1, $this->validateLogin->getError()];
        }

        $map1 = [['email', '=', $username]];
        $map2 = [['mobile', '=', $username]];

        $member = $this->logicUser->whereOr([$map1, $map2])->find();

        if (!empty($member['password']) && data_md5_key($password, $member['salt']) == $member['password']) {

            $this->logicUser->setMemberValue(['id' => $member['id']]);
            
            //查找用户组
            $group_name = $this->logicAuthGroupAccess->getRow(['aga.uid' => $member['id']], 'ag.name,ag.id', 'aga', ['auth_group ag', 'aga.group_id = ag.id']);
            //所属分组
            $member['group_name'] = $group_name['name'];
            $member['group_id']   = $group_name['id'];

            // $auth = ['member_id' => $member['id']];
            
            //记录登录信息
            session($this->sessionPrefix, $member);
            // session('member_auth', $auth);
            // session('member_auth_sign', data_auth_sign($auth));

            Log::setTitle('账号 登录');
            $this->keeplogin($keepLogin);
            return [0, '登录成功', url('index/index')];

        } else {

            $error = empty($member['id']) ? '账号不存在' : '密码输入错误';

            return [1, $error];
        }
    }

    /**
     * 自动登录
     * @return boolean
     */
    public function autologin()
    {
        
        $keeplogin = Cookie::get($this->sessionPrefix . '_keeplogin');
        if (!$keeplogin) {
            return false;
        }
        list($id, $keeptime, $expiretime, $key) = explode('|', $keeplogin);
        if ($id && $keeptime && $expiretime && $key && $expiretime > time()) {
            $admin = User::get($id);
            if (!$admin) {
                return false;
            }
            Log::setTitle('自动登录');
            //token有变更
            // if ($key != md5(md5($id) . md5($keeptime) . md5($expiretime) . $admin->token)) {
            if ($key != md5(md5($id) . md5($keeptime) . md5($expiretime))) {
                return false;
            }
            Session::set($this->sessionPrefix, $admin->toArray());
            //刷新自动登录的时效
            $this->keeplogin(true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 刷新保持登录的Cookie
     *
     * @param   bool|int     $setKeeplogin
     * @return  boolean
     */
    protected function keeplogin($setKeeplogin = false)
    {
        if ($setKeeplogin) {
            $keeptime = is_bool($setKeeplogin)?2592000:(int)$setKeeplogin; //默认一个月
            
            $member_id = session($this->sessionPrefix.'.id');
            $expiretime = time() + $keeptime;
            
            $key  = md5(md5($member_id) . md5($keeptime) . md5($expiretime));
            $data = [$member_id, $keeptime, $expiretime, $key];
            
            Cookie::set($this->sessionPrefix . '_keeplogin', implode('|', $data), ['expire'=>$expiretime]);
            return true;
        }
        return false;
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        Session::delete($this->sessionPrefix);
        Cookie::delete($this->sessionPrefix . '_keeplogin');
        Session::destroy();
        Cookie::clear();
        Log::setTitle('自动登录');
        return [0, '退出成功', url('login/index')];
    }

    /**
     * 清理缓存
     */
    public function clearCache()
    {

        \think\Cache::clear();

        return [RESULT_SUCCESS, '清理成功', url('index/index')];
    }
}
