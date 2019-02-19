<?php

namespace app\cms\admin;

use app\admin\controller\Admin;
use think\Db;

/**
 * 仪表盘控制器
 * @package app\cms\admin
 */
class Index extends Admin
{
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        $this->assign('agent', Db::name('admin_user')->where('role', 2)->count());
        $this->assign('apply', Db::name('apply')->count());
        $this->assign('users', Db::name('user')->count());
        $this->assign('customers', Db::name('customer')->count());
        return $this->fetch(); // 渲染模板
    }
}