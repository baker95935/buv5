<?php

namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;

use app\cms\model\CustomerData as CustomerDataModel;
use think\Db;


class Apply extends Admin
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
        empty($order) && $order='a.id asc';
        // 数据列表
        $data_list = Db::name('apply')
            ->alias('a')
            ->join('customer c','a.cid = c.id','LEFT')
            ->join('user u','a.uid = u.id','LEFT')
            ->join('admin_user au','u.agent_id = au.id','LEFT')
            ->where($map)
            ->field('a.id,u.mobile,au.nickname,c.name,a.create_time,a.ip')
            ->order($order)
            ->group('a.uid,a.cid')
            ->paginate();
 
        // 按钮
        $btn_access = [
            'title' => '导出数据',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('export',$this->request->param())
        ];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['au.nickname' => '渠道名称','c.name'=>'产品名','u.mobile'=>'手机号']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
//                ['id', 'ID'],
                ['mobile', '申请人手机号'],
                ['nickname', '渠道名'],
                ['name', '产品名'],
                ['create_time', '申请时间', 'datetime'],
                ['ip', '申请IP']
            ])
            ->setRowList($data_list) // 设置表格数据
            ->hideCheckbox()
            ->addTimeFilter('a.create_time')
 
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
        
        $_map='';
        if(isset($map['au.nickname']) && null!=$map['au.nickname']) {
        	$_map="( `au`.`nickname` LIKE '".$map['au.nickname'][1]."' OR `c`.`name` LIKE '".$map['au.nickname'][1]."' OR `u`.`mobile` LIKE '".$map['au.nickname'][1]."' )";
        	unset($map['au.nickname']);
        }
        
        empty($order) && $order='a.id asc';
        // 数据列表
        $data_list = Db::name('apply')
            ->alias('a')
            ->join('customer c','a.cid = c.id','LEFT')
            ->join('user u','a.uid = u.id','LEFT')
            ->join('admin_user au','u.agent_id = au.id','LEFT')
            ->where($map)
            ->where($_map)
            ->field('a.id,u.mobile,au.nickname,c.name,a.create_time,a.ip')
            ->order($order)
            ->group('a.uid,a.cid')
            ->paginate();
         
 		$tmpAry=array();
        foreach($data_list as $k=>&$v) {
        	$tmpAry[$k]=$v;
        	$tmpAry[$k]['ctime']=date('Y-m-d H:i',$v['create_time']);
        }
 
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['mobile', 'auto', '申请人手机号'],
            ['nickname', 'auto', '渠道名'],
            ['name', 'auto', '产品名'],
            ['ctime', 'auto', '申请时间'],
            ['ip', 'auto', '申请IP']
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', [date('Y-m-d H:i:s'), $cellName, $tmpAry]);
    }
    
    
    public function statistics()
    {
    	// 查询
    	$map = $this->getMap();
    	// 排序
    	$order = $this->getOrder();
    	// 数据列表
    	//        $data_list = AgentModel::where($map)->order($order)->paginate();
    
    	$map_ = '';
    	if (isset($map['create_time'])){
    		$map_ = $map['create_time'];
    		unset($map['create_time']);
    	}
    
    	$data_list = Db::name('customer')->where($map)->field('id,name')->order('id asc')->select();
    	foreach ($data_list as $k=>&$item){
    		if(!empty($map_)) {
    			$item['count'] = Db::name('apply')->where('cid',$item['id'])
    			->whereTime('create_time','between',$map_[1])->count();
    		} else {
    			$item['count'] = Db::name('apply')->where('cid',$item['id'])->count();
    		}
    		if($item['count']==0) {
    			unset($data_list[$k]);
    		}
    	}
    	
 
    
    	if(isset($_GET['_by']) && isset($_GET['_order'])) {
    		$data_list=$this->array_sort($data_list,$_GET['_order'],$_GET['_by']);
    		$i=1;
    		foreach($data_list as $k=>&$v) {
    			$v['p']=$i;
    			$i++;
    		}
    	} else {
    		
    		$data_list=$this->array_sort($data_list,'count','desc');
    		$i=1;
    		foreach($data_list as $k=>&$v) {
    			$v['p']=$i;
    			$i++;
    		}
    	}
    	 
 
    	//总计
    	if(!empty($map_)) {
    		$totalCount=Db::name('apply')->whereTime('create_time','between',$map_[1])->count();
    	} else {
    		$totalCount=Db::name('apply')->whereTime('create_time','today')->count();
    	}
    	
    	//用户申请总计
     	if(!empty($map_)) {
    		$totalCountUser=Db::name('user')->alias('u')->whereTime('create_time','between',$map_[1])->count();
    	} else {
    		$totalCountUser=Db::name('user')->alias('u')->whereTime('create_time','today')->count();
    	}
  
    	 // 按钮
        $btn_access = [
            'title' => '导出数据',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('exports',$this->request->param())
        ];
    
    	// 使用ZBuilder快速创建数据表格
    	return ZBuilder::make('table')
    	->addOrder('count') // 添加排序
    	->setSearch(['name' => '产品名称']) // 设置搜索框
    	->setPageTitle("产品申请量统计-当日总量".$totalCount.",用户列表-当日总量".$totalCountUser)
    	->addColumns([ // 批量添加数据列
    	  		['p', '排序'],
    			['id', 'ID'],
    			['name', '名称'],
    			['count', '申请量'],
    			])->setRowList($data_list) // 设置表格数据
    			->addTimeFilter('create_time')
    			->addTopButton('custom', $btn_access) // 添加授权按钮
    			->fetch(); // 渲染模板
    }
    
    public function importfile()
    {
    	  	        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）

	    
	    if ($this->request->isPost()) {
 
    		if($_POST['files']) {
    			 CustomerDataModel::where('id','>',0)->delete();
    			
		    	$info=Db::name('admin_attachment')->find($_POST['files']);     
		    	 
		    	$fields=array('name'=>'名称','count'=>'申请量');
		        $result=plugin_action('Excel/Excel/import', [$info['path'],$table='customer_data',$fields,1,$where=array(),'name']);
		        $this->success($result['message'], 'applyinfo');
    		}
	        exit;
	    } else {
		    return ZBuilder::make('form')
		    ->addFormItem('file', 'files', '附件')
		    ->isAjax(false)
		    ->fetch();
	    }
    }
    
    public function applyinfo()
    {
  
    	$data_list = Db::name('customer_data')->field('cid,id,name,count')->order('id asc')->select();
    	$i=1;
       	foreach ($data_list as $k=>&$item){
    		$item['p'] = $i;
    		$i++;
    	}
    	 // 按钮
        $btn_access = [
            'title' => '导入数据',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('importfile',$this->request->param())
        ];
            	 // 按钮
        $btn_access_r = [
            'title' => '导出数据',
            'icon'  => 'fa fa-fw fa-download',
            'href'  => url('exportsdata',$this->request->param())
        ];
        
    	// 使用ZBuilder快速创建数据表格
    	return ZBuilder::make('table')
    	->addOrder('count') // 添加排序
    	->addColumns([ // 批量添加数据列
    			['p', '排序'],
    			['cid', 'ID'],
    			['name', '名称'],
    			['count', '申请量'],
    			 ['right_button', '操作', 'btn']
    			])->setRowList($data_list) // 设置表格数据
    			->addTopButtons('add') // 批量添加顶部按钮
            	->addRightButtons(['edit','delete' => ['data-tips' => '删除后无法恢复。']]) // 批量添加右侧按钮
            	->addTopButton('custom', $btn_access) // 添加授权按钮
            	->addTopButton('custom', $btn_access_r) // 添加授权按钮
    			->fetch(); // 渲染模板
    }
    
    public function into()
    {
    	//先删除数据
    	CustomerDataModel::where('id','>',0)->delete();
    	
    	$new=array();
    	$data_list = Db::name('customer')->field('id,name')->order('id asc')->select();
    	
    	foreach ($data_list as $k=>&$item){
    		$item['count'] = Db::name('apply')->where('cid',$item['id'])->count();
    	}
    	$data_list=$this->array_sort($data_list,'count','desc');
    	foreach ($data_list as $k=>&$item){
    		$new[$k]['name']=$item['name'];
    		$new[$k]['count']=$item['count'];
    		$new[$k]['cid']=$item['id'];
    	}
    	$user = new CustomerDataModel;
     	if ($user->saveAll($new)) {
                 
                $this->success('导入成功', 'applyinfo');
            } else {
                $this->error('新增失败');
            }
    }
    
     public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();


            if ($user = CustomerDataModel::create($data)) {
                 
                $this->success('新增成功', 'applyinfo');
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '名称'],
                ['text', 'count', '申请量'],
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

            if (CustomerDataModel::update($data)) {
               
                $this->success('编辑成功', 'applyinfo');
            } else {
                $this->error('编辑失败');
            }
        }

     	$info = CustomerDataModel::get($id);
        // 显示编辑页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'name', '名称'],
                ['text', 'count', '数量'],
   
            ])
            ->setFormData($info)
            ->fetch();
    }
     
    public function delete($ids = null) {
    	if ($ids === null) $this->error('缺少参数');
    	
    	if (CustomerDataModel::destroy($ids)) {
               
         	$this->success('编辑成功', 'applyinfo');
            } else {
                $this->error('编辑失败');
            }
    }
    
    public function exports()
    {
    	// 查询
    	$map = $this->getMap();
    	// 排序
    	$order = $this->getOrder();
    	// 数据列表
    	//        $data_list = AgentModel::where($map)->order($order)->paginate();
    
    	$map_ = '';
    	if (isset($map['create_time'])){
    		$map_ = $map['create_time'];
    		unset($map['create_time']);
    	}
    
    	$data_list = Db::name('customer')->where($map)->field('id,name')->order('id asc')->select();
    	foreach ($data_list as $k=>&$item){
    		if(!empty($map_)) {
    			$item['count'] = Db::name('apply')->where('cid',$item['id'])
    			->whereTime('create_time','between',$map_[1])->count();
    		} else {
    			$item['count'] = Db::name('apply')->where('cid',$item['id'])->count();
    		}
    		if($item['count']==0) {
    			unset($data_list[$k]);
    		}
    	}
    	
 
    	$tmpAry=array();
    	if(isset($_GET['_by']) && isset($_GET['_order'])) {
    		$data_list=$this->array_sort($data_list,$_GET['_order'],$_GET['_by']);
    		$i=0;
    		foreach($data_list as $k=>&$v) {
    			$tmpAry[$i]=$v;
    			$tmpAry[$i]['p']=$i+1;
    			$i++;
    		}
    	} else {
    		
    		$data_list=$this->array_sort($data_list,'count','desc');
    		$i=0;
    		foreach($data_list as $k=>&$v) {
    		    $tmpAry[$i]=$v;
    		    $tmpAry[$i]['p']=$i+1;
    			$i++;
    		}
    	}
    	
 
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['p', 'auto', '排序'],
            ['id', 'auto', 'ID'],
            ['name', 'auto', '名称'],
            ['count', 'auto', '申请量'],
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', [date('Y-m-d H:i:s'), $cellName, $tmpAry]);
    }
    
 	public function exportsdata()
    {
    	 
    	$data_list = Db::name('customer_data')->field('cid,id,name,count')->order('id asc')->select();
    	$i=1;
    	foreach ($data_list as $k=>&$item){
    		$item['p'] = $i;
    		$i++;
    	}
    	
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['p', 'auto', '排序'],
            ['cid', 'auto', 'ID'],
            ['name', 'auto', '名称'],
            ['count', 'auto', '申请量'],
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', [date('Y-m-d H:i:s'), $cellName, $data_list]);
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