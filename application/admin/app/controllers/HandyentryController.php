<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\BaseService;
use Shop\Services\HandyEntryService;

class HandyEntryController extends ControllerBase
{
    public $service;
    public function initialize()
    {
        parent::initialize();

        $this->service = HandyEntryService::getInstance();


    }
    //wap首页快捷列表
    public function waplistAction()
    {
        $param =[];
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $data['channel_name'] = 'wap';
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = $this->service->EntryList($param);
        $action = array(
            'edit'=>'wapedit',
            'add'=>'wapadd',
        );
        $this->view->setVar('action',$action);
        $this->view->setVar('search',$data);
        $this->view->setVar('list',$list);
        $this->view->pick('handyentry/index');
    }
    //添加wap首页推荐列表
    public function wapaddAction(){
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $param['channel_name'] = 'wap';
            $result = $this->service->addEntry($param,'/handyentry/waplist');
            return $this->response->setJsonContent($result);
        }else{
            $result = $this->service->get_versions();
            $this->view->setVars(array(
                'app_versions' => $result,
                'is_wap'=>1
            ));
            $this->view->pick('handyentry/edit');
        }
    }
    //修改wap首页推荐列表
    public function wapeditAction(){
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $param['id'] = $this->request->get('id');
            $result = $this->service->editEntry($param,'/handyentry/waplist');
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', '');
        $result = $this->service->getIndexEntryInfo($id);
        $this->view->setVars(array(
            'data' => $result['data'],
            'is_wap'=>1,
            'app_versions' => $result['app_versions']
        ));
        $this->view->pick('handyentry/edit');
    }

    //首页快捷列表
    public function indexlistAction(){
        $param =[];
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $data['channel_name'] = ' ';
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $list = $this->service->EntryList($param);
        $action = array(
            'edit'=>'edit',
            'add'=>'add',
        );
        $this->view->setVar('action',$action);
        $this->view->setVar('search',$data);
        $this->view->setVar('list',$list);
        $this->view->pick('handyentry/index');
    }
    //添加首页推荐列表
    public function addAction(){
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $param['channel_name'] = '';
            $result = $this->service->addEntry($param,'/handyentry/indexlist');
            return $this->response->setJsonContent($result);
        }else{
            $result = $this->service->get_versions();
            $this->view->setVars(array(
                'app_versions' => $result
            ));
            $this->view->pick('handyentry/edit');
        }
    }
    //修改首页推荐列表
    public function editAction(){
        if($this->request->isPost()){
            $param = $this->request->getPost();
            $param['id'] = $this->request->get('id');
            $result = $this->service->editEntry($param,'/handyentry/indexlist');
            return $this->response->setJsonContent($result);
        }
        $id = (int)$this->getParam('id', 'trim', '');
        $result = $this->service->getIndexEntryInfo($id);
        $this->view->setVars(array(
            'data' => $result['data'],
            'app_versions' => $result['app_versions']
        ));
        $this->view->pick('handyentry/edit');
    }

    public function hideAction(){
        if($this->request->isAjax()){
            $result =$this->service->update_status((int)$this->request->getPost('id', 'trim'),'hide');
            return $this->response->setJsonContent($result);
        }
    }

    public function showAction(){
        if($this->request->isAjax()){
            $result =$this->service->update_status((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }
    public function editsortAction(){
        if($this->request->isAjax()){
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result =$this->service->update_sort($param);
            return $this->response->setJsonContent($result);
        }
    }

    public function delAction(){
        if($this->request->isAjax()){
            $result =$this->service->delData((int)$this->request->getPost('id', 'trim'));
            return $this->response->setJsonContent($result);
        }
    }

    public function uploadAction()
    {
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->uploadFile($this->request);
            //判断是否是编辑上传图片，是则返回编辑器json格式
            if($this->getParam('dir', 'trim', '') == 'image'){
                $res = array('error' => 0, 'url' => $res['data'][0]['src']);
            }
            return $this->response->setJsonContent($res);
        }
    }

}