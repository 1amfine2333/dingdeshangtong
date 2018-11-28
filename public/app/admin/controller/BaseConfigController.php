<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\BaseConfigModel;
use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;

use think\Db;

/**
 * Class SettingController
 * @package app\admin\controller
 * @adminMenuRoot(
 *     'name'   =>'基础配置设置',
 *     'action' =>'index',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 0,
 *     'icon'   =>'cogs',
 *     'remark' =>'基础配置设置'
 * )
 */
class BaseConfigController extends AdminBaseController
{


    /**
     * 网站信息设置提交
     * @adminMenu(
     *     'name'   => '网站信息设置提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '网站信息设置提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {

            $data      = $this->request->param();
            $config = new BaseConfigModel();
            if($config->count()<5){
                $title = input('post.tel_no');
                $tel_no = input('post.title');
                if(empty($title) && empty($tel_no)){
                    $this->error("请完善输入信息");
                }
                $result = $config->validate(true)->allowField(true)->save($data);

                if ($result === false) {
                    $this->error($config->getError());
                }
                $this->success("添加成功！", url("base_config/index"));
            }

            $this->error("热线已有5个不能再新增,只能删除、编辑");

        }
    }


    /**
     * 编辑配置保存
     * @adminMenu(
     *     'name'   => '编辑配置保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑配置保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {

        $data = $this->request->param();
        $BaseModel = new BaseConfigModel();
        if ($this->request->isPost()) {
            $title = input('post.tel_no');
            $tel_no = input('post.title');
            if(empty($title) && empty($tel_no)){
                $this->error("请完善输入信息");
            }
            $result = $BaseModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
            if ($result === false) {
                $this->error($BaseModel->getError());
            }

            $this->success("保存成功！", url("base_config/index"));
        }
        if (isset($data['id'])){
            return $BaseModel::get($data['id']);
        }

    }

    /**
     * 基础配置
     * @adminMenu(
     *     'name'   => '基础配置',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        $config = new BaseConfigModel();
        $this->assign('base_config',$config->select());
        $this->assign('len',$config->count());
        return $this->fetch();
    }

    /**
     * 删除多条基础配置
     * @adminMenu(
     *     'name'   => '删除多条基础配置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除多条基础配置',
     *     'param'  => ''
     * )
     */
    public function delMultiple()
    {
        $id = $this->request->param('id', 0, 'string');
        $config = new BaseConfigModel();
        $res = $config->where('id','in',$id)->delete();
        $this->success("成功删除{$res}条数据！", url("base_config/index"));
    }

    /**
     * 删除基础配置
     * @adminMenu(
     *     'name'   => '删除基础配置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除基础配置',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        BaseConfigModel::destroy($id);

        $this->success("删除成功！", url("base_config/index"));
    }
}