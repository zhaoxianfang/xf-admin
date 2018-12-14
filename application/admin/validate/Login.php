<?php

namespace app\admin\validate;

/**
 * 登录验证器
 */
class Login extends AdminValidate
{

    // 验证规则
    protected $rule = [
        'username' => 'require',
        'password' => 'require',
        'verify'   => 'require|captcha',
    ];

    // 验证提示
    protected $message = [

        'username.require' => '用户名不能为空',
        'password.require' => '密码不能为空',
        'verify.require'   => '验证码不能为空',
        'verify.captcha'   => '验证码不正确',
    ];

    // 自定义验证规则
    // 验证数据,验证规则,全部数据（数组）,字段名,字段描述
    protected function checkPassword($value, $rule, $data = [])
    {
        return $value;
        return $rule == $value ? true : '名称错误';
    }

    // 应用场景
    protected $scene = [

        'admin'       => ['username', 'password', 'verify'],
        'admin_login' => ['username', 'password'],
    ];
}
