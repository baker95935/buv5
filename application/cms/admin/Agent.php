<?php

namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\user\model\User as AgentModel;
use think\Db;
use think\Cache;

/**
 * 渠道控制器
 * @package app\cms\admin
 */
class Agent extends Admin
{
    public function index()
    {
        // 查询
        $map = $this->getMap();
        $map['role'] = 2;
        // 排序
        $order = $this->getOrder();
        // 数据列表
        $data_list = AgentModel::where($map)->order($order)->paginate();

        foreach ($data_list as &$item){
            $item['url'] = $this->request->domain().'?c='.$item['id'];
        }

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['nickname' => '渠道名称']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
//                ['id', 'ID'],
                ['username', '用户名'],
                ['nickname', '渠道名', 'text.edit'],
                ['url', '链接'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('add,delete') // 批量添加顶部按钮
            ->addRightButtons(['delete' => ['data-tips' => '删除后无法恢复。']]) // 批量添加右侧按钮
            ->setRowList($data_list) // 设置表格数据
            ->setTableName('admin_user')
            ->addTimeFilter('create_time')
            ->fetch(); // 渲染模板
    }

    public function statistics()
    {
        // 查询
        $map = $this->getMap();
 
        $map['role'] = 2;
        // 排序
        $order = $this->getOrder();
        // 数据列表
//        $data_list = AgentModel::where($map)->order($order)->paginate();

        $map_ = '';
        if (isset($map['create_time'])){
            $map_ = $map['create_time'];
            unset($map['create_time']);
        }

           //设置缓存时间
    	$time=3600;//秒

 
        
 
	        $data_list = Db::name('admin_user')->where($map)
	            ->field('id,nickname')->select();
	        foreach ($data_list as &$item){
	        	if(!empty($map_)) {
					$item['view'] = Db::name('view_log')->where('agent_id',$item['id'])
						->whereTime('create_time','between',$map_[1])->count();
				} else {
					$item['view'] = Db::name('view_log')->where('agent_id',$item['id'])->count();
				}
				
				if(!empty($map_)) {
	            	$item['users'] = Db::name('user')->where('agent_id',$item['id'])
	                ->whereTime('create_time','between',$map_[1])->count();
				} else {
					 $item['users'] = Db::name('user')->where('agent_id',$item['id'])->count();
				}
	            //点击量统计
	            //获取当前用户的手下用户，然后统计之后在加
				if(!empty($map_)) {
		            $item['usersCount'] = Db::name('apply')
		            ->alias('a')
		            ->join('user u','a.uid=u.id','LEFT')
		            ->where('u.agent_id',$item['id'])
		            ->whereTime('a.create_time','between',$map_[1])
		            ->count();
				} else {
					 $item['usersCount'] = Db::name('apply')
		            ->alias('a')
		            ->join('user u','a.uid=u.id','LEFT')
		            ->where('u.agent_id',$item['id'])
		            ->count();
				}
	         
	        }
 
	    	 
 
 
    	if(isset($_GET['_by']) && isset($_GET['_order'])) {
    		$data_list=$this->array_sort($data_list,$_GET['_order'],$_GET['_by']);
    	}

//        $data_list = Db::name('admin_user')
//            ->alias('a')
//            ->join('view_log v',$map_v,'LEFT')
//            ->join('user u',$map_u,'LEFT')
//            ->field('a.id,a.nickname,COUNT(v.id) as view,COUNT(u.id) as users')
//            ->where($map)
//            ->order($order)
//            ->fetchSql(true)
//            ->select();

        // 按钮
        $btn_access = [
            'title' => '导出数据',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',$this->request->param())
        ];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->addOrder('view,users,usersCount') // 添加排序
            ->setSearch(['a.nickname' => '渠道名称']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
//                ['id', 'ID'],
                ['nickname', '渠道名'],
                ['view', '访问次数'],
                ['users', '注册量'],
            	['usersCount','点击量']
            ])->setRowList($data_list) // 设置表格数据
            ->addTimeFilter('create_time')
            ->addTopButton('custom', $btn_access) // 添加授权按钮
            ->fetch(); // 渲染模板
    }

