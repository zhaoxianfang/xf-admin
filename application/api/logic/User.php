<?php
// +----------------------------------------------------------------------
// | 用户逻辑
// +----------------------------------------------------------------------
// | @copyright (c) www.kunming.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: wanghuaili <576106898@qq.com>
// +----------------------------------------------------------------------
// | Date: 2018-08-27
// +----------------------------------------------------------------------

namespace app\api\logic;

use Firebase\JWT\JWT;
use think\Config;

/**
 * 用户
 */
class User extends ApiLogic
{

    /**
     * 获取用户基本信息
     * @DateTime 2018-08-27
     * @param    [int]    $user_id  用户ID
     * @return   [array]  $info     用户信息
     */
    public function getUserInfo($user_id = 0)
    {
        $map['status']  = 1;
        $map['user_id'] = $user_id;

        $field = 'user_id, username, nickname, mobile, email, avatar';

        $info = $this->modelUser
            ->field($field)
            ->where($map)
            ->find()
        ;

        return $info;
    }

    /**
     * 用户登陆
     * @DateTime 2018-08-27
     * @param    [array]  $data     用户ID
     * @return   [array]  $info     用户信息
     */
    public function login($data = [])
    {
        // 获取验证用户名及密码
        $username  = $data['username'];
        $password  = $data['password'];

        // 验证用户名
        $map_user['username'] = $username;
        $info = $this->modelUser->where($map_user)->find();

        if (empty($info)) {
            return $this->apiError(['code' => 20001, 'msg' => '用户名错误']);
        }

        // 验证密码
        if ($info['password'] != md5(md5($password) . $info['salt'])) {
            return $this->apiError(['code' => 20002, 'msg' => '密码错误']);
        }

        // 登陆成功
        $token = $this->generateToken($info);

        // 验证通过储存用户微信信息
        if (isset($data['wx_nickname'])) {
            $data_py['wx_nickname'] = $data['wx_nickname'];
        }
        if (isset($data['wx_avatar'])) {
            $data_py['wx_avatar']   = $data['wx_avatar'];
        }
        
        // 加入token
        $data_py['token'] = $token;
        $data_py['user_id'] = $info['user_id'];

        // 存储相关信息
        $map_py['user_id'] = $info['user_id'];
        $info_py = $this->modelWepy->where($map_py)->find();

        try {

            if (empty($info_py)) {
                $result = $this->modelWepy->insert($data_py);
            } else {
                $result = $this->modelWepy->where($map_py)->update($data_py);
            }
            
        } catch (Exception $e) {
            return $this->apiError(['code' => 20005, 'msg' => '用户微信信息写入失败']);
        }
        

        return $token;
    }


    /**
     * 获取ACCESS_TOKEN
     * 
     * @param array $user_info 当前用户信息
     * @param int   $time_out  有效时间
     *
     * @return string 
     */
    public static function generateToken($user_info = [])
    {
        // 非必须。issuer 请求实体，可以是发起请求的用户的信息，也可是jwt的签发者。
        $token['iss'] = request()->domain();
        // 非必须。issued at。 token创建时间，unix时间戳格式
        $token['iat'] = $_SERVER['REQUEST_TIME'];
        // 非必须。expire 指定token的生命周期。unix时间戳格式
        $token['exp'] = $_SERVER['REQUEST_TIME'] + 3600*24;
        // 非必须。not before。如果当前时间在nbf里的时间之前，则Token不被接受；一般都会留一些余地，比如几分钟。
        $token['nbf'] = $_SERVER['REQUEST_TIME'];
        // 用户信息 
        $token['data']['user_id']   = $user_info['user_id'];
        $token['data']['username']  = $user_info['username'];

        try {
            // 获取jwtkey
            $jwtkey = Config::get('wxapi.jwt_key') ? : 'kmxxg';

            $jwt = JWT::encode($token, $jwtkey);
        } catch (\Exception $e) {
            return $this->apiError(['code' => 20004, 'msg' => '获取TOKEN失败']);
        }
        return $jwt;
    }

    /**
     * [logout 退出]
     * @Author   wanghuaili
     * @DateTime 2018-09-04
     * @return   [type]       [description]
     */
    public function logout($user_id = 0)
    {
        $map['user_id'] = $user_id;
        $info = $this->modelUser->where($map)->field('user_id, username')->find();
        
        if (empty($info)) {
            return $this->apiError(['code' => 20006, 'msg' => '退出失败']);
        }
        
        // 退出将token字段置为空
        $res = $this->modelWepy->where($map)->update(['token' => '']);

        return $info;   
    }
}