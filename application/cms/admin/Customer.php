<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Customer as CustomerModel;

class Customer extends Admin
{
    public function index()
    {
        // 查询
        $map = $this->getMap();
        // 排序
        $order = $this->getOrder();
        // 数据列表
        $data_list = CustomerModel::where($map)->order($order)->paginate();

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['name' => '名称']) // 设置搜索框
            ->addColumns([ // 批量添加数据列
//                ['id', 'ID'],
                ['name', '名称', 'text.edit'],
                ['logo', 'LOGO', 'picture'],
                ['url', '链接', 'text.edit'],
                ['create_time', '创建时间', 'datetime'],
                ['sort', '排序', 'text.edit'],
                ['top', '顶置', 'switch'],
                ['status', '上线', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('add,enable,disable,delete') // 批量添加顶部按钮
            ->addRightButtons(['edit', 'delete' => ['data-tips' => '删除后无法恢复。']]) // 批量添加右侧按钮
            ->addOrder('id,sort,top,status,create_time')
            ->setRowList($data_list) // 设置表格数据
            ->addValidate('Customer', 'name,url')
            ->setTableName('customer')
            ->fetch(); // 渲染模板
    }


    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Customer');
            if(true !== $result) $this->error($result);

            if ($customer = CustomerModel::create($data)) {
                // 记录行为
                action_log('customer_add', 'cms_customer', $customer['id'], UID, $data['name']);
                $this->success('新增成功', 'index');
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'name', '名称'],
                ['image', 'logo', '图片'],
                ['text', 'url', '链接'],
                ['number', 'lower', '已下款初始值'],
                ['text', 'point', '要点'],
                ['text', 'sort', '排序', '', 100],
                ['radio', 'new', '新品', '', ['否', '是'], 1],
                ['radio', 'high_pass', '高通过率', '', ['否', '是'], 1],
                ['radio', 'top', '顶置', '', ['否', '是'], 0],
                ['radio', 'status', '上线', '', ['否', '是'], 1]
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

            // 验证
            $result = $this->validate($data, 'Customer');
            if(true !== $result) $this->error($result);

            if (CustomerModel::update($data)) {
                // 记录行为
                action_log('customer_add', 'cms_customer', $id, UID, $data['name']);
                $this->success('编辑成功', 'index');
            } else {
                $this->error('编辑失败');
            }
        }

        $info = CustomerModel::get($id);

        // 显示编辑页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'name', '名称'],
                ['image', 'logo', '图片'],
                ['text', 'url', '链接'],
                ['number', 'lower', '已下款初始值'],
                ['text', 'point', '要点'],
                ['text', 'sort', '排序'],
                ['radio', 'new', '新品', '', ['否', '是'], 1],
                ['radio', 'high_pass', '高通过率', '', ['否', '是'], 1],
                ['radio', 'top', '顶置', '', ['否', '是']],
                ['radio', 'status', '上线', '', ['否', '是']]
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
        $slider_title = CustomerModel::where('id', 'in', $ids)->column('name');
        return parent::setStatus($type, ['customer_'.$type, 'cms_customer', 0, UID, implode('、', $slider_title)]);
    }

    public function quickEdit($record = [])
    {
        $id      = input('post.pk', '');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $slider  = CustomerModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $slider . ')，新值：(' . $value . ')';
        return parent::quickEdit(['customer_edit', 'cms_customer', $id, UID, $details]);
    }
}