    public function export()
    {
        // 查询
        $map = $this->getMap();
        $map['role'] = 2;
        // 排序
        $order = $this->getOrder();
        // 数据列表
//        $data_list = AgentModel::where($map)->order($order)->paginate();

        $map_ = '';
        if (isset($map['create_time'])){
            $map_ = $map['create_time'];
            unset($map['create_time']);
        }

        $data_list = Db::name('admin_user')->where($map)
            ->field('id,nickname')->select();
        foreach ($data_list as &$item){
        	if(!empty($map_)) {
				$item['view'] = Db::name('view_log')->where('agent_id',$item['id'])
					->whereTime('create_time','between',$map_[1])->count();
			} else {
				$item['view'] = Db::name('view_log')->where('agent_id',$item['id'])->count();
			}
			
			if(!empty($map_)) {
            $item['users'] = Db::name('user')->where('agent_id',$item['id'])
                ->whereTime('create_time','between',$map_[1])->count();
			} else {
				 $item['users'] = Db::name('user')->where('agent_id',$item['id'])->count();
			}
			
           //点击量统计
            //获取当前用户的手下用户，然后统计之后在加
			if(!empty($map_)) {
	            $item['usersCount'] = Db::name('apply')
	            ->alias('a')
	            ->join('user u','a.uid=u.id','LEFT')
	            ->where('u.agent_id',$item['id'])
	            ->whereTime('a.create_time','between',$map_[1])
	            ->count();
			} else {
				 $item['usersCount'] = Db::name('apply')
	            ->alias('a')
	            ->join('user u','a.uid=u.id','LEFT')
	            ->where('u.agent_id',$item['id'])
	            ->count();
			}
        }
 
//        $data_list = Db::name('admin_user')
//            ->alias('a')
//            ->join('view_log v',$map_v,'LEFT')
//            ->join('user u',$map_u,'LEFT')
//            ->field('a.id,a.nickname,COUNT(v.id) as view,COUNT(u.id) as users')
//            ->where($map)
//            ->order($order)
//            ->fetchSql(true)
//            ->select();

        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['nickname', 'auto', '渠道名称'],
            ['view', 'auto', '浏览量'],
            ['users', 'auto', '注册量'],
            ['usersCount','auto','点击量']
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

            $data['role'] = 2;
            $data['status'] = 1;
            $result = $this->validate($data, 'app\user\validate\User');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            if ($agent = AgentModel::create($data)) {
                // 记录行为
                action_log('agent_add', 'cms_agent', $agent['id'], UID, $data['username']);
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'username', '登录用户名', '必填，可由英文字母、数字组成'],
                ['text', 'nickname', '渠道名（昵称）', '可以是中文'],
                ['password', 'password', '密码', '必填，6-20位'],
            ])
            ->fetch();
    }
    
    //渠道排名
    public function topviews()
    {
    	//设置缓存时间
    	$time=3600;//秒

    	 // 查询
    	$views_list=array();
    	$views_list=Cache::get('views_list');
 
    	if(empty($views_list)) {
	    	$views_list = Db::name('view_log')
	    	->field('COUNT(id) AS total,agent_id')
	    	->group('agent_id')
	    	->order('total desc')
	    	->whereTime('create_time', 'today')
	    	//->limit('0,15')
	    	->select();
	
	    	$i=1;
	    	foreach($views_list as $k=>&$v)
	    	{
	    		//$v['users']=Db::name('user')->where('agent_id',$v['agent_id'])->count();
	    		$v['nickname']=Db::name('admin_user')->where('id',$v['agent_id'])->value('nickname');
	    		$v['rank']=$i;
	    		$i++;
	    	}
	    	
	    	Cache::set('views_list',$views_list,$time);
    	}
    	
    	// 使用ZBuilder快速创建数据表格
    	return ZBuilder::make('table')
    	->addColumns([ // 批量添加数据列
    			['rank', '排名'],
    			['nickname', '渠道名'],
    			['total', '访问次数'],
    			//['users', '注册量']
    			])->setRowList($views_list) // 设置表格数据
    		 
    			->fetch(); // 渲染模板
    
    }
    
    public function topusers()
    {
    	$time=3600;//秒
    	
    	$users_list=array();
    	//$users_list=Cache::get('users_list');
    	 
    	if(empty($users_list)) {
    		$users_list = Db::name('user')
    		->field('COUNT(id) AS total,agent_id')
    		->group('agent_id')
    		->whereTime('create_time', 'today')
    		->order('total desc')
    		//->limit('0,15')
    		->select();
    	
    		$j=1;
    		foreach($users_list as $k=>&$v)
    		{
    			//$v['views']=Db::name('view_log')->where('agent_id',$v['agent_id'])->count();
    			$v['nickname']=Db::name('admin_user')->where('id',$v['agent_id'])->value('nickname');
    			$v['rank']=$j;
    			$j++;
    		}
    	
    		Cache::set('users_list',$users_list,$time);
    	}
    	 
    	// 使用ZBuilder快速创建数据表格
    	return ZBuilder::make('table')
    	->addColumns([ // 批量添加数据列
    			['rank', '排名'],
    			['nickname', '渠道名'],
    			['total', '注册量'],
    			//['users', '注册量']
    			])->setRowList($users_list) // 设置表格数据
    		 
    			->fetch(); // 渲染模板
    }
    
 	public function topcounts()
    {
    	$time=3600;//秒
    	
    	$users_list=array();
		Cache::set('users_list','');
    	$users_list=Cache::get('users_list');
    	 
    	if(empty($users_list)) {
    		$users_list = Db::name('user')
    		->field('COUNT(id) AS total,agent_id')
    		->group('agent_id')
    		//->whereTime('create_time', 'today')
    		->order('total desc')
    		//->limit('0,15')
    		->select();
    	
    		$j=1;
    		foreach($users_list as $k=>&$v)
    		{
    	
				
    			$v['nickname']=Db::name('admin_user')->where('id',$v['agent_id'])->value('nickname');
    		 
				$v['usersCount'] = Db::name('apply')
	            ->alias('a')
	            ->join('user u','a.uid=u.id','LEFT')
	            ->where('u.agent_id',$v['agent_id'])
				->whereTime('a.create_time', 'today')
	            ->count();
    			//$v['rank']=$j;
    			$j++;
    		}
    	
    		Cache::set('users_list',$users_list,$time);
    	}
		
		
    	$users_list=$this->array_sort($users_list,'usersCount','DESC');
		
		$j=1;
    	foreach($users_list as $k=>&$v) {
			$v['rank']=$j;
    			$j++;
		}
	 
    	
    	 
    	// 使用ZBuilder快速创建数据表格
    	return ZBuilder::make('table')
    	->addColumns([ // 批量添加数据列
    			['rank', '排名'],
    			['nickname', '渠道名'],
    			['usersCount', '点击量'],
    			//['users', '注册量']
    			])->setRowList($users_list) // 设置表格数据
    		 
    			->fetch(); // 渲染模板
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
        $slider_title = AgentModel::where('id', 'in', $ids)->column('username');
        return parent::setStatus($type, ['agent_'.$type, 'cms_agent', 0, UID, implode('、', $slider_title)]);
    }

    public function quickEdit($record = [])
    {
        $id      = input('post.pk', '');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $slider  = AgentModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $slider . ')，新值：(' . $value . ')';
        return parent::quickEdit(['agent_edit', 'cms_agent', $id, UID, $details]);
    }
    
    public function   array_sort($arr,$keys,$type='asc')
    { 
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
		$keysvalue[$k] = $v[$keys];
		}
		if($type == 'asc'){
		asort($keysvalue);
		}else{
		arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
		$new_array[$k] = $arr[$k];
		}
		return $new_array; 
		} 
}