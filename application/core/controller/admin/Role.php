<?php
/**
 * +----------------------------------------------------------------------
 * | InitAdmin/actionphp [ InitAdmin渐进式模块化通用后台 ]
 * +----------------------------------------------------------------------
 * | Copyright (c) 2018-2019 http://initadmin.net All rights reserved.
 * +----------------------------------------------------------------------
 * | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 * +----------------------------------------------------------------------
 * | Author: jry <ijry@qq.com>
 * +----------------------------------------------------------------------
*/
namespace app\core\controller\admin;

use think\Db;
use think\Validate;
use think\facade\Request;
use app\core\controller\common\Admin;
use app\core\util\Tree;

/**
 * 角色
 */
class Role extends Admin
{
    private $core_role;

    public function __construct()
    {
        $this->core_role = Db::name('core_role');
    }

    /**
     * 角色列表
     *
     * @return \think\Response
     */
    public function trees()
    {
        $data_list = $this->core_role
            ->where(['delete_time' => 0])
            ->select();
        $tree      = new Tree();
        $data_tree = $tree->list2tree($data_list);
        //dump($data_tree);
        return json(
            [
                'code' => 200, 'msg' => '成功', 'data' => [
                    'data_list' => $data_tree,
                    'dynamic_data' => [
                        'top_button_list' => [
                            'add' => [
                                'page_type' => 'modal',
                                'modal_data' => [
                                    'title' => '添加角色',
                                    'api' => 'v1/admin/core/role/add',
                                    'width' => '600',
                                ],
                                'route' => '',
                                'title' => '添加角色',
                                'type' => 'default',
                                'size' => '',
                                'shape' => '',
                                'icon' => ''
                            ]
                        ],
                        'right_button_list' => [
                            'member' => [
                                'page_type' => 'modal',
                                'modal_data' => [
                                    'title' => '角色成员',
                                    'api' => 'v1/admin/core/role/member',
                                    'width' => '600',
                                ],
                                'route' => '',
                                'title' => '成员',
                                'type' => 'primary',
                                'size' => '',
                                'shape' => '',
                                'icon' => ''
                            ],
                            'edit' => [
                                'page_type' => 'modal',
                                'modal_data' => [
                                    'title' => '修改角色',
                                    'api' => 'v1/admin/core/role/edit',
                                    'width' => '600',
                                ],
                                'route' => '',
                                'title' => '修改',
                                'type' => 'default',
                                'size' => '',
                                'shape' => '',
                                'icon' => ''
                            ],
                            'delete' => [
                                'page_type' => 'modal',
                                'modal_data' => [
                                    'type' => 'confirm',
                                    'title' => '确认要删除该角色吗？',
                                    'api' => 'v1/admin/core/role/delete',
                                    'width' => '600',
                                    'okText' => '确认删除',
                                    'cancelText' => '取消操作',
                                    'content' => '<p><p>如果该角色下有子角色需要先删除或者移动</p><p>如果该角色下有成员需要先移除才可以删除</p><p>删除该角色将会删除对应的权限数据</p></p>',
                                ],
                                'route' => '',
                                'title' => '删除',
                                'type' => 'default',
                                'size' => '',
                                'shape' => '',
                                'icon' => ''
                            ]
                        ],
                        'columns' => [
                            [
                                'title' => 'ID',
                                'key' => 'id',
                                'width' => '40px'
                            ],
                            [
                                'title' => '部门',
                                'key' => 'title',
                                'minWidth' => '50px'
                            ],
                            [
                                'title' => '排序',
                                'key' => 'sortnum'
                            ],
                            [
                                'title' => '操作',
                                'key' => 'right_button_list',
                                'minWidth' => '50px',
                                'type' => 'template',
                                'template' => 'right_button_list'
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * 添加
     *
     * @return \think\Response
     */
    public function add()
    {
        if(request()->isPost()){
            // 数据验证
            $validate = Validate::make([
                'pid'  => 'number',
                'name' => 'require',
                'title' => 'require'
            ],
            [
                'pid.number' => 'pid必须数字',
                'name.require' => '用户名必须',
                'title.require' => '密码必须'
            ]);
            $data = input('post.');
            if (!$validate->check($data)) {
                return json(['code' => 200, 'msg' => $validate->getError(), 'data' => []]);
            }
            
            // 数据构造
            $data_db = [];
            $data_db['pid'] = isset($data['pid']) ? $data['pid'] : '';
            $data_db['name'] = $data['name'];
            $data_db['title'] = $data['title'];
            $data_db['sortnum'] = isset($data['sortnum']) ? $data['sortnum'] : 0;
            $data_db['view_auth'] = isset($data['view_auth']) ? $data['view_auth'] : ''; //导航菜单/功能按钮/页面等视图权限
            $data_db['api_auth'] = isset($data['api_auth']) ? $data['api_auth'] : ''; //接口权限
            $data_db['status'] = 1;
            
            // 存储数据
            $ret = $this->core_role->insert($data_db);
            if ($ret) {
                return json(['code' => 200, 'msg' => '添加角色成功', 'data' => []]);
            } else {
                return json(['code' => 0, 'msg' => '添加角色失败', 'data' => []]);
            }
        } else {
            return json(
                [
                    'code' => 200,
                    'msg' => '成功',
                    'data' => [
                        'form_data' => [
                            'form_items' => [
                                [
                                    'name' => 'pid',
                                    'title' => '上级',
                                    'type' => 'select',
                                    'options' =>  [
                                        [
                                            'title' => '测试',
                                            'value' => ''
                                        ]
                                    ],
                                    'placeholder' => '请选择上级',
                                    'tip' => '选择上级后会限制权限范围不大于上级'
                                ],
                                [
                                    'name' => 'name',
                                    'title' => '英文名',
                                    'type' => 'text',
                                    'placeholder' => '请输入英文名',
                                    'tip' => '英文名其实可以理解为一个系统代号'
                                ],
                                [
                                    'name' => 'title',
                                    'title' => '角色名称',
                                    'type' => 'text',
                                    'placeholder' => '请输入角色名称',
                                    'tip' => '角色名称也可以理解为部门名称'
                                ]
                            ],
                            'form_values' => [
                                'pid' => 0,
                                'name' => '',
                                'title' => '',
                            ],
                            'form_rules' => [
                                'name' =>  [
                                    [
                                        'required' => true,
                                        'message' => '请填写角色英文名称',
                                        'trigger' => 'change'
                                    ]
                                ],
                                'title' =>  [
                                    [
                                        'required' => true,
                                        'message' => '请填写角色名称',
                                        'trigger' => 'change'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
        } 
    }

    /**
     * 修改
     *
     * @return \think\Response
     */
    public function edit($id)
    {
        if(request()->isPost()){
            // 数据验证
            $validate = Validate::make([
                'pid'  => 'number',
                'name' => 'require',
                'title' => 'require'
            ],
            [
                'pid.number' => 'pid必须数字',
                'name.require' => '用户名必须',
                'title.require' => '密码必须'
            ]);
            $data = input('post.');
            if (!$validate->check($data)) {
                return json(['code' => 200, 'msg' => $validate->getError(), 'data' => []]);
            }

            // 数据构造
            $data_db = $data;
            if (isset($data_db['view_auth'])) {
                $data_db['view_auth'] = json_encode($data_db['view_auth']);
            }
            if (isset($data_db['api_auth'])) {
                $data_db['api_auth'] = json_encode($data_db['api_auth']);
            }
            if (count($data_db) <= 0 ) {
                return json(['code' => 0, 'msg' => '无数据修改提交', 'data' => []]);
            }

            // 存储数据
            $ret = $this->core_role
                ->where('id', $id)
                ->update($data_db);
            if ($ret) {
                return json(['code' => 200, 'msg' => '修改角色成功', 'data' => []]);
            } else {
                return json(['code' => 0, 'msg' => '修改角色失败', 'data' => []]);
            }
        } else {
            $info = $this->core_role
                ->where('id', $id)
                ->find();
            return json([
                'code' => 200,
                'msg' => '成功',
                'data' => [
                    'form_data' => [
                        'form_items' => [
                            [
                                'name' => 'pid',
                                'title' => '上级',
                                'type' => 'select',
                                'options' =>  [
                                    [
                                        'title' => '测试',
                                        'value' => ''
                                    ]
                                ],
                                'placeholder' => '请选择上级',
                                'tip' => '选择上级后会限制权限范围不大于上级'
                            ],
                            [
                                'name' => 'name',
                                'title' => '英文名',
                                'type' => 'text',
                                'placeholder' => '请输入英文名',
                                'tip' => '英文名其实可以理解为一个系统代号'
                            ],
                            [
                                'name' => 'title',
                                'title' => '角色名称',
                                'type' => 'text',
                                'placeholder' => '请输入角色名称',
                                'tip' => '角色名称也可以理解为部门名称'
                            ]
                        ],
                        'form_values' => [
                            'pid' => $info['pid'],
                            'name' => $info['name'],
                            'title' => $info['title'],
                            'view_auth' => $info['view_auth'],
                            'api_auth' => $info['api_auth'],
                            'sortnum' => $info['sortnum'],
                        ],
                        'form_rules' => [
                            'name' =>  [
                                [
                                    'required' => true,
                                    'message' => '请填写角色英文名称',
                                    'trigger' => 'change'
                                ]
                            ],
                            'title' =>  [
                                [
                                    'required' => true,
                                    'message' => '请填写角色名称',
                                    'trigger' => 'change'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        } 
    }

    /**
     * 删除
     * 
     * @return \think\Response
     */
    public function delete($id)
    {
        $ret = $this->core_role
            ->where(['id' => $id])
            ->useSoftDelete('delete_time', time())
            ->delete();
        if ($ret) {
            return json(['code' => 200,'msg' => '删除成功','data' => []]);
        } else {
            return json(['code' => 200,'msg' => '删除错误','data' => []]);
        }
    }
}
