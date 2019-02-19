<?php

namespace app\cms\validate;

use think\Validate;


class Customer extends Validate
{
    // 定义验证规则
    protected $rule = [
        'name|名称' => 'require|length:1,30',
        'logo|图片' => 'require',
        'url|链接'   => 'require|url',
    ];

    // 定义验证场景
    protected $scene = [
        'name' => ['name'],
        'url'   => ['url'],
    ];
}
