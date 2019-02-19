<?php

namespace app\index\controller;

use think\Db;

/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index extends Home
{
    public function index()
    {
        // 默认跳转模块
        if (config('home_default_module') != 'index') {
            $this->redirect(config('home_default_module'). '/index/index');
        }
        $agent = $this->request->param('c');
        if ($agent){
            session('agent_id',$agent);
        }
        if (!$agent && defined('UID')){
            $agent = Db::name('user')
                ->where('id',UID)
                ->value('agent_id');
        }
        if ($agent){
            // 总浏览量统计
            Db::name('admin_user')
                ->where('id',$agent)->setInc('view');
            // 浏览日志
            Db::name('view_log')
                ->insert(['agent_id'=>$agent,'create_time'=>time(),'ip'=>$this->request->ip()]);
        }
        $this->assign('slider', $this->getSlider());
        $this->assign('customer', $this->getCustomer());
        return $this->fetch();
    }

    public function supermarket()
    {
        $new = Db::name('customer')
            ->where(['status'=>1,'new'=>1])
            ->order(['sort'=>'asc','id'=>'desc'])
            ->select();
        $high = Db::name('customer')
            ->where(['status'=>1,'high_pass'=>1])
            ->order(['sort'=>'asc','id'=>'desc'])
            ->select();
        $this->assign('new', $new);
        $this->assign('high', $high);
        return $this->fetch();
    }

    public function click()
    {
        if (!defined('UID')){
            $this->error('unlogin');
        }
        $cid = $this->request->param('cid');
        $data = [
            'uid'   =>  UID,
            'cid'   =>  $cid,
            'create_time'   =>  $this->request->time(),
            'ip'    =>  $this->request->ip()
        ];
		$count=Db::name('apply')->where(['uid'=>UID,'cid'=>$cid])->count();
		if($count==0){
			Db::name('apply')->insert($data);
		}
        $redirect = Db::name('customer')->where('id',$cid)->value('url');
        $this->success('成功',$redirect);
    }

    private function getSlider()
    {
        return Db::name('cms_slider')->where('status', 1)->select();
    }

    private function getCustomer()
    {
        return Db::name('customer')
            ->where(['status'=>1,'top'=>1])
            ->order(['sort'=>'asc','id'=>'desc'])
            ->select();
    }
}
