<?php

namespace app\cms\validate;

use think\Validate;


class Agent extends Validate
{
    // 定义验证规则
    protected $rule = [
        'name|渠道名称' => 'require|length:1,30',
    ];

    // 定义验证场景
    protected $scene = [
        'name' => ['name'],
    ];
}
