<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;

use Shop\Services\AdHomeTopService;

class AdHomeTopController extends ControllerBase
{
    public $service;
    public function initialize()
    {
        parent::initialize();
        $this->service = AdHomeTopService::getInstance();
    }

    public function adlistAction()
    {
        $param =[];
        foreach($this->request->get() as $k=>$v){
            if($this->getParam($k,'trim')){
                $data[$k]  =   $this->getParam($k,'trim');
            }
        }
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = $this->service->getAllad($param);
        $this->view->setVar('search',$data);
        $this->view->setVar('list',$list);
        $this->view->pick('adhometop/index');
    }

    public function addAction(){
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $result = $this->service->addAd($param);
            return $this->response->setJsonContent($result);
        }else{
            $this->view->pick('adhometop/add');
        }

    }

    public function editAction(){
        $id = (int)$this->request->get('id', 'trim')?$this->request->get('id', 'trim'):0;
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $param['id'] = $id;
            $result = $this->service->editData($param);
            return $this->response->setJsonContent($result);
        }else{
            $data = $this->service->getAd($id);
            $data['status'] = $this->request->get('status', 'trim');
            $this->view->setVar('data',$data);
        }
    }
    // 删除首页广告
    public function delAction(){
        if($this->request->isAjax()){
            $result =$this->service->delAppHomeAdImg((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }
    //删除广告图
    public function deladAction(){
        if($this->request->isAjax()){
            $result =$this->service->delAdImg((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }

}