<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\BaseService;
use Shop\Services\AppVersionService;


class AppVersionController extends ControllerBase
{
    public $service;
    public function initialize()
    {
        parent::initialize();
        $this->service = AppVersionService::getInstance();
    }

    public function indexAction()
    {
        $param =[];
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $data[$k]  =   $this->getParam($k,'trim');
            }
        }
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = $this->service->getList($param);
        $this->view->setVar('list',$list);
    }

    public function deldwonurlAction(){
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result =$this->service->delDwonUrl($param['id']);
            return $this->response->setJsonContent($result);
        }
    }

    public function editAction()
    {
        $id = $this->request->get('id')?$this->request->get('id'):0;
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $param['versions_id'] =$id;
            $result = $this->service->editData($param,'/appversion/index');
            return $this->response->setJsonContent($result);
        }else{
            if($id > 0){
                $info = $this->service->getdata($id);
                $download = $this->service->getDownUrl($id);
                if($download){
                    $this->view->setVar('download',$download);
                }
                $this->view->setVar('info',$info);
            }
            $appclannel = $this->service->getClannelList();
            $this->view->setVar('clannel',$appclannel);
        }
    }

    public function addAction(){
        if($this->request->isPost()){
            $param = $this->request->getPost();
                $result = $this->service->addData($param,'/appversion/index');
            return $this->response->setJsonContent($result);
        }else{
            $appclannel = $this->service->getClannelList();
            $this->view->setVar('clannel',$appclannel);
            $this->view->pick('appversion/edit');
        }
    }
}