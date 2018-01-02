<?php
/**
 * @desc 商品组控制器
 * @author 邓永军
 * @date 2016-10-10
 */
namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\GoodsetService;
use Shop\Services\PromotionService;

class GoodsetController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        $this->setTitle('商品管理');
        $this->view->setVar('management','promotion');
    }

    /**
     * @desc 添加商品套餐
     * @author 邓永军
     * @date 2016-10-10
     */
    public function addAction()
    {
        if($this->request->isPost()){
            return $this->response->setJsonContent(GoodsetService::getInstance()->do_add($this->request->getPost()));
        }else{
            $this->view->setVar('goodSetEnum',PromotionService::getInstance()->getPromotionEnum());
        }
    }

    /**
     * @desc 商品套餐列表
     * @author 邓永军
     * @date 2016-10-10
     */
    public function listAction()
    {
        $param = [
            'param' => $this->request->get(),
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/goodset/list',
        ];
        $this->view->setVar("group_name",$this->request->get("group_name"));
        $this->view->setVar("status",$this->request->get("group_status"));
        $this->view->setVar("platform",$this->request->get("platform_status"));
        $this->view->setVar("start_time",$this->request->get("start_time"));
        $this->view->setVar("end_time",$this->request->get("end_time"));
        $this->view->setVar('GoodSetList',GoodsetService::getInstance()->getList($param));
    }

    /**
     * @desc 商品套餐编辑
     * @author 邓永军
     * @date 2016-10-10
     */
    public function editAction()
    {
        if($this->request->isPost()){
            return $this->response->setJsonContent(GoodsetService::getInstance()->do_edit($this->request->getPost()));
        }else{
            $id=$this->request->get("id","int");
            if(!isset($id) || empty($id)){
                $this->response->redirect('/goodset/list');
            }
            $edit_info=GoodsetService::getInstance()->edit($id);
            $view=$this->request->get("view","int");
            if(!empty($view)){
                $this->view->setVar('view',1);
            }else{
                $this->view->setVar('view',0);
            }
            $this->view->setVar('goodSetEnum',PromotionService::getInstance()->getPromotionEnum());
            $this->view->setVar('mutexList',explode(',',$edit_info['mutex']));
            $this->view->setVar("edit_info",$edit_info);
        }
    }

    /**
     * @desc 删除商品套餐
     * @author 邓永军
     * @date 2016-10-10
     */
    public function delAction()
    {
        if($this->request->isPost()){
            $mid = $this->request->getPost('mid','trim');
            $request = (string)$this->request->getPost('request','trim','');
            $result = GoodsetService::getInstance()->do_del($mid,$request);
            return $this->response->setJsonContent($result);
        }
    }
}