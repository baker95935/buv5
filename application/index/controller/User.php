<?php
/**
 * Created by PhpStorm.
 * User: Rafi
 * Date: 2018/8/9
 * Time: 23:43
 */

namespace app\index\controller;

use app\index\model\User as UserModel;
use think\Db;

class User extends Home
{
    protected $beforeActionList = [
        'isLogin' =>  ['except'=>'login,logout,register,forget'],
    ];

    protected function isLogin(){
        if(!defined('UID')){
            $this->redirect('index/user/login');
        }
    }

    public function index()
    {
        $user_info = UserModel::get(UID);
       
        
        if(empty($user_info['firstlimit'])) {
        	$data=array();
        	$user_info['firstlimit']=$data['firstlimit']=rand(50,500)*100;
        	$user_info->save($data);
        }
        $this->assign('user',$user_info);
        
        //商品随机选择6个
        $info=Db::name('customer')
        ->where(['status'=>1,'top'=>1])
        ->order(['sort'=>'asc','id'=>'desc'])
        ->limit(6)->select();
        $realinfo='请申请  ';
        foreach($info as $k=>$v) {
        	$realinfo.=$v['name'].' ';
        }
        $realinfo.=' 即可得此额度';
        
        
        $this->assign('realinfo',$realinfo);
        return $this->fetch();
    }

    public function register()
    {
        if ($this->request->isAjax()){
            $info = input();
            $check = $this->validate($info,'User');
            if (true !== $check) {
                $this->error($check);
            }
            if ($info['smsVerify'] != cache('verify_'.$info['mobile'])){
                $this->error('验证码错误');
            }
            cache('verify_'.$info['mobile'],null);
            unset($info['smsVerify']);
            $info['password'] = password_hash($info['password'],1);
            $agent = session('agent_id');
            if ($agent){
                $info['agent_id'] = $agent;
                Db::name('admin_user')
                    ->where('id',$agent)->setInc('customers');
            }
            $info['signup_ip'] = $this->request->ip();
            $info['firstlimit']=rand(50,500)*100;
            $user_db = new UserModel;
            if ($user_db->save($info) !== false){
                $this->autoLogin($user_db->id);
                $this->success();
            }
            $this->error('注册失败');
        }
        return $this->fetch();
    }

    public function login()
    {
        if ($this->request->isAjax()){
            $mobile = input('mobile');
            $pass = input('password');
            $user = Db::name('user')->where('mobile',$mobile)
                ->find();
            if (!password_verify($pass,$user['password'])){
                $this->error('用户名或密码错误');
            }
            $this->autoLogin($user['id']);
            $this->success();
        }
        return $this->fetch();
    }

    public function forget()
    {
        if ($this->request->isAjax()){
            $info = input();
            if ($info['smsVerify'] != cache('verify_'.$info['mobile'])){
                $this->error('验证码错误');
            }
            cache('verify_'.$info['mobile'],null);
            $user = UserModel::get(['mobile'=>$info['mobile']]);
            $update_data = [
                'password'=>password_hash($info['password'],1)
            ];
            if ($user->save($update_data) !== false){
                $this->success();
            }
            $this->error('重置失败');
        }
        return $this->fetch();
    }

    public function logout()
    {
        session('uid',null);
        $this->redirect($this->request->domain());
    }

    public function autoLogin($uid)
    {
        session('uid',$uid);
        UserModel::update(['last_login_ip'=>get_client_ip()],['id'=>$uid]);
    }

    public function kefu()
    {
        return $this->fetch();
    }

    public function co()
    {
        return $this->fetch();
    }

    public function about()
    {
        return $this->fetch();
    }

    public function set()
    {
        return $this->fetch();
    }

}