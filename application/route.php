<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

    'login'         =>  'index/user/login',
    'logout'        =>  'index/user/logout',
    'register'      =>  'index/user/register',
    'user'          =>  'index/user/index',
    'user/set'      =>  'index/user/set',
    'about'         =>  'index/user/about',
    'co'            =>  'index/user/co',
    'kefu'          =>  'index/user/kefu',
    'forget'        =>  'index/user/forget',
    'supermarket'   =>  'index/index/supermarket'

];
