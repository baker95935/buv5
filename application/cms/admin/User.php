<?php

namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\User as UserModel;
use think\Db;


/**
 * 用户控制器
 * @package app\cms\admin
 */
class User extends Admin
{
    public function index()
    {
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder();

        $role = session('user_auth.role');
        if ($role == '2'){
            $map['u.agent_id'] = UID;
        }
        if (isset($map['create_time'])){
            $map['u.create_time'] = $map['create_time'];
            unset($map['create_time']);
        }
		empty($order) && $order='u.create_time asc';
        // 数据列表
        $data_list = Db::name('user')
            ->alias('u')
            ->join('admin_user a','u.agent_id = a.id','LEFT')
            ->field('u.id,u.mobile,u.avatar,u.name,u.age,u.ant,u.create_time,u.last_login_ip,a.nickname as agent_name')
            ->where($map)->order($order)->paginate();

        // 按钮
        $btn_access = [
            'title' => '导出数据',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',$this->request->param())
        ];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['u.mobile'=>'手机号','u.name' => '姓名','a.nickname'=>'渠道名称']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
//                ['id', 'ID'],
                ['mobile', '手机号'],
                ['avatar', '头像', 'picture'],
                ['name', '姓名'],
                ['age', '年龄'],
                ['ant', '芝麻分'],
                ['agent_name', '渠道'],
                ['create_time', '注册时间', 'datetime'],
                ['last_login_ip', '最后登录IP'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('add,delete') // 批量添加顶部按钮
            ->addRightButtons(['edit','delete' => ['data-tips' => '删除后无法恢复。']]) // 批量添加右侧按钮
            ->setRowList($data_list) // 设置表格数据
            ->addValidate('User', 'name,url')
            ->setTableName('user')
            ->addTimeFilter('create_time')
            ->addTopButton('custom', $btn_access) // 添加授权按钮
            ->fetch(); // 渲染模板
    }

    public function export()
    {
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder();

        $role = session('user_auth.role');
        if ($role == '2'){
            $map['u.agent_id'] = UID;
        }
        if (isset($map['create_time'])){
            $map['u.create_time'] = $map['create_time'];
            unset($map['create_time']);
        }
        // 数据列表
        $data_list = Db::name('user')
            ->alias('u')
            ->join('admin_user a','u.agent_id = a.id','LEFT')
            ->field('u.id,u.mobile,u.avatar,u.name,u.age,u.ant,u.create_time,u.last_login_ip,a.nickname as agent_name')
            ->where($map)->order($order)->paginate();

        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['mobile', 'auto', '手机号'],
            ['avatar', 'auto', '头像', 'picture'],
            ['name', 'auto', '姓名'],
            ['age', 'auto', '年龄'],
            ['ant', 'auto', '芝麻分'],
            ['agent_name', 'auto', '渠道'],
            ['create_time', 'auto', '注册时间', 'datetime'],
            ['last_login_ip', 'auto', '最后登录IP']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', [date('Y-m-d H:i:s'), $cellName, $data_list]);
    }


    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'User');
            if(true !== $result) $this->error($result);

            if ($user = UserModel::create($data)) {
                // 记录行为
                action_log('user_add', 'cms_user', $user['id'], UID, $data['name']);
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '名称'],
            ])
            ->fetch();
    }
    
  public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();
			
            if($data['agent_name']) {
            	//校验下是否存在
            	 $tmpInfo = Db::name('admin_user')->where("nickname='".$data['agent_name']."'")->find();
            	 if(empty($tmpInfo)) {
            	 	$this->error('渠道不存在，请重试');
            	 } else {
            	 	$data['agent_id']=$tmpInfo['id'];
            	 }
            } else {
            	$this->error('请填写渠道');
            }

            if (UserModel::update($data)) {
                // 记录行为
                action_log('user_edit', 'cms_user', $id, UID, $data['mobile']);
                $this->success('编辑成功', 'index');
            } else {
                $this->error('编辑失败');
            }
        }

        $info = UserModel::get($id);
        //如果有代理商ID 那么获取
        $info['agent_name']='';
        if(!empty($info['agent_id'])) {
        	$tmp=Db::name('admin_user')->find($info['agent_id']);
        	$info['agent_name']=$tmp['nickname'];
        }
    
        // 显示编辑页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'mobile', '手机'],
                ['text', 'agent_name', '渠道'],
   
            ])
            ->setFormData($info)
            ->fetch();
    }

    public function delete($record = [])
    {
        return $this->setStatus('delete');
    }

    public function enable($record = [])
    {
        return $this->setStatus('enable');
    }

    public function disable($record = [])
    {
        return $this->setStatus('disable');
    }

    public function setStatus($type = '', $record = [])
    {
        $ids          = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $slider_title = UserModel::where('id', 'in', $ids)->column('name');
        return parent::setStatus($type, ['user_'.$type, 'cms_user', 0, UID, implode('、', $slider_title)]);
    }

    public function quickEdit($record = [])
    {
        $id      = input('post.pk', '');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $slider  = UserModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $slider . ')，新值：(' . $value . ')';
        return parent::quickEdit(['user_edit', 'cms_user', $id, UID, $details]);
    }
}