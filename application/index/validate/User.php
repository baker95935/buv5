<?php

namespace app\index\validate;


use think\Validate;

class User extends Validate
{
    //定义验证规则
    protected $rule = [
        'mobile|手机号'   => 'require|regex:^1\d{10}|unique:user',
        'smsVerify|验证码'   => 'require',
        'password|密码'   => 'require',
        'name|姓名'   => 'require',
        'age|年龄'   => 'require',
        'ant|芝麻分'   => 'require',
    ];

    //定义验证提示
    protected $message = [
        'mobile.regex'    => '手机号格式不符',
        'mobile.unique'    => '该手机号已注册',
    ];

    //定义验证场景
    protected $scene = [
        //手机号验证
        'mobile' => ['mobile'],
    ];
}